<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Book;
use AppBundle\Services\ApiKeyChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\DeserializationContext;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validation;

/**
 * @Route("/api/v1")
 */
class ApiController extends Controller
{
    protected $checkApi;

    public function __construct(ApiKeyChecker $checker)
    {
        $this->checkApi = $checker;
    }

    /**
     * @Route("/books", methods={"GET"})
     */
    public function listAction(Request $request)
    {
        if ($this->checkApi->checkKey()) {
            return $this->invalidResponse('wrong apiKey', Response::HTTP_UNAUTHORIZED);
        }

        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('AppBundle:Book')->findBy([], ['date' => 'DESC']);

        return $this->successfulResponse($books);
    }

    /**
     * @Route("/books/add", methods={"POST"}))
     */
    public function addAction(Request $request)
    {
        if ($this->checkApi->checkKey()) {
            return $this->invalidResponse('wrong apiKey', Response::HTTP_UNAUTHORIZED);
        }

        $bookRequest = $request->request->get('book');
        $serializer = SerializerBuilder::create()
            ->setExpressionEvaluator(new ExpressionEvaluator(new ExpressionLanguage()))
            ->build();

        try {
            $bookCreate = $serializer->deserialize(
                $bookRequest,
                Book::class,
                'json',
                DeserializationContext::create()->setGroups(['edit'])
            );
        } catch (\Throwable $ex) {
            return $this->invalidResponse($ex->getMessage());
        }

        $errors = $this->validateBook($bookCreate);

        if ($errors) {
            return $this->invalidResponse($errors);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bookCreate);
        $em->flush();

        return $this->successfulResponse(
            [
                "id" => $bookCreate->getId()
            ]
        );
    }

    /**
     * @Route("/books/{id}/edit", methods={"POST"})
     */
    public function editAction(Request $request, Book $book)
    {
        if ($this->checkApi->checkKey()) {
            return $this->invalidResponse('wrong apiKey', Response::HTTP_UNAUTHORIZED);
        }

        $bookRequest = $request->request->get('book');
        $serializer = SerializerBuilder::create()
            ->setExpressionEvaluator(new ExpressionEvaluator(new ExpressionLanguage()))
            ->build();

        try {
            $bookEdit = $serializer->deserialize(
                $bookRequest,
                Book::class,
                'json',
                DeserializationContext::create()->setGroups(['edit'])
            );
        } catch (\Throwable $ex) {
            return $this->invalidResponse($ex->getMessage());
        }

        $errors = $this->validateBook($bookEdit);

        if ($errors) {
            return $this->invalidResponse($errors);
        }

        if (!empty($bookEdit->getName())) {
            $book->setName($bookEdit->getName());
        }

        if (!empty($bookEdit->getAuthor())) {
            $book->setAuthor($bookEdit->getAuthor());
        }

        if (!empty($bookEdit->getDate())) {
            $book->setDate($bookEdit->getDate());
        }

        $book->setDownloadable($bookEdit->getDownloadable());

        $this->getDoctrine()->getManager()->flush();

        return $this->successfulResponse(["id" => $book->getId()]);
    }

    /**
     * Get successful response.
     *
     * @param $result
     * @return JsonResponse|Response
     */
    protected function successfulResponse($result)
    {
        $response = [
            'success' => true,
            'response' => $result
        ];

        $serializer = SerializerBuilder::create()
            ->setExpressionEvaluator(new ExpressionEvaluator(new ExpressionLanguage()))
            ->build();

        try {
            $requestModel = $serializer->serialize($response, 'json');
        } catch (\Throwable $ex) {
            return $this->invalidResponse($ex->getMessage());
        }

        return new Response($requestModel);
    }

    /**
     * Get invalid response.
     *
     * @param $status
     * @param $message
     * @return JsonResponse
     */
    public function invalidResponse($message = "Unknown error", $status = 400)
    {
        return new JsonResponse([
            'success' => false,
            'errorMsg' => $message
        ], $status);
    }

    protected function validateBook(Book $book)
    {
        $validator = Validation::createValidator();
        $metadata = $validator->getMetadataFor(Book::class);

        $metadata->addGetterConstraint('name', new NotBlank(), new Type("string"));
        $metadata->addGetterConstraint('author', new NotBlank(), new Type("string"));
        $metadata->addGetterConstraint('date', new NotBlank(), new Date());
        $metadata->addGetterConstraint('downloadable', new NotNull(), new Type("boolean"));

        $violations = $validator->validate($book);

        if (count($violations) !== 0) {
            $arViolations = [];
            foreach ($violations as $violation) {
                $arViolations[] = $violation->getPropertyPath() . ' : ' . $violation->getMessage();
            }

            return $arViolations;
        }

        return false;
    }
}

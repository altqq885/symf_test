<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Book;
use AppBundle\Form\BookType;
use AppBundle\Services\FileUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('AppBundle:Book')->findAllBooks();

        return $this->render('app/index.html.twig', ['books' => $books]);
    }

    /**
     * @Route("book", name="add_book")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addBookAction(Request $request, FileUploader $fileUploader)
    {
        $document = new Book();
        $form = $this->createForm(BookType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $imageName = $fileUploader->upload($document->getImage());
            $document->setImage($imageName);

            if ($pdfFile = $document->getFile()) {
                $pdfFileName = $fileUploader->upload($pdfFile);
                $document->setFile($pdfFileName);
            }

            $em->persist($document);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('app/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/book/{id}/edit", name="edit_book", requirements={"id"="\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editBookAction($id, Request $request, FileUploader $fileUploader)
    {
        $em = $this->getDoctrine()->getManager();
        $book = $em->getRepository('AppBundle:Book')->find($id);

        if (!$book) {
            // have no 404 page
            return $this->redirectToRoute('homepage');
        }

        $imagePath = $book->getImage();
        if ($imagePath) {
            try {
                $image = new File($this->getParameter('files_dir') . $imagePath);
                $book->setImage($image);
            } catch (FileException $ex) {
                $book->setImage(null);
            }
        } else {
            $book->setImage(null);
        }

        $filePath = $book->getFile();
        if ($filePath) {
            try {
                $file = new File($this->getParameter('files_dir') . $filePath);
                $book->setFile($file);
            } catch (FileException $ex) {
                $book->setFile(null);
            }
        } else {
            $book->setFile(null);
        }

        $form = $this->createForm(BookType::class, $book);
        $form->add(
            'deleteImg',
            CheckBoxType::class,
            [
                'label' => 'delete image',
                'mapped' => false,
                'required' => false
            ]
        )
            ->add(
                'deleteFile',
                CheckBoxType::class,
                [
                    'label' => 'delete File',
                    'mapped' => false,
                    'required' => false
                ]
            );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($book->getImage()) {
                $imageName = $fileUploader->upload($book->getImage());
                $book->setImage($imageName);
            } else {
                $book->setImage($imagePath);
            }

            if ($pdfFile = $book->getFile()) {
                $pdfFileName = $fileUploader->upload($pdfFile);
                $book->setFile($pdfFileName);
            } else {
                $book->setFile($filePath);
            }

            if ($form->get('deleteImg')->getData()) {
                $img = $this->getParameter('files_dir') . $book->getImage();
                $book->setImage(null);
                if (is_file($img) && file_exists($img)) {
                    unlink($img);
                }
            }

            if ($form->get('deleteFile')->getData()) {
                $file = $this->getParameter('files_dir') . $book->getFile();
                $book->setFile(null);
                if (is_file($file) && file_exists($file)) {
                    unlink($file);
                }
            }

            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('app/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/book/{id}/delete", name="delete_book", requirements={"id"="\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteBookAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Book')->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No book');
        }

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}

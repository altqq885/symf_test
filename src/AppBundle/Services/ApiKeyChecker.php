<?php

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiKeyChecker
{
    protected $container;

    protected $requestStack;

    protected $invalidApiKey = false;

    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    public function checkKey()
    {
        $apiKey = $this->container->getParameter('api_key');
        $request = $this->requestStack->getCurrentRequest();
        $reqApiKey = $request->get('apiKey');

        if (empty($reqApiKey) || $reqApiKey != $apiKey) {
            return true;
        }

        return false;
    }
}

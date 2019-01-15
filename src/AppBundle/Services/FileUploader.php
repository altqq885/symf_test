<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;


class FileUploader
{
    private $targetDirectory;

    private $logger;

    public function __construct($targetDirectory, LoggerInterface $logger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->logger = $logger;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $postfix = $this->getDirectoryPostfix();
        try {
            $file->move($this->getTargetDirectory() . $postfix, $fileName);
        } catch (FileException $e) {
            $this->logger->error($e->getMessage());
        }

        return $postfix . '/' . $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory . '/';
    }

    private function getDirectoryPostfix()
    {
        return substr(md5(mt_rand()), 0, 3) . '/' . substr(md5(mt_rand()), 0, 3);
    }
}

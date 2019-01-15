<?php

namespace AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Repository\BookRepository;
use AppBundle\Entity\Book;

class BookSubscriber implements EventSubscriber
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function getSubscribedEvents()
    {
        return [
            'postRemove', 'postUpdate', 'postPersist'
        ];
    }

    public function clearCache(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Book) {
            return;
        }

        $args->getEntityManager()
            ->getConfiguration()
            ->getResultCacheImpl()
            ->delete(BookRepository::BOOK_LIST_CACHE_ID);
    }

    public function removeFiles(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Book) {
            return;
        }

        $img = $entity->getImage();
        if ($img && file_exists($this->targetDirectory . $img)) {
            unlink($this->targetDirectory . $img);
        }

        $path = $entity->getFile();
        if ($path && file_exists($this->targetDirectory . $path)) {
            unlink($this->targetDirectory . $path);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
        $this->removeFiles($args);
    }
}
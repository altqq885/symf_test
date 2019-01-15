<?php

namespace AppBundle\Repository;

class BookRepository extends \Doctrine\ORM\EntityRepository
{
    const BOOK_LIST_CACHE_ID = 'book_list';

    public function findAllBooks()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p FROM AppBundle:Book p ORDER BY p.date ASC')
            ->useResultCache(true, 3600 * 24, self::BOOK_LIST_CACHE_ID)
            ->getResult();
    }
}

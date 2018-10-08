<?php
/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 7/28/17
 * Time: 10:16 AM
 */

namespace conghau\Bundle\ApiResource\Factory;

use Doctrine\ORM\QueryBuilder;
use conghau\Bundle\ApiResource\Collection\PaginateCollection;


/**
 * Class PaginationFactory
 * @package conghau\ApiResource\Factory
 */
class PaginationFactory
{
    private $defaultPageSize = 5;

    /**
     * PaginationFactory constructor.
     *
     * @param string $defaultPageSize
     */
    public function __construct(string $defaultPageSize)
    {
        $this->defaultPageSize = $defaultPageSize;
    }

    /**
     * createCollection
     *
     * @param QueryBuilder $qb
     * @param int          $pageSize
     * @param int          $pageNumber
     *
     * @return PaginateCollection
     */
    public function createCollection($qb, int $pageSize, int $pageNumber)
    {

        $adapter = new \stdClass();
        if ($qb instanceof QueryBuilder) {
            $adapter = new \Pagerfanta\Adapter\DoctrineORMAdapter($qb, false, false);
        }
        if ($qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            $countQueryBuilderModifier = function ($queryBuilder) {
                $queryBuilder->select('COUNT(*) AS total_results')
                    ->setMaxResults(1);
            };
            $adapter = new \Pagerfanta\Adapter\DoctrineDbalAdapter($qb, $countQueryBuilderModifier);
        }

        $pagerFanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($pageSize);
        $pagerFanta->setCurrentPage($pageNumber);

        $programmers = [];
        foreach ($pagerFanta->getCurrentPageResults() as $result) {
            $programmers[] = $result;
        }
        $paginatedCollection = new PaginateCollection(
            $programmers, $pagerFanta->getNbResults(), $pageNumber, $pageSize
        );

        return $paginatedCollection;
    }
}
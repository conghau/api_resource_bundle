<?php

/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 7/28/17
 * Time: 11:16 AM
 */

namespace conghau\Bundle\ApiResource\Util;

use Doctrine\ORM\QueryBuilder;
use conghau\Bundle\ApiResource\Helper\Helper;

trait TraitApiResourceRepository
{
    public function getDfAlias()
    {
        return 'tch';
    }

    /**
     * @param object $entity
     * @param bool   $isFlush
     */
    public function delete(object $entity, $isFlush = true)
    {
        $this->_em->remove($entity);
        if ($isFlush) {
            $this->_em->flush();
        }
    }

    /**
     * @param object $entity
     * @param bool   $isFlush
     *
     * @return object
     */
    public function update(object $entity, $isFlush = true)
    {
        $this->_em->merge($entity);
        if ($isFlush) {
            $this->_em->flush();
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @param bool   $isFlush
     *
     * @return object
     */
    public function create(object $entity, $isFlush = true)
    {
        $this->_em->persist($entity);
        if ($isFlush) {
            $this->_em->flush();
        }

        return $entity;
    }

    /**
     * @param      $entity
     * @param bool $isFlush
     *
     * @return object
     */
    public function save(object $entity, $isFlush = true)
    {
        if (($entity->getId() ?? 0) > 0) {
            $this->update($entity, $isFlush);
        } else {
            $this->create($entity, $isFlush);
        }

        return $entity;
    }

    /**
     * @param array $searchPost
     *
     * @return mixed
     */
    public function countRecord(array $searchPost = [])
    {
        $alias = $this->getDfAlias();
        /**
         * @var QueryBuilder $qb
         */
        $qb = $this->searchWith($searchPost);
        $qb->select("COUNT($alias.id)");

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array  $searchPost
     * @param string $orderBy
     * @param string $orderDirection
     *
     * @return QueryBuilder
     */
    public function searchWith(
        array $searchPost = [],
        string $orderBy = 'id',
        string $orderDirection = 'ASC'
    ) {
        $alias = $this->getDfAlias();

        $qb = $this->createQueryBuilder($alias);
        $qb = $this->buildQuery($qb, $searchPost);

        $qb = $this->applyOrderBy($qb, $orderBy, $orderDirection);

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $params
     *
     * @return QueryBuilder
     */
    public function buildQuery(QueryBuilder $qb, $params = [])
    {
        if (empty($params)) {
            return $qb;
        }
        $dfAlias = $this->getDfAlias();
        $except = ['pageNum', 'pageSize'];
        $index = 0;
        foreach ($params as $field => $value) {
            if (is_null($value) || in_array($field, $except)) {
                continue;
            }
            $field = strtolower($field);
            $operator = '=';
            if (strpos($field, ' ~=') !== false) {
                $field = trim(str_replace('~=', '', $field));
                $value = '%'.$value.'%';
                $operator = 'LIKE';
            } elseif (strpos($field, ' !=') !== false) {
                $field = trim(str_replace('!=', '', $field));
                $operator = '!= ';
            } elseif (strpos($field, ' lte') !== false) {
                $field = trim(str_replace('lte', '', $field));
                $operator = '<=';
            } elseif (strpos($field, ' lt') !== false) {
                $field = trim(str_replace('lt', '', $field));
                $operator = '<';
            } elseif (strpos($field, ' gte') !== false) {
                $field = trim(str_replace('gte', '', $field));
                $operator = '>=';
            } elseif (strpos($field, ' gt') !== false) {
                $field = trim(str_replace('gt', '', $field));
                $operator = '>';
            } elseif (strpos($field, '=') !== false) {
                $field = trim(str_replace('=', '', $field));
                $operator = '=';
            } elseif (strpos($field, '*in') !== false) {
                $field = trim(str_replace('*in', '', $field));
                $operator = "IN";
                $value = explode(',', $value);
            } elseif (strpos($field, 'is_null') !== false) {
                $field = trim(str_replace('is_null', '', $field));
                $operator = "IS_NULL";
                $value = explode(',', $value);
            }
            $parameterKey = $field.$index;
            if ($operator === 'IN') {
                $qb->andWhere("$dfAlias.$field  IN(:$parameterKey)")
                    ->setParameter($parameterKey, $value);
            } elseif ($operator === 'IS_NULL') {
                $qb->andWhere("$dfAlias.$field  IS NULL");
            } else {
                $qb->andWhere("$dfAlias.$field  $operator  :$parameterKey")
                    ->setParameter($parameterKey, $value);
            }

            $index++;
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $orderBy
     * @param string       $orderDirection
     *
     * @return QueryBuilder
     */
    public function applyOrderBy($qb, string $orderBy = 'createdAt', string $orderDirection = 'ASC')
    {
        $alias = $this->getDfAlias();
        if ($qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            $orderBy = Helper::fromCamelCase($orderBy);
        }

        return $qb->orderBy("$alias.$orderBy", $orderDirection);
    }
}
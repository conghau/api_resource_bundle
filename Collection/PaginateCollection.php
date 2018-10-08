<?php
/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 09/01/2017
 * Time: 14:17
 */

namespace conghau\Bundle\ApiResource\Pagination;

/**
 * Class PaginationCollection
 *
 */
class PaginateCollection
{
    private $items;
    private $total;
    private $count;
    private $pageNumber;
    private $pageSize;

    /**
     * PaginateCollection constructor.
     *
     * @param array $items
     * @param int   $total
     * @param int   $pageNumber
     * @param int   $pageSize
     */
    public function __construct(array $items, int $total, int $pageNumber, int $pageSize)
    {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

}
<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use League\Fractal\Pagination\PaginatorInterface;

final class Paginator implements PaginatorInterface
{
    /**
     * The default per page limit
     */
    const DEFAULT_LIMIT = 10;

    /**
     * The maximum per page limit
     */
    const MAX_LIMIT = 100;

    /**
     * The request object
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Final data
     *
     * @var Builder|Relation;
     */
    private $data;

    /**
     * Total number of entries
     *
     * @var int
     */
    private $total;

    /**
     * Per page limit
     *
     * @var int
     */
    private $limit;

    /**
     * The current page number
     *
     * @var int
     */
    private $currentPage;

    /**
     * Class constructor
     *
     * @param \Illuminate\Http\Request $request
     * @param Builder|Relation         $query
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Request $request, $query)
    {
        if (!($query instanceof Builder || $query instanceof Relation)) {
            throw new \InvalidArgumentException();
        }

        $this->request = $request;

        $this->total = $query->count();

        $paging = $request->input('page', null);
        $this->currentPage = max(1, empty($paging['number']) ? 1 : intval($paging['number']));
        $this->limit = max(1, min(self::MAX_LIMIT, (!isset($paging['limit']) ? self::DEFAULT_LIMIT : intval($paging['limit']))));
        $skip = (($this->currentPage - 1) * $this->limit);

        $this->data = $query->skip($skip)->take($this->limit)->get();
    }

    /**
     * Get the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return max(ceil($this->total / $this->limit), 1);
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->data);
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->limit;
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     *
     * @return string
     */
    public function getUrl($page)
    {
        if (self::DEFAULT_LIMIT === $this->limit) {
            return $this->request->fullUrlWithQuery(['page' => ['number' => $page]]);
        }

        return $this->request->fullUrlWithQuery(['page' => ['limit' => $this->limit, 'number' => $page]]);
    }
}

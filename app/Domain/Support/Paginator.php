<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use League\Fractal\Pagination\PaginatorInterface;

final class Paginator implements PaginatorInterface
{
    /**
     * The default page limit
     */
    const DEFAULT_LIMIT = 10;

    /**
     * The request object
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Final data
     *
     * @var \Illuminate\Database\Eloquent\Builder
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

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;

        $this->total = $query->count();

        $paging = $request->input('page', null);
        $this->currentPage = max(empty($paging['number']) ? 1 : intval($paging['number']), 1);
        $this->limit = max(min((empty($paging['limit']) ? self::DEFAULT_LIMIT : intval($paging['limit'])), 100), 1);
        $skip = (($this->currentPage - 1) * $this->limit);

        $this->data = $query->skip($skip)->take($this->limit);
    }

    /**
     * Get the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data->get();
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
        return $this->data->count();
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

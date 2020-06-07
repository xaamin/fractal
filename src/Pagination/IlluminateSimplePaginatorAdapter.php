<?php
namespace Xaamin\Fractal\Pagination;

use Illuminate\Pagination\Paginator;
use League\Fractal\Pagination\PaginatorInterface;

class IlluminateSimplePaginatorAdapter implements PaginatorInterface
{
    /**
     * The paginator instance.
     *
     * @var \Illuminate\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new illuminate pagination adapter.
     *
     * @param \Illuminate\Pagination\Paginator $paginator
     *
     * @return void
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return null;
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return null;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->paginator->count();
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->paginator->perPage();
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
        return $this->paginator->url($page);
    }

    /**
     * Get the paginator instance.
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}

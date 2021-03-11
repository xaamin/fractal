<?php namespace Xaamin\Fractal;

use LogicException;
use BadMethodCallException;
use League\Fractal\Manager;
use Illuminate\Http\Request;
use League\Fractal\Resource\Item;
use Illuminate\Pagination\Paginator;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use Illuminate\Contracts\Support\Arrayable;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\Pagination\PaginatorInterface;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class Transformer
{
    /**
     * @var \League\Fractal\Manager
     */
    protected $manager;

    protected $paginator;

    protected $cursor;

    protected $includes = [];

    protected $excludes = [];

    public function __construct(SerializerAbstract $serializer = null)
    {
        $this->manager = new Manager();
        $this->manager->setSerializer($serializer ? : new ArraySerializer);
    }

    public function including($includes)
    {
        return $this->with($includes);
    }

    public function excluding($includes)
    {
        return $this->without($includes);
    }

    public function embedding($includes)
    {
        return $this->with($includes);
    }

    public function with($includes)
    {
        $this->includes = $this->parseRequestedIncludes($includes);

        return $this;
    }

    public function without($includes)
    {
        $this->excludes = $this->parseRequestedIncludes($includes);

        return $this;
    }

    protected function parseRequestedIncludes($includes) {
        if (is_string($includes)) {
            $includes = explode(',', $includes);
            $includes = array_filter($includes, 'trim');
            $includes = array_map('trim', $includes);
        }

        return $includes;
    }

    public function collection($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Collection($data, $this->getTransformer($transformer), $resourceKey);

        if ($this->cursor and $this->paginator) {
            throw new LogicException('Only one pagination strategy must be specified, received both pagination and cursor');
        }

        if ($this->paginator) {
            $resource->setPaginator($this->paginator);
        }

        if ($this->cursor) {
            $resource->setCursor($this->cursor);
        }

        return $this->createData($resource);
    }

    public function item($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Item($data, $this->getTransformer($transformer), $resourceKey);
        return $this->createData($resource);
    }

    public function paginate(LengthAwarePaginator $paginator, $transformer = null, $resourceKey = null)
    {
        $resource = new Collection($paginator->items(), $this->getTransformer($transformer), $resourceKey);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $this->createData($resource);
    }

    public function simplePaginate(Paginator $paginator, $transformer = null, $resourceKey = null)
    {
        $data = $paginator->items();

        $meta = [
            'current' => $paginator->currentPage(),
            'previous' => $paginator->currentPage() - 1 ? $paginator->currentPage() - 1 : null,
            'next' => $paginator->currentPage() + 1,
        ];

        return $this->cursor($data, $transformer, $meta, $resourceKey);
    }

    public function cursor($data, $transformer = null, array $meta = [], $resourceKey = null)
    {
        $current = (isset($meta['current']) and trim($meta['current']) !== '') ? $meta['current'] : null;
        $previous = (isset($meta['previous']) and trim($meta['previous']) !== '') ? $meta['previous'] : null;
        $next = (isset($meta['next']) and trim($meta['next']) !== '') ? $meta['next'] : null;

        $resource = new Collection($data, $this->getTransformer($transformer), $resourceKey);
        $cursor = new Cursor($current, $previous, $next, count($data));

        $resource->setCursor($cursor);

        return $this->createData($resource);
    }

    public function paginateUsing(PaginatorInterface $paginator)
    {
        $this->setPaginator($paginator);

        return $this;
    }

    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    public function paginateUsingCursor(CursorInterface $cursor)
    {
        $this->setCursor($cursor);

        return $this;
    }

    public function setCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;

        return $this;
    }

    protected function getTransformer($transformer = null)
    {
        return $transformer ?: function($data) {
            if($data instanceof Arrayable) {
                return $data->toArray();
            }
            return (array) $data;
        };
    }

    protected function createData($resource)
    {
        $this->manager->parseIncludes($this->includes);
        $this->manager->parseExcludes($this->excludes);

        $data = $this->manager->createData($resource)->toArray();

        $this->includes = [];

        return $data;
    }

    public function __call($method, $parameters)
    {
        if (is_callable([$this->manager, $method])) {
            return call_user_func_array([$this->manager, $method], $parameters);
        }

        throw new BadMethodCallException("Error Processing Request", 1);
    }
}
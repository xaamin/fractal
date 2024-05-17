<?php namespace Xaamin\Fractal;

use LogicException;
use BadMethodCallException;
use Illuminate\Support\Arr;
use League\Fractal\Manager;
use InvalidArgumentException;
use League\Fractal\Resource\Item;
use Illuminate\Pagination\Paginator;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Primitive;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection;
use Illuminate\Contracts\Support\Arrayable;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\SerializerAbstract;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Xaamin\Fractal\Pagination\IlluminateSimplePaginatorAdapter;

class Fractal
{
    /**
     * @var \League\Fractal\Manager
     */
    protected $manager;

    protected $paginator;

    protected $cursor;

    protected $resource;

    protected $includes = [];

    protected $excludes = [];

    /**
     * Default serializaer
     *
     * @var \League\Fractal\Serializer\SerializerAbstract
     */
    protected static $serializer;

    /**
     * Constructor
     *
     * @param Manager|null $manager
     * @param SerializerAbstract|null $serializer
     */
    public function __construct(Manager $manager = null, SerializerAbstract $serializer = null)
    {
        if ($serializer && !static::$serializer) {
            static::$serializer = $serializer;
        }

        $defaultSerializer = static::$serializer;

        $this->manager = $manager ? : new Manager();
        $this->manager->setSerializer($serializer ? : ($defaultSerializer ? : new ArraySerializer));
    }

    /**
     * Set the default serializer
     *
     * @param SerializerAbstract $serializer
     *
     * @return void
     */
    public static function setDefaultSerializer(SerializerAbstract $serializer)
    {
        static::$serializer = $serializer;
    }

    /**
     * Create a new resource by guessing the type
     *
     * @param array|array<midex> $data
     * @param null|string|Closure|\League\Fractal\TransformerAbstract $transformer
     * @param string|null $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public static function make($data = null, $transformer = null, $resourceKey = null)
    {
        $fractal = new static(new Manager(), static::$serializer);

        if ($data instanceof LengthAwarePaginator || $data instanceof Paginator) {
            $fractal->paginate($data, $transformer, $resourceKey);
        } else if (is_array($data)) {
            if (Arr::isAssoc($data)) {
                $fractal->item($data, $transformer, $resourceKey);
            } else {
                $fractal->collection($data, $transformer, $resourceKey);
            }
        } else if ($data instanceof Model) {
            $fractal->item($data, $transformer, $resourceKey);
        } else if ($data instanceof LaravelCollection) {
            $fractal->collection($data, $transformer, $resourceKey);
        } else {
            $fractal->primitive($data, $transformer, $resourceKey);
        }

        return $fractal;
    }

    /**
     * Add includes
     *
     * @param string@string[] $includes
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function including($includes)
    {
        return $this->with($includes);
    }

    /**
     * Omit includes
     *
     * @param string@string[] $includes
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function excluding($includes)
    {
        return $this->without($includes);
    }

    /**
     * Add includes
     *
     * @param string@string[] $includes
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function with($includes)
    {
        $this->includes = $includes === null ? '' : $includes;

        return $this;
    }

    /**
     * Omit includes
     *
     * @param string@string[] $includes
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function without($excludes)
    {
        $this->excludes = $excludes === null ? '' : $excludes;

        return $this;
    }

    /**
     * Get all includes
     *
     * @return string[]
     */
    public function getIncludes()
    {
        return $this->manager
            ->parseIncludes($this->includes)
            ->parseExcludes($this->excludes)
            ->getRequestedIncludes();
    }

    /**
     * Get excluded includes
     *
     * @param string@string[] $includes
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function getExcludes()
    {
        return $this->manager
            ->parseIncludes($this->includes)
            ->parseExcludes($this->excludes)
            ->getRequestedExcludes();
    }

    /**
     *  Set the collection data that must be transformed
     *
     * @param mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param string|null $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function collection($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Collection($data, $this->getTransformer($transformer), $resourceKey);

        if ($this->cursor && $this->paginator) {
            throw new LogicException('Only one pagination strategy must be specified, received both pagination and cursor');
        }

        if ($this->paginator) {
            $resource->setPaginator($this->paginator);
        }

        if ($this->cursor) {
            $resource->setCursor($this->cursor);
        }

        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the item data that must be transformed.
     *
     * @param mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param string|null $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function primitive($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Primitive($data, $transformer, $resourceKey);

        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the item data that must be transformed.
     *
     * @param mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param string|null $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function item($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Item($data, $transformer, $resourceKey);

        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the paginated data that must be transformed.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator|Illuminate\Contracts\Pagination\Paginator $paginator
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param string|null $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function paginate($paginator, $transformer = null, $resourceKey = null)
    {
        $adapter = null;

        switch (true) {
            case $paginator instanceof LengthAwarePaginator:
                $adapter = new IlluminatePaginatorAdapter($paginator);
                break;
            case $paginator instanceof Paginator:
                $adapter = new IlluminateSimplePaginatorAdapter($paginator);
                break;
            default:
                throw new InvalidArgumentException('Class ' . get_class($paginator) . ' is not a paginator instance.');
                break;
        }

        $resource = new Collection($paginator->items(), $this->getTransformer($transformer), $resourceKey);
        $resource->setPaginator($adapter);

        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the cursor data that must be transformed.
     *
     * @param array<string,string|int>
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param string|null $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function cursor($data, $transformer = null, array $meta = [], $resourceKey = null)
    {
        $current = (isset($meta['current']) and trim($meta['current']) !== '') ? $meta['current'] : null;
        $previous = (isset($meta['previous']) and trim($meta['previous']) !== '') ? $meta['previous'] : null;
        $next = (isset($meta['next']) and trim($meta['next']) !== '') ? $meta['next'] : null;

        $resource = new Collection($data, $this->getTransformer($transformer), $resourceKey);
        $cursor = new Cursor($current, $previous, $next, count($data));

        $resource->setCursor($cursor);

        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the paginator for the data
     *
     * @param PaginatorInterface $paginator
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Set the cursor for the data
     *
     * @param CursorInterface $cursor
     *
     * @return \Xaamin\Fractal\Fractal
     */
    public function setCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;

        return $this;
    }

    /**
     * Get the transfomer to be used
     *
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     *
     * @return callable|\League\Fractal\TransformerAbstract
     */
    protected function getTransformer($transformer = null)
    {
        return $transformer ?: function($data) {
            if ($data instanceof Arrayable) {
                return $data->toArray();
            }

            return (array) $data;
        };
    }

    /**
     * Perform the transformation to array.
     *
     * @return array|null
     */
    public function toArray()
    {
        $this->manager->parseIncludes($this->includes);
        $this->manager->parseExcludes($this->excludes);

        $data = $this->manager->createData($this->resource)->toArray();

        return $data;
    }

    /**
     * Perform the transformation to json.
     *
     * @param int $flags
     *
     * @return string
     */
    public function toJson($flags = 0)
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * Perform the transformation to string.
     *
     * @return string
     */
    public function toString()
    {
        return $this->toJson();
    }

    /**
     * Perform the transformation to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    public function __call($method, $parameters)
    {
        if (is_callable([$this->manager, $method])) {
            return call_user_func_array([$this->manager, $method], $parameters);
        }

        throw new BadMethodCallException('Bad method call');
    }
}

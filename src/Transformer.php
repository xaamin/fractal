<?php  namespace Xaamin\Fractal;

use BadMethodCallException;
use League\Fractal\Manager;
use Illuminate\Http\Request;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use Illuminate\Contracts\Support\Arrayable;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\Pagination\PaginatorInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class Transformer
{
    /**
     * @var \League\Fractal\Manager
     */
    protected $manager;

    protected $paginator;

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

        if ($this->paginator) {
            $resource->setPaginator($this->paginator);
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

    public function paginateUsing(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

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
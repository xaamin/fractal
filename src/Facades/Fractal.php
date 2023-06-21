<?php
namespace Xaamin\Fractal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Xaamin\Fractal\Fractal including(array|string $includes)
 * @method static \Xaamin\Fractal\Fractal excluding(array|string $includes)
 * @method static \Xaamin\Fractal\Fractal with(array|string $includes)
 * @method static \Xaamin\Fractal\Fractal without(array|string $excludes)
 * @method static \Xaamin\Fractal\Fractal getIncludes()
 * @method static \Xaamin\Fractal\Fractal getExcludes()
 * @method static \Xaamin\Fractal\Fractal primitive($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal collection($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal item($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal paginate($paginator, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal cursor($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, array $meta = [], $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal setPaginator(\League\Fractal\Pagination\PaginatorInterface $paginator)
 * @method static \Xaamin\Fractal\Fractal setCursor(\League\Fractal\Pagination\CursorInterface $cursor)
 * @method static array toArray()
 * @method static string toJson()
 * @method static string toString()
 */
class Fractal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Xaamin\Fractal\Fractal';
    }
}

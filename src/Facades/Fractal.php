<?php
namespace Xaamin\Fractal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Xaamin\Fractal\Fractal make(mixed $data = null, null|callable|\League\Fractal\TransformerAbstract $transformer = null, string|null $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal including(string|string[]|null $includes)
 * @method static \Xaamin\Fractal\Fractal excluding(string|string[]|null $includes)
 * @method static \Xaamin\Fractal\Fractal with(string|string[]|null $includes)
 * @method static \Xaamin\Fractal\Fractal without(string|string[]|null $excludes)
 * @method static string[] getIncludes()
 * @method static string[] getExcludes()
 * @method static \Xaamin\Fractal\Fractal primitive($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal collection($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal item($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal paginate($paginator, null|callable|\League\Fractal\TransformerAbstract $transformer = null, $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal cursor($data, null|callable|\League\Fractal\TransformerAbstract $transformer = null, array<string, string|int|null> $meta = [], $resourceKey = null)
 * @method static \Xaamin\Fractal\Fractal setPaginator(\League\Fractal\Pagination\PaginatorInterface $paginator)
 * @method static \Xaamin\Fractal\Fractal setCursor(\League\Fractal\Pagination\CursorInterface $cursor)
 * @method static array<string, mixed>|null toArray()
 * @method static string toJson(int $flags = 0)
 * @method static string toString()
 */
class Fractal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Xaamin\Fractal\Fractal';
    }
}

# Fractal for Laravel

A small Laravel wrapper around [league/fractal](https://fractal.thephpleague.com) with a fluent API, automatic resource detection, Laravel paginator support and a flat `ArraySerializer` by default.

```php
return fractal($users, new UserTransformer())->including('posts')->toArray();
```

## Installation

```bash
composer require xaamin/fractal
```

The service provider (`Xaamin\Fractal\Providers\FractalServiceProvider`) is registered through package auto-discovery. Optionally add a facade alias in `config/app.php`:

```php
'aliases' => [
    'Fractal' => Xaamin\Fractal\Facades\Fractal::class,
],
```

## Usage

You can create a transformation with the `fractal()` helper, the `Fractal` facade or `Xaamin\Fractal\Fractal::make()`. They all take the same arguments: `($data, $transformer = null, $resourceKey = null)`.

### Resource detection

`make()` picks the resource type from the data:

| Data | Resource |
| --- | --- |
| Associative array, Eloquent `Model` | Item |
| List array, `Illuminate\Support\Collection` | Collection |
| `LengthAwarePaginator`, `Paginator` | Paginated collection (resource key `data`) |

```php
fractal(['id' => 1, 'name' => 'Ana'])->toArray();
// ['id' => 1, 'name' => 'Ana']

fractal([['id' => 1], ['id' => 2]])->toArray();
// [['id' => 1], ['id' => 2]]
```

You can also set the resource explicitly:

```php
use Xaamin\Fractal\Fractal;

(new Fractal())->item($user, new UserTransformer())->toArray();
(new Fractal())->collection($users, new UserTransformer())->toArray();
```

### Transformers

Without a transformer, `Arrayable` objects (models, collections) are converted with `toArray()` and any other value is cast to array. A transformer can be a closure or a `League\Fractal\TransformerAbstract`:

```php
fractal($user, fn ($user) => ['id' => $user->id, 'name' => $user->name])->toArray();
```

```php
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['posts'];

    public function transform(User $user)
    {
        return ['id' => $user->id, 'name' => $user->name];
    }

    public function includePosts(User $user)
    {
        return $this->collection($user->posts, new PostTransformer());
    }
}
```

### Resource keys

A resource key wraps the output:

```php
fractal(['id' => 1], null, 'user')->toArray();
// ['user' => ['id' => 1]]

fractal([['id' => 1]], null, 'users')->toArray();
// ['users' => [['id' => 1]]]
```

### Includes and excludes

Includes accept an array or a comma separated string. `including()`/`excluding()` are aliases of `with()`/`without()`.

```php
fractal($user, new UserTransformer())
    ->including(['posts', 'profile'])
    ->excluding('profile')
    ->toArray();

// Typical usage from a request
fractal($user, new UserTransformer())->with($request->query('include'))->toArray();
```

### Meta

```php
fractal($users)
    ->withMeta(['version' => 1])   // merged recursively
    ->withMeta(['source' => 'api'])
    ->toArray();
// [..., 'meta' => ['version' => 1, 'source' => 'api']]

fractal($users)->setMeta(['only' => true]); // replaces the meta
```

### Pagination

Laravel paginators are detected automatically:

```php
fractal(User::paginate(15), new UserTransformer())->toArray();

// [
//     'data' => [...],
//     'meta' => [
//         'pagination' => [
//             'total' => 50, 'count' => 15, 'per_page' => 15,
//             'current_page' => 1, 'total_pages' => 4,
//             'links' => ['next' => 'https://example.com/users?page=2'],
//         ],
//     ],
// ]
```

`simplePaginate()` results are supported too; their pagination meta contains `count`, `per_page`, `current_page` and the `previous`/`next` links.

You can also call `paginate($paginator, $transformer, $resourceKey)` directly, or attach any `League\Fractal\Pagination\PaginatorInterface` with `setPaginator()` before calling `collection()`.

### Cursors

```php
(new Fractal())
    ->cursor($users, new UserTransformer(), ['current' => 10, 'previous' => 5, 'next' => 15])
    ->toArray();

// [..., 'meta' => ['cursor' => ['current' => 10, 'prev' => 5, 'next' => 15, 'count' => 2]]]
```

Alternatively, attach a `League\Fractal\Pagination\Cursor` with `setCursor()` before calling `collection()`. A paginator and a cursor cannot be used at the same time.

### Output

```php
$fractal = fractal(['id' => 1]);

$fractal->toArray();                // ['id' => 1]
$fractal->toJson();                 // '{"id":1}'
$fractal->toJson(JSON_PRETTY_PRINT);
(string) $fractal;                  // '{"id":1}'
```

Returning `fractal(...)->toArray()` from a controller produces a JSON response.

### Serializers

`Xaamin\Fractal\Serializer\ArraySerializer` is used by default: it does not add a `data` key unless a resource key is given. Change it globally (e.g. in a service provider's `boot()` method) with any Fractal serializer:

```php
use League\Fractal\Serializer\DataArraySerializer;
use Xaamin\Fractal\Fractal;

Fractal::setDefaultSerializer(new DataArraySerializer());

fractal(['id' => 1])->toArray();
// ['data' => ['id' => 1]]
```

Any other method is forwarded to the underlying `League\Fractal\Manager` (e.g. `setRecursionLimit()`).

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

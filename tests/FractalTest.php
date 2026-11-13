<?php

use stdClass;
use LogicException;
use BadMethodCallException;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Primitive;
use League\Fractal\Serializer\DataArraySerializer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection as LaravelCollection;
use Xaamin\Fractal\Fractal;
use Xaamin\Fractal\Serializer\ArraySerializer;
use Xaamin\Fractal\Pagination\IlluminateSimplePaginatorAdapter;
use Xaamin\Fractal\Tests\Fixtures\DummyModel;
use Xaamin\Fractal\Tests\Fixtures\UserTransformer;

test('assoc array becomes item', function () {
    $fractal = Fractal::make(['id' => 1, 'name' => 'Ana']);

    $this->assertInstanceOf(Item::class, $fractal->getResource());
    $this->assertSame(['id' => 1, 'name' => 'Ana'], $fractal->toArray());
});

test('list array becomes collection', function () {
    $data = [['id' => 1], ['id' => 2]];
    $fractal = Fractal::make($data);

    $this->assertInstanceOf(Collection::class, $fractal->getResource());
    $this->assertSame($data, $fractal->toArray());
});

test('resource key wraps output', function () {
    $this->assertSame(
        ['user' => ['id' => 1]],
        Fractal::make(['id' => 1], null, 'user')->toArray()
    );
    $this->assertSame(
        ['users' => [['id' => 1]]],
        Fractal::make([['id' => 1]], null, 'users')->toArray()
    );
});

test('laravel collection becomes collection', function () {
    $fractal = Fractal::make(new LaravelCollection([['id' => 1], ['id' => 2]]));

    $this->assertInstanceOf(Collection::class, $fractal->getResource());
    $this->assertCount(2, $fractal->toArray());
});

test('model becomes item using arrayable', function () {
    $fractal = Fractal::make(new DummyModel(['id' => 5, 'name' => 'Bob']));

    $this->assertInstanceOf(Item::class, $fractal->getResource());
    $this->assertSame(['id' => 5, 'name' => 'Bob'], $fractal->toArray());
});

test('scalar becomes primitive', function () {
    $fractal = Fractal::make('hello', fn ($v) => strtoupper($v));

    $this->assertInstanceOf(Primitive::class, $fractal->getResource());
});

test('default transformer casts objects to array', function () {
    $obj = new stdClass;
    $obj->a = 1;

    $this->assertSame(['a' => 1], (new Fractal)->item($obj)->toArray());
});

test('custom transformer closure', function () {
    $out = Fractal::make(['id' => 1, 'name' => 'x'], fn ($u) => ['n' => $u['name']])->toArray();

    $this->assertSame(['n' => 'x'], $out);
});

test('get resource without resource throws', function () {
    $this->expectException(LogicException::class);

    (new Fractal)->getResource();
});

test('includes', function () {
    $user = ['id' => 1, 'name' => 'Ana'];

    $out = Fractal::make($user, new UserTransformer)->including('posts')->toArray();
    $this->assertSame(['A', 'B'], array_column($out['posts']['posts'], 'title'));
    $this->assertArrayNotHasKey('profile', $out);

    $out = Fractal::make($user, new UserTransformer)->with(['posts', 'profile'])->toArray();
    $this->assertArrayHasKey('posts', $out);
    $this->assertSame(['bio' => 'hi'], $out['profile']['profile']);
});

test('get includes and excludes', function () {
    $fractal = Fractal::make(['id' => 1, 'name' => 'a'], new UserTransformer)
        ->with('posts,profile')
        ->without('profile');

    $this->assertSame(['posts', 'profile'], $fractal->getIncludes());
    $this->assertSame(['profile'], $fractal->getExcludes());
});

test('excluding removes include', function () {
    $out = Fractal::make(['id' => 1, 'name' => 'a'], new UserTransformer)
        ->including(['posts', 'profile'])
        ->excluding('profile')
        ->toArray();

    $this->assertArrayHasKey('posts', $out);
    $this->assertArrayNotHasKey('profile', $out);
});

test('null includes are accepted', function () {
    $out = Fractal::make(['id' => 1, 'name' => 'a'], new UserTransformer)
        ->with(null)
        ->without(null)
        ->toArray();

    $this->assertSame(['id' => 1, 'name' => 'a'], $out);
});

test('meta merging and replacing', function () {
    $fractal = Fractal::make([['id' => 1]])
        ->withMeta(['a' => ['x' => 1]])
        ->withMeta(['a' => ['y' => 2], 'b' => 3]);

    $this->assertSame(
        ['a' => ['x' => 1, 'y' => 2], 'b' => 3],
        $fractal->toArray()['meta']
    );

    $fractal->setMeta(['only' => true]);
    $this->assertSame(['only' => true], $fractal->toArray()['meta']);
});

test('meta does not leak into original resource', function () {
    $fractal = Fractal::make([['id' => 1]])->withMeta(['a' => 1]);
    $fractal->toArray();

    $this->assertSame([], $fractal->getResource()->getMeta());
});

test('to json and to string', function () {
    $fractal = Fractal::make(['id' => 1]);

    $this->assertSame('{"id":1}', $fractal->toJson());
    $this->assertSame('{"id":1}', $fractal->toString());
    $this->assertSame('{"id":1}', (string) $fractal);
    $this->assertSame("{\n    \"id\": 1\n}", $fractal->toJson(JSON_PRETTY_PRINT));
});

test('cursor', function () {
    $out = (new Fractal)
        ->cursor([['id' => 1], ['id' => 2]], null, ['current' => 'a', 'previous' => ' ', 'next' => 'c'])
        ->toArray();

    $this->assertSame(['id' => 1], $out[0]);
    $this->assertSame('a', $out['meta']['cursor']['current']);
    $this->assertNull($out['meta']['cursor']['prev']);
    $this->assertSame('c', $out['meta']['cursor']['next']);
    $this->assertSame(2, $out['meta']['cursor']['count']);
});

test('set cursor and paginator together throws', function () {
    $fractal = (new Fractal)
        ->setCursor(new Cursor(1, null, 2, 1))
        ->setPaginator(new IlluminateSimplePaginatorAdapter(new Paginator([], 5)));

    $this->expectException(LogicException::class);
    $fractal->collection([['id' => 1]]);
});

test('set cursor is applied to collection', function () {
    $out = (new Fractal)->setCursor(new Cursor(1, null, 2, 1))->collection([['id' => 1]])->toArray();

    $this->assertSame(2, $out['meta']['cursor']['next']);
});

test('paginate rejects invalid paginator', function () {
    $this->expectException(\InvalidArgumentException::class);

    (new Fractal)->paginate(new stdClass);
});

test('set default serializer', function () {
    Fractal::setDefaultSerializer(new DataArraySerializer);

    $this->assertSame(['data' => ['id' => 1]], Fractal::make(['id' => 1])->toArray());
});

test('constructor serializer is used and becomes default once', function () {
    $first = new DataArraySerializer;
    new Fractal(new Manager, $first);
    new Fractal(new Manager, new ArraySerializer);

    $this->assertSame(['data' => ['id' => 1]], Fractal::make(['id' => 1])->toArray());
});

test('magic call proxies to manager', function () {
    $fractal = new Fractal;

    $this->assertInstanceOf(Manager::class, $fractal->setRecursionLimit(3));
    $this->assertSame([], $fractal->getRequestedIncludes());
});

test('magic call unknown method throws', function () {
    $this->expectException(BadMethodCallException::class);

    (new Fractal)->nonExistentMethod();
});

test('primitive resources are transformed at the top level', function () {
    $this->assertSame(84, Fractal::make(42, fn ($v) => $v * 2)->toArray());
    $this->assertSame('hello', Fractal::make('hello')->toArray());
    $this->assertSame('"HELLO"', Fractal::make('hello', fn ($v) => strtoupper($v))->toJson());
});

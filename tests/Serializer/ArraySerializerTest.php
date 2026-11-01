<?php

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Xaamin\Fractal\Fractal;

test('length aware paginator', function () {
    $paginator = new LengthAwarePaginator([['id' => 1], ['id' => 2]], 5, 2, 1, ['path' => 'http://localhost/items']);

    $out = Fractal::make($paginator)->toArray();

    $this->assertCount(2, $out['data']);
    $this->assertSame(5, $out['meta']['pagination']['total']);
    $this->assertSame(3, $out['meta']['pagination']['total_pages']);
    $this->assertSame('http://localhost/items' . '?page=2', $out['meta']['pagination']['links']['next']);
});

test('simple paginator links', function () {
    $items = [['id' => 1], ['id' => 2]];
    $paginator = new Paginator($items, 2, 2, ['path' => 'http://localhost/items']);

    $out = Fractal::make($paginator, null, 'items')->toArray();

    $this->assertCount(2, $out['items']);
    $pagination = $out['meta']['pagination'];
    $this->assertSame(2, $pagination['count']);
    $this->assertSame(2, $pagination['per_page']);
    $this->assertSame(2, $pagination['current_page']);
    $this->assertSame(['previous' => 'http://localhost/items' . '?page=1', 'next' => 'http://localhost/items' . '?page=3'], $pagination['links']);
});

test('simple paginator without links yields empty object', function () {
    $paginator = new Paginator([['id' => 1]], 5, 1, ['path' => 'http://localhost/items']);

    $pagination = Fractal::make($paginator)->toArray()['meta']['pagination'];

    $this->assertEquals((object) [], $pagination['links']);
});

test('default serializer is xaamin array serializer', function () {
    $out = Fractal::make([['id' => 1]], null, 'data')->toArray();

    $this->assertSame(['data' => [['id' => 1]]], $out);
});

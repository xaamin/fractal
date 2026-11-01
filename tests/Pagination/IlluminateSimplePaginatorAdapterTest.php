<?php

use Illuminate\Pagination\Paginator;
use Xaamin\Fractal\Pagination\IlluminateSimplePaginatorAdapter;

test('exposes paginator data', function () {
    $paginator = new Paginator([['id' => 1], ['id' => 2]], 2, 3, ['path' => 'http://localhost/items']);
    $adapter = new IlluminateSimplePaginatorAdapter($paginator);

    $this->assertSame(3, $adapter->getCurrentPage());
    $this->assertSame(2, $adapter->getCount());
    $this->assertSame(2, $adapter->getPerPage());
    $this->assertNull($adapter->getLastPage());
    $this->assertNull($adapter->getTotal());
    $this->assertSame('http://localhost/items?page=4', $adapter->getUrl(4));
    $this->assertSame($paginator, $adapter->getPaginator());
});

<?php

namespace Xaamin\Fractal\Tests\Fixtures;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['posts', 'profile'];

    public function transform(array $user)
    {
        return ['id' => $user['id'], 'name' => $user['name']];
    }

    public function includePosts(array $user)
    {
        return $this->collection([['title' => 'A'], ['title' => 'B']], fn ($p) => $p, 'posts');
    }

    public function includeProfile(array $user)
    {
        return $this->item(['bio' => 'hi'], fn ($p) => $p, 'profile');
    }
}

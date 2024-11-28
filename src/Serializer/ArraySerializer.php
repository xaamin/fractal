<?php
namespace Xaamin\Fractal\Serializer;

use League\Fractal\Pagination\PaginatorInterface;
use Xaamin\Fractal\Pagination\IlluminateSimplePaginatorAdapter;
use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

class ArraySerializer extends BaseArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return $resourceKey ? [ $resourceKey => $data ] : $data;
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        return $resourceKey ? [ $resourceKey => $data ] : $data;
    }

    /**
     * Serialize null resource.
     *
     * @return array
     */
    public function null()
    {
        return null;
    }

     /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function paginator(PaginatorInterface $paginator)
    {
        if ($paginator instanceof IlluminateSimplePaginatorAdapter) {
            $currentPage = (int) $paginator->getCurrentPage();
            $lastPage = (int) $paginator->getLastPage();

            $pagination = [
                'count' => (int) $paginator->getCount(),
                'per_page' => (int) $paginator->getPerPage(),
                'current_page' => $currentPage,
            ];

            $pagination['links'] = [];

            if ($currentPage > 1) {
                $pagination['links']['previous'] = $paginator->getUrl($currentPage - 1);
            }

            if ($pagination['count'] >= $pagination['per_page']) {
                $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
            }

            if (empty($pagination['links'])) {
                $pagination['links'] = (object) [];
            }

            return ['pagination' => $pagination];
        }

        return parent::paginator($paginator);
    }

}

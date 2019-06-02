<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

use League\Fractal\Serializer\JsonApiSerializer as BaseSerializer;

final class JsonApiSerializer extends BaseSerializer
{
    /**
     * {@inheritdoc}
     */
    public function meta(array $meta)
    {
        $result = parent::meta($meta);

        // Rename what we call certain keys to better match input
        if (isset($result['meta']['pagination'])) {
            $result['meta']['pagination'] = [
                'total'  => $result['meta']['pagination']['total'],
                'count'  => $result['meta']['pagination']['count'],
                'limit'  => $result['meta']['pagination']['per_page'],
                'number' => $result['meta']['pagination']['current_page'],
                'pages'  => $result['meta']['pagination']['total_pages'],
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function item($resourceKey, array $data)
    {
        if (isset($data['__relationship__'])) {
            return [
                'data' => [
                    'type'       => $resourceKey,
                    'id'         => $this->getIdFromData($data),
                ],
            ];
        }

        return parent::item($resourceKey, $data);
    }
}

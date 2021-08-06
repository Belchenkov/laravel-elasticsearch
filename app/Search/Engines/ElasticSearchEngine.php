<?php

namespace App\Search\Engines;

use Elasticsearch\Client;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class ElasticSearchEngine extends Engine
{

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function update($models)
    {
        $models->each(function ($model) {
            $params = [
                'index' => $model->searchableAs(),
                'type' => $model->searchableAs(),
                'id' => $model->id,
                'body' => $model->toSearchableArray()
            ];

            $this->client->index($params);
        });
    }

    public function delete($models)
    {
        // TODO: Implement delete() method.
    }

    public function search(Builder $builder)
    {
        // TODO: Implement search() method.
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        // TODO: Implement paginate() method.
    }

    public function mapIds($results)
    {
        // TODO: Implement mapIds() method.
    }

    public function map(Builder $builder, $results, $model)
    {
        // TODO: Implement map() method.
    }

    public function lazyMap(Builder $builder, $results, $model)
    {
        // TODO: Implement lazyMap() method.
    }

    public function getTotalCount($results)
    {
        // TODO: Implement getTotalCount() method.
    }

    public function flush($model)
    {
        // TODO: Implement flush() method.
    }

    public function createIndex($name, array $options = [])
    {
        // TODO: Implement createIndex() method.
    }

    public function deleteIndex($name)
    {
        // TODO: Implement deleteIndex() method.
    }
}

<?php

namespace App\Search\Engines;

use Elasticsearch\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
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
            $params = $this->getRequestBody($model, [
                'id' => $model->id,
                'body' => $model->toSearchableArray()
            ]);

            $this->client->index($params);
        });
    }

    public function delete($models)
    {
        $models->each(function ($model) {
           $params = $this->getRequestBody($model, [
               'id' => $model->id,
           ]);

           $this->client->delete($params);
        });
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    /**
     * @param Builder $builder
     * @param array $options
     * @return callable|array
     */
    protected function performSearch(Builder $builder, array $options = []): callable|array
    {
        $params = array_merge_recursive($this->getRequestBody($builder->model), [
            'body' => [
                'from' => 0,
                'size' => 5000,
                'query' => [
                    'multi_match' => [
                        'query' => $builder->query ?? '',
                        'fields' => ['username', 'name', 'email'],
                        'type' => 'phrase_prefix'
                    ]
                ]
            ]
        ], $options);

        return $this->client->search($params);
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, [
            'from' => ($page - 1) * $perPage,
            'size' => 10
        ]);
    }

    public function mapIds($results)
    {
        return collect(Arr::get($results, 'hits.hits'))
            ->pluck('_id')
            ->values();
    }

    /**
     * @param Builder $builder
     * @param mixed $results
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if (count($hits = Arr::get($results, 'hits.hits')) === 0) {
            return $model->newCollection();
        }

        $hits = Arr::get($results, 'hits.hits');

        return $model->getScoutModelsByIds(
            $builder,
            collect($hits)->pluck('_id')->values()->all()
        );
    }

    public function lazyMap(Builder $builder, $results, $model)
    {
        // TODO: Implement lazyMap() method.
    }

    public function getTotalCount($results)
    {
        return Arr::get($results,'hits.total')['value'];
    }

    public function flush($model)
    {
        $this->client->indices()->delete([
           'index' => $model->searchableAs()
        ]);

        Artisan::call('scout:elastic:create', [
            'model' => get_class($model)
        ]);
    }

    public function createIndex($name, array $options = [])
    {
        // TODO: Implement createIndex() method.
    }

    public function deleteIndex($name)
    {
        // TODO: Implement deleteIndex() method.
    }

    /**
     * @param $model
     * @param array $options
     * @return array
     */
    protected function getRequestBody($model, array $options = []): array
    {
        return array_merge_recursive([
            'index' => $model->searchableAs(),
            'type' => $model->searchableAs(),
        ], $options);
    }
}

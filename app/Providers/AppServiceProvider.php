<?php

namespace App\Providers;

use App\Search\Engines\ElasticSearchEngine;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('elasticsearch', function () {
            return ClientBuilder::create()
                ->setHosts([
                    'http://localhost:9200'
                ])
                ->build();
        });

        resolve(EngineManager::class)->extend('elasticsearch', function () {
            return new ElasticSearchEngine(app('elasticsearch'));
        });
    }
}

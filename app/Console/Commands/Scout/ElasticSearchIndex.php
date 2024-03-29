<?php

namespace App\Console\Commands\Scout;

use Illuminate\Console\Command;

class ElasticSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:elastic:create {model}';

    protected $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an ElasticSearch index';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = app('elasticsearch');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! class_exists($model = $this->argument('model'))) {
            return $this->error($model . ' could not be resolved');
        }

        $model = new $model;

        try {
            $this->client->indeces()->create([
               'index' => $model->searchableAs(),
               'body' => [
                   'settings' => [
                       'index' => [
                           'analysis' => [
                               'filter' => $this->filters(),
                               'analyzer' => $this->analyzers(),
                           ]
                       ]
                   ]
               ]
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    protected function filters() {
        return [
            'words_splitter' => [
                'catenate_all' => 'true',
                'type' => 'word_delimiter',
                'preserve_original' => 'true'
            ]
        ];
    }

    protected function analyzers() {
        return [
            'default' => [
                'filter' => ['lowercase', 'words_splitter'],
                'char_filter' => ['html_strip'],
                'type' => 'custom',
                'tokenizer' => 'standard'
            ]
        ];
    }
}

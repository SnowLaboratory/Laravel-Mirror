<?php

namespace SnowBuilds\Mirror\Console;

use Exception;
use Illuminate\Console\Command;
use SnowBuilds\Mirror\Contracts\Scoring;
use SnowBuilds\Mirror\Mirror;
use SnowBuilds\Mirror\Models\Recommendation;

class SyncMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mirror:sync {model?} {--model=} {--model_ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ranking matrix for models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->argument('model') ?? $this->option('model');

        if (is_null($model)) {
            throw new Exception('Model not specified');
        }

        $class = $model;
        $model = resolve($model);

        if(! Mirror::models()->contains(get_class($model))) {
            throw new Exception('Model not registered with Mirror');
        }

        if(! method_exists($model, 'registerRecommendations')) {
            throw new Exception(sprintf('Model does not implement [%s]', Scoring::class));
        }

        $models = is_string($this->option('model_ids'))
            ? $class::findOrFail(explode(',', $this->option('model_ids')))
            : $class::recommendable()->get();
        
        Mirror::strategies($class)->each(function ($strategy) use ($models) {
            $items = $strategy->query()->get();
            $collectionName = $strategy->collection();

            $inserts = collect();

            $model_id = config('mirror.column_names.model_id', 'model_id');
            $model_type = config('mirror.column_names.model_type', 'model_type');
            $recommended_id = config('mirror.column_names.recommended_id', 'recommended_id');
            $recommended_type = config('mirror.column_names.recommended_type', 'recommended_type');
            $type = config('mirror.column_names.type', 'type');
            $score = config('mirror.column_names.score', 'score');

            foreach($models as $model) {
                $modelPk = data_get($model, $model->getKeyName());
                $modelClass = get_class($model);
                foreach($items as $item) {
                    $recommendedPk = data_get($item, $item->getKeyName());
                    $recommendedClass = get_class($item);

                    if ($modelPk === $recommendedPk && $modelClass === $recommendedClass) {
                        continue;
                    }
                    
                    $inserts->push([
                        $model_id => $modelPk,
                        $model_type => $modelClass,
                        $recommended_id => data_get($item, $item->getKeyName()),
                        $recommended_type => get_class($item),
                        $score => $model->compare($item),
                        $type => $collectionName,
                    ]);
                }
            }

            $chunkSize = 50;
            $recommendation = config('mirror.models.recommendation', Recommendation::class);
            // dd($inserts);

            $idChunks = $models->pluck($model->getKeyName())->chunk($chunkSize)->toArray();

            foreach($idChunks as $ids) {
                $recommendation::where([
                    $type => $collectionName,
                    $model_type => $modelClass,
                ])->whereIn($model_id, $ids)
                ->delete();
            }

            $insertChunks = $inserts->chunk($chunkSize);
            foreach($insertChunks as $inserts) {
                $recommendation::upsert($inserts->toArray(), [
                    $type, $model_id, $model_type, $recommended_id, $recommended_type
                ], ['score']);
            }
            
            // dd($strategy->)
            
            // foreach($items as $item) {

            // }
            
        });
    }
}

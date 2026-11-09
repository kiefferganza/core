<?php

namespace App\Support;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class Models
{
    private static function getResourceMap(): array
    {
        return config('models.resource_map');
    }

    private static function getMorphMap(): array
    {
        return config('models.morph_map');
    }

    public static function registerModel(string $model, string $resource, string $morphAlias): void
    {
        config()->set('models.resource_map', array_merge(static::getResourceMap(), [$model => $resource]));
        config()->set('models.morph_map', array_merge(static::getMorphMap(), [$model => $morphAlias]));
    }

    public static function getResource(Model $model): JsonResource
    {
        $class = get_class($model);

        $resource = data_get(static::getResourceMap(), $class);

        throw_if(! $resource, Exception::class, 'No resource was mapped for '.$class);

        return new $resource($model);
    }

    public static function getModelFromAlias(string $alias): string
    {
        $model = array_search($alias, static::getMorphMap());

        throw_if(! $model, Exception::class, 'No model was mapped for '.$alias);

        return $model;
    }
}

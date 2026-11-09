<?php

namespace App\Support\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class AllowedIncludeExtended extends AllowedInclude
{
    /**
     * Since Spatie query builder does not support the feature of customizing eager-loaded relationship,
     * we will implement our own by extending the AllowedInclude class for us to modify
     * the query.
     */
    public static function relationshipBuilder(string $name, callable $callback): Collection
    {
        return collect([
            new AllowedInclude(
                $name, new class($callback) implements IncludeInterface
                {
                    protected $callback;

                    public function __construct(callable $callback)
                    {
                        $this->callback = $callback;
                    }

                    public function __invoke(Builder $query, string $include): Builder
                    {
                        $callback = $this->callback;

                        return $query->with([
                            $include => $callback,
                        ]);
                    }
                }
            ),
        ]);
    }
}

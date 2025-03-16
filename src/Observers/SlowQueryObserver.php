<?php

namespace Beam\Beam\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{DB, Event};
use Beam\Beam\Payloads\QueriesPayload;
use Beam\BeamCore\Actions\Config;
use Beam\BeamCore\Beam;

class SlowQueryObserver
{
    public function register(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            if (!$this->isEnabled()) {
                return;
            }

            /** @var float $minimumTimeInMs */
            $minimumTimeInMs = Config::get('slow_queries.threshold_in_ms', 500);

            if ($query->time >= $minimumTimeInMs) {
                $toSql = DB::getQueryGrammar()
                    ->substituteBindingsIntoRawSql(
                        $query->sql,
                        $query->bindings
                    );

                $queries = [
                    'sql'            => $toSql,
                    'time'           => $query->time,
                    'database'       => $query->connection->getDatabaseName(),
                    'driver'         => $query->connection->getDriverName(),
                    'connectionName' => $query->connectionName,
                    'query'          => $query,
                ];

                $dumps = new Beam();

                $payload = new QueriesPayload($queries, screen: 'slow queries');

                $dumps->send($payload);
            }
        });
    }

    public function isEnabled(): bool
    {
        $enabled = boolval(Config::get('observers.slow_queries', false));

        if ($enabled && app()->bound('db')) {
            collect(DB::getConnections())->each(fn ($connection) => $connection->enableQueryLog());
        }

        return $enabled;
    }
}

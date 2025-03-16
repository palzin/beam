<?php

namespace Beam\Beam\Payloads;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Beam\BeamCore\Payloads\{Label, Payload, Screen};

class QueryPayload extends Payload
{
    public function __construct(
        protected Builder $query
    ) {
    }

    public function content(): array
    {
        $toSql = DB::getQueryGrammar()
            ->substituteBindingsIntoRawSql(
                $this->query->toSql(),
                $this->query->getBindings()
            );

        return [
            'sql' => $toSql,
        ];
    }

    public function type(): string
    {
        return 'query';
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return new Label('Queries');
    }
}

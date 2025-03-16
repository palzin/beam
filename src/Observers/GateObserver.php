<?php

namespace Beam\Beam\Observers;

use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Event};
use Beam\Beam\Beam;
use Beam\BeamCore\Actions\{Config, Dumper};
use Beam\BeamCore\Payloads\TableV2Payload;

class GateObserver
{
    protected ?string $label = 'Gate';

    private bool $enabled = false;

    public function register(): void
    {
        Event::listen(GateEvaluated::class, function (GateEvaluated $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $dump = new Beam();
            $user = $event->user;

            $payload = new TableV2Payload([
                'Ability'   => $event->ability,
                'Result'    => $this->gateResult($event->result),
                'Arguments' => Dumper::dump(collect($event->arguments)->map(function ($argument) {
                    return $argument instanceof Model ? $this->formatModel($argument) : $argument;
                })->toArray())[0],
                'User' => Dumper::dump($user instanceof Authenticatable ? $user->toArray() : null)[0],
            ], screen: 'gate', label: $this->label);

            $dump->toScreen('gate');
            $dump->send($payload);
        });
    }

    public function enable(string $label = null): void
    {
        $this->label = $label;

        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        if (!boolval(Config::get('observers.gate', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.gate', false));
    }

    private function gateResult(null|bool|Response $result): string
    {
        if ($result instanceof Response) {
            return $result->allowed() ? 'allowed' : 'denied';
        }

        return $result ? 'allowed' : 'denied';
    }

    private function formatModel(Model $model): string
    {
        $keys = $model instanceof Pivot && !$model->incrementing
            ? [
                $model->getAttribute($model->getForeignKey()),
                $model->getAttribute($model->getRelatedKey()),
            ]
            : $model->getKey();

        return get_class($model) . ':' . implode('_', array_map(function ($value) {
            if (PHP_VERSION_ID > 80100) {
                return $value instanceof \BackedEnum ? $value->value : $value;
            }

            return $value;
        }, Arr::wrap($keys)));
    }
}

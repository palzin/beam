<?php

namespace Beam\Beam\Observers;

use Illuminate\Cache\Events\{CacheEvent, CacheHit, CacheMissed, KeyForgotten, KeyWritten};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Beam\BeamCore\Actions\Config;
use Beam\BeamCore\Beam;
use Beam\BeamCore\Payloads\TableV2Payload;

class CacheObserver
{
    protected ?string $label = 'Cache';

    protected array $hidden = [];

    private bool $enabled = false;

    public function register(): void
    {
        Event::listen(CacheHit::class, function (CacheHit $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type'  => 'hit',
                'Key'   => $event->key,
                'Value' => $this->formatValue($event),
            ], 'width: 120px', 'Cache Hit');
        });

        Event::listen(CacheMissed::class, function (CacheMissed $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type' => 'missed',
                'Key'  => $event->key,
            ], 'width: 120px', 'Cache Missed');
        });

        Event::listen(KeyForgotten::class, function (KeyForgotten $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type' => 'forget',
                'Key'  => $event->key,
            ], 'width: 120px', 'Cache Forgot');
        });

        Event::listen(KeyWritten::class, function (KeyWritten $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type'       => 'set',
                'Key'        => $event->key,
                'Value'      => $this->formatValue($event),
                'Expiration' => $this->formatExpiration($event),
            ], 'width: 120px', 'Cache Written');
        });
    }

    protected function sendCache(CacheEvent $event, array $data, string $headerStyle = '', string $label = ''): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        if ($this->shouldIgnore($event)) {
            return;
        }

        $dump    = new Beam();
        $payload = new TableV2Payload($data, $headerStyle, 'cache', $this->label ?: $label);

        $dump->send($payload);
    }

    public function enable(string $label = ''): void
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
        if (!boolval(Config::get('observers.cache', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.cache', false));
    }

    public function hidden(array $hidden = []): array
    {
        if (!empty($hidden)) {
            $this->hidden = array_merge($hidden);
        }

        return $this->hidden ?? [];
    }

    private function formatValue(mixed $event): mixed
    {
        return (!$this->shouldHideValue($event))
            ? $event->value // @phpstan-ignore-line
            : '********';
    }

    private function shouldHideValue(mixed $event): bool
    {
        return Str::is(
            $this->hidden(),
            $event->key // @phpstan-ignore-line
        );
    }

    protected function formatExpiration(KeyWritten $event): mixed
    {
        return property_exists($event, 'seconds') // @phpstan-ignore-line
            ? $event->seconds
            : $event->minutes * 60; // @phpstan-ignore-line
    }

    private function shouldIgnore(mixed $event): bool
    {
        return Str::is(
            [
                'illuminate:queue:restart',
                'framework/schedule*',
                'telescope:*',
            ],
            $event->key // @phpstan-ignore-line
        );
    }
}

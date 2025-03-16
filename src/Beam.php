<?php

namespace Beam\Beam;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Beam\Beam\Livewire\Support\Debug;
use Beam\Beam\Observers\{CacheObserver,
    CommandObserver,
    GateObserver,
    HttpClientObserver,
    QueryObserver,
    ScheduledCommandObserver};
use Beam\Beam\Payloads\{MailablePayload, MarkdownPayload, ModelPayload, RoutesPayload};
use Beam\BeamCore\BeamLaravel as BaseBeamLaravel;

class BeamLaravel extends BaseBeamLaravel
{
    protected function beforeWrite(mixed $args): \Closure
    {
        return function () use ($args) {
            if ($args instanceof Model) {
                $payload = new ModelPayload($args);

                return [
                    $payload,
                    uniqid(),
                ];
            }

            if (class_exists(\Livewire\Volt\Component::class)
                && $args instanceof \Livewire\Volt\Component) {
                (new Debug())->debug($args->getId());

                return [[], null];
            }

            return parent::beforeWrite($args)();
        };
    }

    /**
     * Send Routes
     */
    public function routes(mixed ...$except): self
    {
        $this->send(new RoutesPayload($except));

        return $this;
    }

    /**
     * Shows model attributes and relationship
     */
    public function model(Model ...$models): BeamLaravel
    {
        foreach ($models as $model) {
            if ($model instanceof Model) {
                $payload = new ModelPayload($model);
                $this->send($payload);
            }
        }

        return $this;
    }

    /**
     * Display all queries that are executed with custom label
     */
    public function queriesOn(?string $label = null): void
    {
        app(QueryObserver::class)->enable($label);
    }

    /**
     * Stop displaying queries
     */
    public function queriesOff(): void
    {
        app(QueryObserver::class)->disable();
    }

    /**
     * Send rendered mailable
     */
    public function mailable(Mailable $mailable): self
    {
        $payload = new MailablePayload($mailable);

        $this->send($payload);

        return $this;
    }

    /**
     * Display all HTTP Client requests that are executed with custom label
     */
    public function httpOn(string $label = ''): self
    {
        app(HttpClientObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop displaying HTTP Client requests
     */
    public function httpOff(): void
    {
        app(HttpClientObserver::class)->disable();
    }

    /*
     * Sends rendered markdown
     */
    public function markdown(string $markdown): self
    {
        $payload = new MarkdownPayload($markdown);
        $this->send($payload);

        return $this;
    }

    /**
     * Dump all Jobs that are dispatched with custom label
     */
    public function cacheOn(string $label = ''): self
    {
        app(CacheObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop dumping Jobs
     */
    public function cacheOff(): void
    {
        app(CacheObserver::class)->disable();
    }

    /**
     * Dump all Commands with custom label
     */
    public function commandsOn(?string $label = null): self
    {
        app(CommandObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop dumping Commands
     */
    public function commandsOff(): void
    {
        app(CommandObserver::class)->disable();
    }

    /**
     * Dump Scheduled Commands with custom label
     */
    public function scheduledCommandOn(?string $label = null): void
    {
        app(ScheduledCommandObserver::class)->enable($label);
    }

    /**
     * Dump all Gate & Policy checkes with custom label
     */
    public function gateOn(?string $label = null): void
    {
        app(GateObserver::class)->enable($label);
    }

    /**
     * Stop dumping Scheduled Commands
     */
    public function scheduledCommandOff(): void
    {
        app(ScheduledCommandObserver::class)->disable();
    }

    /**
     * Stop dumping Gate
     */
    public function gateOff(): void
    {
        app(GateObserver::class)->disable();
    }
}

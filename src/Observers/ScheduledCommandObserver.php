<?php

namespace Beam\Beam\Observers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\{CallbackEvent, Event, Schedule};
use Beam\Beam\Beam;
use Beam\BeamCore\Actions\Config;
use Beam\BeamCore\Payloads\{Payload, TableV2Payload};

class ScheduledCommandObserver
{
    private bool $enabled = false;

    private string $label = 'Schedule';

    public function register(): void
    {
        $this->enabled = $this->isEnabled();

        \Illuminate\Support\Facades\Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (!$this->isEnabled()) {
                return;
            }

            if (
                $event->command !== 'schedule:run' &&
                $event->command !== 'schedule:finish'
            ) {
                return;
            }

            collect(app(Schedule::class)->events())
                ->each(function ($event) {
                    $event->then(function () use ($event) {
                        $payload = $this->generatePayload($event);
                        $this->sendPayload($payload);
                    });
                });
        });
    }

    public function enable(string $label = null): void
    {
        if ($label) {
            $this->label = $label;
        }

        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        if (!boolval(Config::get('observers.scheduled_command', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.scheduled_command', false));
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new Beam();

        $dumps->send($payload);
    }

    private function generatePayload(Event $event): Payload
    {
        return new TableV2Payload([
            'Command'     => $event instanceof CallbackEvent ? 'Closure' : $event->command,
            'Description' => $event->description,
            'Expression'  => $event->expression,
            'Timezone'    => $event->timezone,
            'User'        => $event->user,
            'Output'      => $this->getEventOutput($event),
        ], screen: 'scheduled commands', label: $this->label);
    }

    protected function getEventOutput(Event $event): string|null
    {
        if (!$event->output ||
            $event->output === $event->getDefaultOutput() ||
            $event->shouldAppendOutput ||
            !file_exists($event->output)) {
            return '';
        }

        return trim(file_get_contents($event->output)); /** @phpstan-ignore-line */
    }
}

<?php

namespace Beam\Beam\Observers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use Beam\BeamCore\Actions\{Config, Dumper};
use Beam\BeamCore\Beam;
use Beam\BeamCore\Payloads\{DumpPayload, Payload};

class CommandObserver
{
    private bool $enabled = false;

    private string $label = 'Command';

    public function register(): void
    {
        Event::listen(CommandFinished::class, function (object $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $payload = $this->generatePayload($event);

            $this->sendPayload($payload);
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
        if (!(bool) boolval(Config::get('observers.commands', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.commands', false));
    }

    private function generatePayload(object $event): Payload
    {
        return new DumpPayload(Dumper::dump([
            /* @phpstan-ignore-next-line */
            'command'   => $event->command ?? $event->input->getArguments()['command'] ?? 'default',
            'exit_code' => $event->exitCode, /** @phpstan-ignore-line */
            'arguments' => $event->input->getArguments(), /** @phpstan-ignore-line */
            'options'   => $event->input->getOptions(), /** @phpstan-ignore-line */
        ]));
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new Beam();

        $dumps->send($payload);
        $dumps->label($this->label);
    }
}

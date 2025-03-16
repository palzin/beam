<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Beam\Beam\Beam;
use Beam\Beam\Payloads\{BladePayload, ModelPayload};
use Beam\BeamCore\Actions\Dumper;
use Spatie\Backtrace\Backtrace;

if (!function_exists('dsBlade')) {
    function dsBlade(mixed $args): void
    {
        $frame = collect(debug_backtrace())
            ->filter(function ($frame) {
                /** @var array $frame */
                return $frame['function'] === 'render' && $frame['class'] === 'Illuminate\View\View'; // @phpstan-ignore-line
            })->first();

        /** @var BladeCompiler $blade
        * @phpstan-ignore-next-line */
        $blade    = $frame['object'];
        $viewPath = $blade->getPath();

        $backtrace = Backtrace::create();
        $backtrace = $backtrace->applicationPath(appBasePath());
        $frame     = app(Beam::class)->parseFrame($backtrace);

        $frame = [
            'file' => $viewPath,
            'line' => data_get($frame, 'lineNumber'),
        ];

        $notificationId = Str::uuid()->toString();
        $beam      = new Beam(notificationId: $notificationId);

        if ($args instanceof Model) {
            $payload = new ModelPayload($args);
            $payload->setDumpId(uniqid());
        } else {
            [$pre, $id] = Dumper::dump($args);

            $payload = new BladePayload($pre);
            $payload->setDumpId($id);
        }

        $payload->setFrame($frame);

        $beam->send($payload, withFrame: false);
    }
}

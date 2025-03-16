<?php

namespace Beam\Beam\Observers;

use Illuminate\Http\Client\Events\{RequestSending, ResponseReceived};
use Illuminate\Http\Client\{Request, Response};
use Illuminate\Support\Facades\Event;
use Beam\BeamCore\Actions\{Config, Dumper};
use Beam\BeamCore\Beam;
use Beam\BeamCore\Payloads\{Payload, TableV2Payload};

class HttpClientObserver
{
    private bool $enabled = false;

    private string $label = '';

    public function register(): void
    {
        Event::listen(RequestSending::class, function (RequestSending $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $payload = $this->handleRequest($event->request);

            $this->sendPayload(
                $payload,
                'Http Sending'
            );
        });

        Event::listen(ResponseReceived::class, function (ResponseReceived $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $payload = $this->handleResponse($event->request, $event->response);

            $this->sendPayload(
                $payload,
                'Http Received'
            );
        });
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
        if (!boolval(Config::get('observers.http', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.http', false));
    }

    protected function getRequestType(Request $request): string
    {
        if ($request->isJson()) {
            return 'Json';
        }

        if ($request->isMultipart()) {
            return 'Multipart';
        }

        return 'Form';
    }

    protected function handleRequest(Request $request): Payload
    {
        return new TableV2Payload([
            'Method'  => $request->method(),
            'URL'     => $request->url(),
            'Headers' => $request->headers(),
            'Data'    => $request->data(),
            'Body'    => $request->body(),
            'Type'    => $this->getRequestType($request),
        ], screen: 'http', label: $this->label ?? 'http request');
    }

    protected function handleResponse(Request $request, Response $response): Payload
    {
        return new TableV2Payload([
            'URL'          => $request->url(),
            'Real Request' => !empty($response->handlerStats()),
            'Success'      => $response->successful(),
            'Status'       => $response->status(),
            'Headers'      => Dumper::dump($response->headers())[0],
            'Body'         => rescue(function () use ($response) {
                return $response->json();
            }, Dumper::dump($response->body())[0], false),
            'Cookies'         => Dumper::dump($response->cookies())[0],
            'Size'            => $response->handlerStats()['size_download'] ?? null,
            'Connection time' => $response->handlerStats()['connect_time'] ?? null,
            'Duration'        => $response->handlerStats()['total_time'] ?? null,
            'Request Size'    => $response->handlerStats()['request_size'] ?? null,
        ], screen: 'http', label: $this->label ?? 'http response');
    }

    private function sendPayload(Payload $payload, string $label): void
    {
        $dumps = new Beam();

        $dumps->send($payload);
    }
}

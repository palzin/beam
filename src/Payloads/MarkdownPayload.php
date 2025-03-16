<?php

namespace Beam\Beam\Payloads;

use Illuminate\Mail\Markdown;
use Beam\BeamCore\Payloads\{Label, Payload, Screen};

class MarkdownPayload extends Payload
{
    public function __construct(
        public string $dump
    ) {
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump' => Markdown::parse($this->dump)->toHtml(),
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}

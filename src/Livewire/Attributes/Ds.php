<?php

namespace Beam\Beam\Livewire\Attributes;

use Beam\Beam\Livewire\Support\Debug;
use Livewire\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Ds extends Attribute
{
    public function __construct(
        public bool $queries = true,
    ) {
    }

    public function boot(): void
    {
        app(Debug::class)->debug(
            $this->component->getId(),
            $this->queries
        );
    }
}

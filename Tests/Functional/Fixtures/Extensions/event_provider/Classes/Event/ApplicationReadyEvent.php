<?php

declare(strict_types=1);

namespace Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\EventProvider\Event;

final class ApplicationReadyEvent
{
    public function __construct(
        private readonly string $context,
    ) {}

    public function getContext(): string
    {
        return $this->context;
    }
}

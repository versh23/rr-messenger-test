<?php

namespace App\Message;

final readonly class TestMessage
{
    public function __construct(
        public string $message,
    ) {
    }
}

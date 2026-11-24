<?php

namespace App\Enums;

enum MessageDirection: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';

    public function isInbound(): bool
    {
        return $this === self::INBOUND;
    }

    public function isOutbound(): bool
    {
        return $this === self::OUTBOUND;
    }
}


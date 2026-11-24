<?php

namespace App\Enums;

enum ConversationType: string
{
    case COMMENT_THREAD = 'comment_thread';
    case DIRECT_MESSAGE = 'direct_message';
    case MENTION = 'mention';

    public function label(): string
    {
        return match ($this) {
            self::COMMENT_THREAD => 'Post Comment',
            self::DIRECT_MESSAGE => 'Direct Message',
            self::MENTION => 'Social Mention',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COMMENT_THREAD => 'bi-chat-left-text',
            self::DIRECT_MESSAGE => 'bi-envelope',
            self::MENTION => 'bi-at',
        };
    }
}


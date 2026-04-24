<?php

namespace App\MessageHandler;

use App\Message\UserMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserMessageHandler {
    public function __invoke(UserMessage $message) {
    }
}

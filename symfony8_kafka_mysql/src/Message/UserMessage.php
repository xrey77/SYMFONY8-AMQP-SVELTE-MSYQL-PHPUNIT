<?php

// src/Message/UserMessage.php
namespace App\Message;

class UserMessage
{
    public function __construct(
        private int $userId,
        private string $action
    ) {}

    public function getUserId(): int { return $this->userId; }
    public function getAction(): string { return $this->action; }
}

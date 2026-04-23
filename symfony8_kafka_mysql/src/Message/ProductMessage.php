<?php

// src/Message/ProductMessage.php
namespace App\Message;

readonly class ProductMessage
{
    /**
     * @param int[] $productIds
     */
    public function __construct(
        public array $productIds,
        public string $action
    ) {}
}

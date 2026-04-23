<?php

// src/MessageHandler/ProductHandler.php
namespace App\MessageHandler;

use App\Message\ProductMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProductHandler
{
    public function __invoke(ProductMessage $message): void
    {
        // Access your message data
        $ids = $message->productIds;
        $action = $message->action;

        // Add your logic here (e.g., updating a database or clearing cache)
        foreach ($ids as $id) {
            // Logic for $action on product $id
        }
    }
}

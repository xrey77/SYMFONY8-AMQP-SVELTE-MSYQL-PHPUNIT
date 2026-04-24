<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Message\ProductMessage;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ProductsearchTest extends ApiTestCase
{
    use InteractsWithMessenger; 

    public function testProductsearchSuccess(): void
    {
        // 2. You must create the client first
        $client = static::createClient();

        // 3. Ensure '1' is the page number (matches {page} in your Route)
        $key = "cineo";
        $client->request('GET', "/api/productsearch/{$key}");

        // 4. Assert 200 OK
        $this->assertResponseStatusCodeSame(200); 
        
        // 5. Verify the Messenger queue 
        // Note: Use the default transport or the one defined in your messenger.yaml
        // Your controller dispatches ProductMessage, so we check for that here.
        $this->messenger('async_products')->queue()->assertContains(ProductMessage::class);   
        $this->messenger('async_products')->queue()->assertCount(1);
    }
}

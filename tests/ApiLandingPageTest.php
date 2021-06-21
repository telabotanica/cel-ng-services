<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiLandingPageTest extends WebTestCase
{
    /**
     * Test api landing page.
     */
    public function testAnonymousApiPageIsValid()
    {
        $client = static::createClient();

        $pages = [
            '/api',
        ];
        foreach ($pages as $page) {
            $crawler = $client->request('GET', $page);

            $this->assertEquals(
                200,
                $client->getResponse()->getStatusCode(),
                sprintf('Assert page %s is StatusCode 200', $page)
            );
        }

    }
}

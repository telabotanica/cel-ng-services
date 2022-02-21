<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OccurrenceTest extends WebTestCase
{
    public function testAnonymousOccurrencePageIsValid()
    {
        $client = static::createClient();

        $client->request('GET', '/api/occurrences');

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            sprintf('Assert anonymous page %s is StatusCode 403', '/api/occurrences')
        );
    }

    public function testBadTokenOccurrencePageIsValid()
    {
        $client = static::createClient();

        $authorizationToken = 'Bearer bad.token.lol';

        $client->request('GET', '/api/occurrences', [
            'headers' => [
                'Authorization' => $authorizationToken,
            ],
        ]);

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            sprintf('Assert logged in page %s is StatusCode 403', '/api/occurrences')
        );
    }
}

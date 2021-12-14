<?php

namespace App\Tests;

use App\Service\PlantnetService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlantnetOccurrencesDeserializerTest extends KernelTestCase
{
    public function testPlantnetServiceDeserializer()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = self::$container;

        // (3) run some service & test the result
        $plantnetService = $container->get(PlantnetService::class);

        $occurrencesContent = $plantnetService->deserializeOccurrences(self::getContent());

        // if we can get here then all deserialize thing is working
        $this->assertEquals(
            'https://my-api.plantnet.org/v2/observations/sync/latest?startDate=1629210929735',
            $occurrencesContent->getNextStartDate()
        );

        // qualityVotes
        $this->assertEquals(1, $occurrencesContent->getData()[0]->getImages()[0]->getQualityVotes()->getPlus());

        // organVotes
        $this->assertEquals(1, $occurrencesContent->getData()[0]->getImages()[0]->getOrgansVotes()->getLeaf()->getPlus());

        // identificationResults
        $this->assertEquals(
            'Fallopia japonica (Houtt.) Ronse Decr.',
            $occurrencesContent->getData()[0]->getIdentificationResults()[0]->getSpecies()
        );

        $this->assertEquals((float) '42.649743', $occurrencesContent->getData()[0]->getGeo()->getLat());

        $this->assertEquals('salut sava', $occurrencesContent->getData()[0]->getVotes()[0]->getName());
    }

    private static function getContent(): string
    {
        return file_get_contents(__DIR__ .'/PlantnetOccurrencesDeserializerTestContent.txt');
    }

    public function testPlantnetServiceDeserializerBadContent()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = self::$container;

        // (3) run some service & test the result
        $plantnetService = $container->get(PlantnetService::class);

        $occurrencesContent = $plantnetService->deserializeOccurrences(self::getBadContent());

        // if we can get here then all deserialize thing is working
        $this->assertEquals(
            false,
            $occurrencesContent->hasMore(),
            'Hi! I am this test message, and here I am to tell you that'
        );
    }

    private static function getBadContent(): string
    {
        return file_get_contents(__DIR__ .'/PlantnetOccurrencesDeserializerTestBadContent.txt');
    }
}

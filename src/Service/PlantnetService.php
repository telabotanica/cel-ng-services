<?php

namespace App\Service;

use App\Model\PlantnetImage;
use App\Model\PlantnetOccurrence;
use App\Model\PlantnetOccurrences;
use App\Serializer\PlantnetMicroDateTimeNormalizer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PlantnetService
{
    private $client;
    private $plantnetApiKey;
    private $plantnetBaseApiUrl;

    public function __construct(string $plantnetApiKey, string $plantnetBaseApiUrl)
    {
        $this->client = HttpClient::create();
        $this->plantnetApiKey = $plantnetApiKey;
        $this->plantnetBaseApiUrl = $plantnetBaseApiUrl;
    }

    public function deserializeOccurrences(string $plantnetOccurrences): PlantnetOccurrences
    {
        $encoder = [new JsonEncoder()];

        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $normalizer = [
            new ArrayDenormalizer(),
            new PlantnetMicroDateTimeNormalizer(),
            new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter(), null, $extractor),
        ];
        $serializer = new Serializer($normalizer, $encoder);

        return $serializer->deserialize($plantnetOccurrences, PlantnetOccurrences::class, 'json', [
            ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true
        ]);
    }

    /**
     * Get last occurrences starting at $startTime and filtered by $email
     */
    public function getOccurrences(int $startDate = 0, string $email = '', int $endDate): PlantnetOccurrences
    {
        $params = [
            'startDate' => $startDate,
            'api-key' => $this->plantnetApiKey,
        ];
		
		if ($endDate){
			$params['endDate'] = $endDate;
		}

        if ('' !== $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $params['email'] = $email;
        }
		
        $params = http_build_query($params);

        $response = $this->client->request('GET', $this->plantnetBaseApiUrl.'/observations/sync/latest?'.$params, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $this->handleOccurrencesResponse($response);

        return $this->deserializeOccurrences($response->getContent());
    }

    public function getOccurrencesByNextUrl(string $nextUrl): PlantnetOccurrences
    {
        $apiKey = '&api-key='.$this->plantnetApiKey;

        $response = $this->client->request('GET', $nextUrl.$apiKey, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $this->handleOccurrencesResponse($response);

        return $this->deserializeOccurrences($response->getContent());
    }

    /**
     * Some crash happens because of ONE bad data, so we try to move offset after
     */
    public function handleOccurrencesResponse(ResponseInterface $response)
    {
        if (200 === $response->getStatusCode()) {
            return $response;
        }

        $infos = $response->getInfo();
        $offset = 0;
        $url = $infos['url'].'&offset=';

        do {
            $url .= ++$offset;
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } while (200 !== $response->getStatusCode() && $offset < 100);

        return (200 === $response->getStatusCode()) ? $response : false;
    }

    public function getOccurrenceById(int $plantnetOccurrenceId): PlantnetOccurrence
    {
        $params = http_build_query([
            'api-key' => $this->plantnetApiKey,
        ]);

        // get occurrence
        $url = $this->plantnetBaseApiUrl.'/observations/'.$plantnetOccurrenceId.'?'.$params;
        $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Unable to get occurrence from: "'.$url.'" Error #'.$response->getStatusCode());
        }

        $encoder = [new JsonEncoder()];

        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $normalizer = [
            new ArrayDenormalizer(),
            new PlantnetMicroDateTimeNormalizer(),
            new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter(), null, $extractor),
        ];
        $serializer = new Serializer($normalizer, $encoder);

        return $serializer->deserialize($response->getContent(), PlantnetOccurrence::class, 'json', [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true
            ]);
    }

    public function getExistingPairs(): array
    {
        $params = http_build_query([
            'api-key' => $this->plantnetApiKey,
        ]);

        // get occurrence
        $url = $this->plantnetBaseApiUrl.'/observations/sync/partnerids?'.$params;
        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Accept' => 'text/plain',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Unable to get pairs from: "'.$url.'" Error #'.$response->getStatusCode());
        }

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getImageFile(PlantnetImage $image): File
    {
        $response = $this->client->request('GET', $image->getOriginalImageUrl());

        if (200 !== $response->getStatusCode()) {
            throw new \Exception(
                'Unable to download image from: "'.$image->getOriginalImageUrl().'" Error #'.$response->getStatusCode());
        }

        $filePath = '/tmp/'.$image->getId().uniqid();
        $fileHandler = fopen($filePath, 'wb');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        return new File($filePath, true);
    }
}

<?php

namespace App\Service;

use App\Model\AnnuaireUser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AnnuaireService
{
    public const PLANTNET_BOT_USER_ID = 6;

    private $cachedUsers = [];
    private $client;
    private $annuaireBaseUrl;

    public function __construct(string $annuaireBaseUrl)
    {
        $this->client = HttpClient::create();
        $this->annuaireBaseUrl = $annuaireBaseUrl;
    }

    /**
     * @return false|AnnuaireUser
     */
    public function findUserInfo(string $email)
    {
        if (array_key_exists($email, $this->cachedUsers)) {
            return $this->cachedUsers[$email];
        }

        $response = $this->client->request(
            'GET', $this->annuaireBaseUrl.':utilisateur/identite-par-courriel/'.$email
        );

        // Sometimes annuaire doesn't respond, this trycatch aims to avoid breaking associated command.
        // It's not perfect because false should not be used both for network errors and unknown users.
        try {
            if (200 !== $response->getStatusCode()) {
                if (500 === $response->getStatusCode()) {
                    // annuaire returns a 500 when email is not found
                    $this->cachedUsers[$email] = false;

                    return false;
                }

                throw new \Exception(sprintf(
                    'Annuaire is not happy, getting some %d error for: "%s"',
                    $response->getStatusCode(),
                    $response->getInfo('url')
                ));
            }
        } catch (\Exception $e) {
            return false;
        }

        $userData = $response->getContent();
        if ('[]' === $userData) {
            return false;
        }
        $userData = $this->fixDumbAnnuaireDataStructure($userData);

        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $normalizer = [
            new ArrayDenormalizer(),
            new ObjectNormalizer(null, null, null, $extractor),
        ];
        $serializer = new Serializer($normalizer, [new JsonEncoder()]);

        $user = $serializer->deserialize($userData, AnnuaireUser::class, 'json');
        if ($user) {
            $this->cachedUsers[$email] = $user;
        }

        return $user;
    }

    public function isKnownUser(string $email): bool
    {
        return (bool)$this->findUserInfo($email);
    }

    /**
     * Switch data structure from email indexed form (stupid isn't it?)
     * "{"killian@tela-botanica.org":{"id":"1312",...,"intitule":"killian-stefanini",...}}"
     * to standard form: email inside data structure
     * "{"id":"1312","email":"killian@tela-botanica.org","intitule":"killian-stefanini"}"
     */
    private function fixDumbAnnuaireDataStructure(string $data): string
    {
        $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        $email = array_key_first($data);
        $id = $data[$email]['id'];
        $intitule = $data[$email]['intitule'] ?? $data[$email]['pseudo'] ?? $data[$email]['prenom'] ?? 'anonymous';

        return json_encode([
            'id' => $id,
            'email' => $email,
            'intitule' => $intitule,
        ], JSON_THROW_ON_ERROR);
    }
}

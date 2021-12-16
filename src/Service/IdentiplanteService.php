<?php

namespace App\Service;

use App\Entity\Occurrence;
use Symfony\Component\HttpClient\HttpClient;

class IdentiplanteService
{
    private $client;
    private $identiplanteServiceUrl;

    public function __construct(string $identiplanteServiceUrl)
    {
        $this->client = HttpClient::create();
        $this->identiplanteServiceUrl = $identiplanteServiceUrl;
    }

    public function getComments(int $occurrenceId)
    {
        $response = $this->client->request(
            'GET', $this->identiplanteServiceUrl.'/observations/' .$occurrenceId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $comments = $response->getContent();
        $comments = json_decode($comments, true, 512, JSON_THROW_ON_ERROR);

        return $comments['commentaires'];
    }

    public function addComment(Occurrence $occurrence): void
    {
        // nom_sel=Acer+monspessulanum+LoL.&auteur.prenom=Killian&auteur.nom=Stefanini&auteur.courriel=killian%40tela-botanica.org
        // &nom_sel_nn=182&nom_referentiel=bdtfx&observation=3632862&auteur.id=6&texte=commentaireDeTest
        $response = $this->client->request('PUT', $this->identiplanteServiceUrl.'/commentaires/', [
            'headers' => [
                'Content-Type' => 'text/plain;charset=UTF-8'
            ],
            'body' => http_build_query([
                'observation' => $occurrence->getId(),
                'nom_sel' => $occurrence->getUserSciName(),
                'nom_sel_nn' => $occurrence->getUserSciNameId(),
                'nom_referentiel' => $occurrence->getTaxoRepo(),
                'auteur.id' => AnnuaireService::PLANTNET_BOT_USER_ID,
                'auteur.prenom' => 'Pl@ntNetBot',
                'auteur.nom' => 'Pl@ntNetBot',
                'auteur.courriel' => 'plantnet_bot_account@tela-botanica.org',
                'texte' => 'Commentaire généré automatiquement par l\'import Pl@ntNet',
            ])
        ]);
    }
}

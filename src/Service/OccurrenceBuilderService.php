<?php

namespace App\Service;

use App\DBAL\CertaintyEnumType;
use App\DBAL\InputSourceEnumType;
use App\DBAL\LocationAccuracyEnumType;
use App\Entity\Occurrence;
use App\Model\AnnuaireUser;
use App\Model\PlantnetOccurrence;

class OccurrenceBuilderService
{
    private $taxoRepoService;

    public function __construct(TaxoRepoService $taxoRepoService)
    {
        $this->taxoRepoService = $taxoRepoService;
    }

    public function createOccurrence(AnnuaireUser $user, PlantnetOccurrence $pnOccurrence): Occurrence
    {
        $occurrence = new Occurrence();
        $occurrence
            ->setUserId($user->getId())
            ->setUserEmail($user->getEmail())
            ->setUserPseudo($user->getIntitule())
            ->setObserver($user->getIntitule());

        $occurrence->setPlantnetId($pnOccurrence->getId());
        $occurrence->setCertainty(CertaintyEnumType::DOUBTFUL);
        $occurrence->setInputSource(InputSourceEnumType::PLANTNET);
        $occurrence->setIsPublic(true);

        return $occurrence;
    }

    public function updateWithPlantnetOccurrence(Occurrence $occurrence, PlantnetOccurrence $pnOccurrence): Occurrence
    {
        if (!$pnOccurrence->getCurrentName()) {
            return $occurrence;
        }

        // search taxon data
        $taxonInfo = $this->taxoRepoService->getTaxonInfo($pnOccurrence->getCurrentName(), $pnOccurrence->getProject());

        $occurrence->setDateObserved($pnOccurrence->getDateObs())
            ->setDateCreated($pnOccurrence->getDateCreated())
            ->setDateUpdated($pnOccurrence->getDateUpdated())
            ->setDatePublished($pnOccurrence->getDateCreated());

        $occurrence->setTaxoRepo($taxonInfo['taxoRepo'])
            ->setUserSciName($taxonInfo['sciName'] ?? $pnOccurrence->getCurrentName())
            ->setUserSciNameId($taxonInfo['sciNameId'])
            ->setAcceptedSciName($taxonInfo['acceptedSciName'])
            ->setAcceptedSciNameId($taxonInfo['acceptedSciNameId'])
            ->setFamily($taxonInfo['family'] ??
                ($pnOccurrence->getSpecies() ? $pnOccurrence->getSpecies()->getFamily() : ''));

        if ($pnOccurrence->getGeo()->getLon() && $pnOccurrence->getGeo()->getLat()) {
            $occurrence->setGeometry(json_encode([
                'type' => 'Point',
                'coordinates' => [
                    $pnOccurrence->getGeo()->getLon(),
                    $pnOccurrence->getGeo()->getLat()
                ]
            ], JSON_THROW_ON_ERROR));

            $occurrence->setLocality($pnOccurrence->getGeo()->getPlace());
            if ($pnOccurrence->getGeo()->getAccuracy()) {
                $occurrence->setLocationAccuracy(LocationAccuracyEnumType::getAccuracyRangeForFloat($pnOccurrence->getGeo()->getAccuracy()));
            }
        }

        return $occurrence;
    }

}
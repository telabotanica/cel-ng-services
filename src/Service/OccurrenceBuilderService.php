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
		//TODO: Gestion des obs  privées
//        $occurrence->setIsPublic(true);

        return $occurrence;
    }

    public function updateWithPlantnetOccurrence(Occurrence $occurrence, PlantnetOccurrence $pnOccurrence): Occurrence
    {
        $firstIdentificationResult = $pnOccurrence->getIdentificationResults()[0] ?? false;
        if (!$pnOccurrence->getCurrentName() && !$firstIdentificationResult) {
            return $occurrence;
        }
//		print_r($pnOccurrence->getSpecies()->getName());
//        $taxonName = $pnOccurrence->getCurrentName() ?? $firstIdentificationResult->getSpecies();
		//TODO: pbl ici
		$species = $pnOccurrence->getSpecies();
		if (isset($species) && !empty($pnOccurrence->getSpecies()->getName())){
			$taxonName = $pnOccurrence->getSpecies()->getName();
		} elseif ($pnOccurrence->getCurrentName()){
			$taxonName = $pnOccurrence->getCurrentName();
		} else {
			$taxonName = $firstIdentificationResult->getSpecies();
		}
//        $taxonName = $pnOccurrence->getSpecies()->getName() ?? $firstIdentificationResult->getSpecies();

        // search taxon data
        $taxonInfo = $this->taxoRepoService->getTaxonInfo($taxonName, $pnOccurrence->getProject());

		//TODO: changer le date published ?
		//TODO ajouter dateUpdatedRemote -> changer entity Occurrence
        $occurrence->setDateObserved($pnOccurrence->getDateObs())
//            ->setDateCreated($pnOccurrence->getDateCreated())
            ->setDateUpdated($pnOccurrence->getDateUpdated())
            ->setDatePublished($pnOccurrence->getDateCreated());

        $occurrence->setTaxoRepo($taxonInfo['taxoRepo'])
            ->setUserSciName($taxonInfo['sciName'] ?? $taxonName)
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

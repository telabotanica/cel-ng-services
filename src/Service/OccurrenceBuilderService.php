<?php

namespace App\Service;

use App\DBAL\CertaintyEnumType;
use App\DBAL\InputSourceEnumType;
use App\DBAL\LocationAccuracyEnumType;
use App\Entity\Occurrence;
use App\Model\AnnuaireUser;
use App\Model\PlantnetOccurrence;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;

class OccurrenceBuilderService
{
    private $taxoRepoService;
	private $client;

    public function __construct(TaxoRepoService $taxoRepoService)
    {
        $this->taxoRepoService = $taxoRepoService;
		$this->client = HttpClient::create();
		
    }

    public function createOccurrence(AnnuaireUser $user, PlantnetOccurrence $pnOccurrence): Occurrence
    {
        $occurrence = new Occurrence();
        $occurrence
            ->setUserId($user->getId())
            ->setUserEmail($user->getEmail())
            ->setUserPseudo($user->getIntitule())
            ->setObserver($user->getIntitule());
		$occurrence->setIsPublic(true);

        $occurrence->setPlantnetId($pnOccurrence->getId());
        $occurrence->setCertainty(CertaintyEnumType::DOUBTFUL);
        $occurrence->setInputSource(InputSourceEnumType::PLANTNET);
        return $occurrence;
    }

    public function updateWithPlantnetOccurrence(Occurrence $occurrence, PlantnetOccurrence $pnOccurrence): Occurrence
    {
        $firstIdentificationResult = $pnOccurrence->getIdentificationResults()[0] ?? false;
        if (!$pnOccurrence->getCurrentName() && !$firstIdentificationResult) {
            return $occurrence;
        }
		$species = $pnOccurrence->getSpecies();
		
		if (isset($species) && !empty($pnOccurrence->getSpecies()->getName())){
			$taxonName = $pnOccurrence->getSpecies()->getName();
		} elseif ($pnOccurrence->getCurrentName()){
			$taxonName = $pnOccurrence->getCurrentName();
		} else {
			$taxonName = $firstIdentificationResult->getSpecies();
		}
		
        // search taxon data

		if ($species && $species->getPowoId()){
			$taxonIpniId = $this->taxoRepoService->getTaxonInfoFromPowo($species->getPowoId());
			if ($taxonIpniId){
				try {
					$taxonInfo = $this->taxoRepoService->getTaxonInfo($taxonIpniId, $pnOccurrence->getProject());
				} catch (\Exception $e) {
					$taxonInfo = $this->taxoRepoService->getTaxonInfo($taxonName, $pnOccurrence->getProject());
				}
			} else {
				// Si pas de correspondance on recherche avec le nom et le référentiel
				$taxonInfo = $this->taxoRepoService->getTaxonInfo($taxonName, $pnOccurrence->getProject());
			}
		} else {
			// Si pas de powoId on recherche avec le nom et le référentiel
			$taxonInfo = $this->taxoRepoService->getTaxonInfo($taxonName, $pnOccurrence->getProject());
		}
			
        $occurrence->setDateObserved($pnOccurrence->getDateObs())
            ->setDateCreated($pnOccurrence->getDateCreated())
            ->setDateUpdated($pnOccurrence->getDateUpdated());

        $occurrence->setTaxoRepo($taxonInfo['taxoRepo'])
            ->setUserSciName($taxonInfo['sciName'] ?? $taxonName)
            ->setUserSciNameId($taxonInfo['sciNameId'])
            ->setAcceptedSciName($taxonInfo['acceptedSciName'])
            ->setAcceptedSciNameId($taxonInfo['acceptedSciNameId'])
            ->setFamily($taxonInfo['family'] ??
                ($pnOccurrence->getSpecies() ? $pnOccurrence->getSpecies()->getFamily() : ''));

        if ($pnOccurrence->getGeo()->getLon() && $pnOccurrence->getGeo()->getLat()) {
			$occurrence->setIsPublic(true);
			
			$altitude = $this->getAltitude($pnOccurrence->getGeo()->getLon(), $pnOccurrence->getGeo()->getLat());
			if ($altitude){
				$occurrence->setElevation($altitude);
			}
			
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
        } else {
			$occurrence->setIsPublic(false);
		}

		if ($occurrence->getIsPublic()){
			$occurrence->setDatePublished(new \DateTime("now"));
		}

        return $occurrence;
    }
	
	public function getAltitude($longitude, $latitude){
		$altitude = null;
		$opentopodataApi = 'https://api.opentopodata.org/v1/mapzen?';
		$response = $this->client->request('GET', $opentopodataApi.'locations='.$latitude.','.$longitude);
		
		if (200 !== $response->getStatusCode()) {
			print_r('Erreur lors de la récupération de l\'altitude.');
			
		} else {
			$response = json_decode($response->getContent(), true) ?? [];
			$altitude = $response['results'][0]['elevation'];
		}
		return $altitude;
	}

}

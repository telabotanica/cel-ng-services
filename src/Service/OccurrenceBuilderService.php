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
				
		$taxonName = $this->getPnTaxon($pnOccurrence)[0];
		$taxonInfo = $this->getPnTaxon($pnOccurrence)[1];
		
		$pnOccurrence->getDateObs() ? $occurrence->setDateObserved($pnOccurrence->getDateObs()) : $occurrence->setDateObserved($pnOccurrence->getDateCreated());
		
        $occurrence->setDateCreated($pnOccurrence->getDateCreated())
            ->setDateUpdated($pnOccurrence->getDateUpdated());

        $occurrence->setTaxoRepo($taxonInfo['taxoRepo'])
            ->setUserSciName($taxonName)
            ->setUserSciNameId($taxonInfo['sciNameId'])
            ->setAcceptedSciName($taxonInfo['acceptedSciName'])
            ->setAcceptedSciNameId($taxonInfo['acceptedSciNameId'])
            ->setFamily($taxonInfo['family'] ??
                ($pnOccurrence->getSpecies() ? $pnOccurrence->getSpecies()->getFamily() : ''));

		// Ajout des données de localisation
        if ($pnOccurrence->getGeo()->getLon() && $pnOccurrence->getGeo()->getLat()) {
			// Parfois il n'y a pas d'acceptedSciName ou de userSciNammeId, donc dans ce cas on ne veut pas que l'obs soit public
			if ($occurrence->getAcceptedSciNameId()){
				$occurrence->setIsPublic(true);
			} else {
				$occurrence->setIsPublic(false);
			}
			
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
			
			if ($pnOccurrence->getGeo()->getPlace()){
				$occurrence->setLocality($pnOccurrence->getGeo()->getPlace());
				if ($pnOccurrence->getGeo()->getAccuracy()) {
					$occurrence->setLocationAccuracy(LocationAccuracyEnumType::getAccuracyRangeForFloat($pnOccurrence->getGeo()->getAccuracy()));
				}
			} else {
				$locality = $this->getLocality($pnOccurrence->getGeo()->getLon(), $pnOccurrence->getGeo()->getLat());
				$occurrence->setLocality($locality[0]);
				$occurrence->setSublocality($locality[1]);
//				$occurrence->setLocalityInseeCode($locality[2]);
				if ($pnOccurrence->getGeo()->getAccuracy()) {
					$occurrence->setLocationAccuracy(LocationAccuracyEnumType::getAccuracyRangeForFloat($pnOccurrence->getGeo()->getAccuracy()));
				}
			}
        } else {
			$occurrence->setIsPublic(false);
		}
		
		$occurrence->setDatePublished(new \DateTime("now"));

        return $occurrence;
    }
	
	public function getLocality($longitude, $latitude){
		$infos = null;
		$bigDataApi = 'https://api.bigdatacloud.net/data/reverse-geocode-client?';
		$response = $this->client->request('GET', $bigDataApi.'latitude='.$latitude.'&longitude='.$longitude.'&localityLanguage=fr');
		
		if (200 !== $response->getStatusCode()) {
			print_r('Erreur lors de la récupération de la localité.');
		} else {
			$response = json_decode($response->getContent(), true) ?? [];
			$infos = [
				$response['city'],
				$response['locality'],
				$response['postcode']
			];
		}
		return $infos;
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
	
	public function getPnTaxon(PlantnetOccurrence $pnOccurrence){
		$firstIdentificationResult = $pnOccurrence->getIdentificationResults()[0] ?? false;
		
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
		
		return [$taxonName, $taxonInfo];
	}

	public function isGeoChanged(Occurrence $occurrence, PlantnetOccurrence $pnOccurrence){
		$geo = json_encode([
			'type' => 'Point',
			'coordinates' => [
				$pnOccurrence->getGeo()->getLon(),
				$pnOccurrence->getGeo()->getLat()
			]]);
		if ($pnOccurrence->getGeo()->getPlace() != $occurrence->getLocality() || $geo != $occurrence->getGeometry()){
			return true;
		}
		
		return false;
	}
}

<?php

namespace App\Elastica\Transformer;  

use App\Entity\Occurrence;

use DateTime;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
 
/**
 * Transforms <code>Occurrence</code> entity instances into elastica 
 * <code>Document</code> instances.
 *
 * @package App\Elastica\Transformer
 */
class OccurrenceToElasticaTransformer implements ModelToElasticaTransformerInterface {
 
    /**
     * @inheritdoc
     */
	public function transform($occ, array $fields) {
		return new Document($occ->getId(), $this->buildData($occ));
	}
 
    // @refactor DRY this using meta prog black magic + an abstract class
	protected function buildData($occ) {
		$data = [];
        $tags = [];

        foreach($occ->getUserOccurrenceTags() as $tag){
            $nestedTag = array(
                'name' => $tag->getUserOccurrenceTag()->getName(),
                'path' => $tag->getUserOccurrenceTag()->getPath(),
                'id' => $tag->getUserOccurrenceTag()->getId(),
            );
            $tags[] = $nestedTag;
        }
        $data['userOccurrenceTags'] = $tags;

		$data['id'] = $occ->getId();
		$data['id_keyword'] = $occ->getId();
        $data['geom'] = json_decode($occ->getGeometry());
		$data['userId'] = $occ->getUserId();
		$data['userEmail'] = $occ->getUserEmail();
		$data['userPseudo'] = $occ->getUserPseudo();
		$data['observer'] = $occ->getObserver();
		$data['observerInstitution'] = $occ->getObserverInstitution();
        $dateObserved = $occ->getFormattedDateObserved();
        $data['dateObserved'] = $dateObserved;
        $data['dateObserved_keyword'] = $dateObserved;
        $data['dateObservedMonth'] = $occ->getDateObservedMonth();
        $data['dateObservedDay'] = $occ->getDateObservedDay();
        $data['dateObservedYear'] = $occ->getDateObservedYear();
        $data['dateCreated'] = $occ->getFormattedDateCreated();
        $data['dateCreated_keyword'] = $occ->getFormattedDateCreated();
        $data['dateUpdated'] = $occ->getFormattedDateUpdated();
        $data['datePublished'] = $occ->getFormattedDatePublished();
        $data['userSciName'] = $occ->getUserSciName();
        $data['userSciName_keyword'] = $occ->getUserSciName();
        $data['userSciNameId'] = $occ->getUserSciNameId();
        $data['acceptedSciName'] = $occ->getAcceptedSciName();
        $data['acceptedSciNameId'] = $occ->getAcceptedSciNameId();
        $data['plantnetId'] = $occ->getPlantnetId();
        $data['family'] = $occ->getFamily();
        $data['family_keyword'] = $occ->getFamily();
        $data['certainty'] = $occ->getCertainty();
        $data['certainty_keyword'] = $occ->getCertainty();
        $data['occurrenceType'] = $occ->getOccurrenceType();
        $data['isWild'] = $occ->getIsWild();
        $data['coef'] = $occ->getCoef();
        $data['phenology'] = $occ->getPhenology();
        $data['sampleHerbarium'] = $occ->getSampleHerbarium();
        $data['bibliographySource'] = $occ->getBibliographySource();
        $data['inputSource'] = $occ->getInputSource();
        $data['isPublic'] = $occ->getIsPublic();
		$data['isPublic_keyword'] = $occ->getIsPublic();
        $data['isVisibleInCel'] = $occ->getIsVisibleInCel();
        $data['isVisibleInVegLab'] = $occ->getIsVisibleInVegLab();
        $data['signature'] = $occ->getSignature();
        $data['geometry'] = $occ->getGeometry();
        $data['elevation'] = $occ->getElevation();
        $data['elevation_keyword'] = $occ->getElevation();
        $data['geodatum'] = $occ->getGeodatum();
        $data['locality'] = $occ->getLocality();
        $data['localityInseeCode'] = $occ->getLocalityInseeCode();
        $data['locality_keyword'] = $occ->getLocality();
        $data['sublocality'] = $occ->getSublocality();
        $data['environment'] = $occ->getEnvironment();
        $data['localityConsistency'] = $occ->getLocalityConsistency();
        $data['station'] = $occ->getStation();
        $data['publishedLocation'] = $occ->getPublishedLocation();
        $data['locationAccuracy'] = $occ->getLocationAccuracy();
        $data['osmCounty'] = $occ->getOsmCounty();
        $data['osmState'] = $occ->getOsmState();
        $data['osmPostcode'] = $occ->getOsmPostcode();
        $data['osmCountry'] = $occ->getOsmCountry();
        $data['osmCountryCode'] = $occ->getOsmCountryCode();
        $data['osmId'] = $occ->getOsmId();
        $data['osmPlaceId'] = $occ->getOsmPlaceId();
        $data['identiplanteScore'] = $occ->getIdentiplanteScore();
        $data['identiplanteScore_keyword'] = $occ->getIdentiplanteScore();
        $data['isIdentiplanteValidated'] = $occ->getIsIdentiplanteValidated();
        $data['taxoRepo'] = $occ->getTaxoRepo();
        $data['frenchDep'] = $occ->getFrenchDep();

        // Flatten associated Project resource if any:
        if ( null !== $occ->getProject()) {
            $data['projectId'] = $occ->getProject()->getId();
        }
      
		return $data;
	}
 

}



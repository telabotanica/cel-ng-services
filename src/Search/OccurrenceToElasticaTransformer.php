<?php
namespace App\Search;
  

use DateTime;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

use App\Entity\Occurrence;
 
class OccurrenceToElasticaTransformer implements ModelToElasticaTransformerInterface
{
 
	/**
	 * @param Occurrence  $occ
	 * @param array $occ
	 *
	 * @return Document
	 */
	public function transform($occ, array $fields)
	{
		return new Document($occ->getId(), $this->buildData($occ));
	}
 
    // @todo DRY this using meta prog black magic
	protected function buildData($occ)
	{
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
//echo var_dump(strtolower($occ->getGeometry()));
        $data['geom'] = json_decode($occ->getGeometry());
		$data['userId'] = $occ->getUserId();
		$data['userEmail'] = $occ->getUserEmail();
		$data['userPseudo'] = $occ->getUserPseudo();
		$data['observer'] = $occ->getObserver();
		$data['observerInstitution'] = $occ->getObserverInstitution();
        $dateObserved = $occ->getFormattedDateObserved();
        $data['dateObserved'] = $dateObserved;
        $data['dateObserved_keyword'] = $occ->getFormattedDateObserved();
        $data['dateObservedMonth'] = $occ->getDateObservedMonth();
        $data['dateObservedDay'] = $occ->getDateObservedDay();
        $data['dateObservedYear'] = $occ->getDateObservedYear();
        $data['dateCreated'] = $occ->getFormattedDateCreated();
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

        // Flatten associated Project resource if any:
        if ( null !== $occ->getProject()) {
    		$data['projectId'] = $occ->getProject()->getId();
    		$data['projectLabel'] = $occ->getProject()->getLabel();
        }

        if (null !== $occ->getTaxoRepo() ) {
            $data['taxoRepo'] = $occ->getTaxoRepo()->getName();
        }  
                   
		return $data;
	}
 

}


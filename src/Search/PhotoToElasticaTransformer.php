<?php
namespace App\Search;
  

use DateTime;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

use App\Entity\Photo;
use App\Search\PhotoToElasticaTransformer;
 
// @refactor move to a Transformer package
class PhotoToElasticaTransformer  implements ModelToElasticaTransformerInterface
{

 
	/**
	 * @param Occurrence  $post
	 * @param array $occ
	 *
	 * @return Document
	 */
	public function transform($occ, array $fields)
	{
		return new Document($occ->getId(), $this->buildData($occ));
	}

    // @todo DRY this using meta prog black magicgetFormattedDateUpdated
	protected function buildData($photo)
	{
		$data = [];
        $tags = [];
        $occ = $photo->getOccurrence();

        foreach($photo->getPhotoTags() as $tag){
            $nestedTag = array(
                'name' => $tag->getName(),
                'path' => $tag->getPath(),
                'id' => $tag->getId(),
            );
            $tags[] = $nestedTag;
        }
        $data['photoTags'] = $tags;

        $data['id'] = $photo->getId();
        $data['userId'] = $photo->getUserId();
		$data['userEmail'] = $photo->getUserEmail();
		$data['userPseudo'] = $photo->getUserPseudo();
		$data['originalName'] = $photo->getOriginalName();
        $data['dateCreated'] = $photo->getFormattedDateCreated();
        $data['dateUpdated'] = $photo->getFormattedDateUpdated();
        $data['dateShot'] = $photo->getFormattedDateShot();
        $data['dateShot_keyword'] = $photo->getFormattedDateShot();
        $data['dateShotMonth'] = $photo->getDateShotMonth();
        $data['dateShotDay'] = $photo->getDateShotDay();
        $data['dateShotYear'] = $photo->getDateShotYear();
        
        if (isset($occ)) {

		    $dateObserved = $occ->getFormattedDateObserved();
            $data['dateObserved'] = $dateObserved;
            $data['dateObservedMonth'] = $occ->getDateObservedMonth();
            $data['dateObservedDay'] = $occ->getDateObservedDay();
            $data['dateObservedYear'] = $occ->getDateObservedYear();
            $data['dateCreated'] = $occ->getFormattedDateCreated();
            $data['dateUpdated'] = $occ->getFormattedDateCreated();
            $data['datePublished'] = $occ->getFormattedDatePublished();
            $data['userSciName'] = $occ->getUserSciName();
            $data['userSciNameId'] = $occ->getUserSciNameId();
            $data['family'] = $occ->getFamily();
            $data['family_keyword'] = $occ->getFamily();
            $data['isPublic'] = $occ->getIsPublic();
            $data['certainty'] = $occ->getCertainty();
            $data['certainty_keyword'] = $occ->getCertainty();
            $data['locality'] = $occ->getLocality();
            $data['locality_keyword'] = $occ->getLocality();
            $data['osmCounty'] = $occ->getOsmCounty();
            $data['osmCountry'] = $occ->getOsmCountry();
            $data['osmCountryCode'] = $occ->getOsmCountryCode();

            // Flatten Project associated with the Occurrence resource if any:
            if ( null !== $occ->getProject()) {
        		$data['projectId'] = $occ->getProject()->getId();
        		$data['projectLabel'] = $occ->getProject()->getLabel();
            }
        }
	
                   
		return $data;
	}
 

}



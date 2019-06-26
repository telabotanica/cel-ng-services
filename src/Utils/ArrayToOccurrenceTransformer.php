<?php
namespace App\Utils;

use App\Entity\Occurrence;
use App\Entity\OccurrenceUserOccurrenceTagRelation;
use App\Entity\Photo;
use App\Entity\UserOccurrenceTag;
use App\Security\User\TelaBotanicaUser;
use App\DBAL\CertaintyEnumType;

use DateTime;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Transforms (deserializes) data arrays into a <code>Occurrence</code> 
 * objects. 
 *
 * @internal Only used during spreadsheet file import.
 * @package App\Utils
 */
class ArrayToOccurrenceTransformer
{

    private $doctrine;
    private $lineCount = 0;
    // Map between CSV headers (keys) and corresponding index 
    // values (int). Built dynamically.
    private $headerIndexArray = array();

    // Map between CSV headers (keys) and Occurrence members (values)
    const CSV_HEADER_OCC_PROP_MAP = array(
            'Transmis'      => 'isPublic',
            'Spontanéité'   => 'isWild',
            'Observateur' => 'observer',
            "Structure de l'observateur" => 'observerInstitution',
            'Espèce' => 'userSciName',
            'Numéro nomenclatural' => 'userSciNameId',
            'Abondance' => 'coef',
            "Type d'observation" => 'occurrenceType',
            "Floutage" => 'publishedLocation',
            "Phénologie" => 'phenology',
            "Echantillon d'herbier" => 'sampleHerbarium',
            "Certitude" => 'certainty',
            "Altitude" => 'elevation',
            'Référentiel Géographique' => 'geodatum',
            "Milieu" => 'environment',
            "Lieu-dit" => 'sublocality',
            "Station" => 'station',
            "Commune" => 'locality',
            "Pays" => 'osmCountry',
            'Referentiel taxonomique' => 'taxoRepo'
        );

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
     }

    private function explodeAndClean($commaSeparatedValues) {
        $values = explode( ',', $commaSeparatedValues );
        foreach($values as $value) {
            $value = trim($value);
        }
        return $values;
    }

	/**
     * Instanciate and persist an Occurrence instance populated with the 
     * data in the array provided. 
     *
     * @param array $csvLine An array containing the data of a CSV line
     * @param TelaBotanicaUser $user The current user..
     *
     * @return array The ancestor descriptions for the taxon with ID = $taxonId
     *         in the $taxoRepo repository. Returns null if it cannot be 
     *         retrieved.
     */
	public function transform(array $csvLine, TelaBotanicaUser $user) {
		$resultMsgs = array();
		$em = $this->doctrine->getManager();     

	    if ( $this->lineCount == 0 ) {
		     for( $i = 0; $i<sizeof($csvLine); $i++ ) {
			    if (null !== $csvLine[$i]) {
				    $this->headerIndexArray[$csvLine[$i]] = $i;
			    }
		    }		
		    $this->lineCount++;			
	    }
	    else {

		    $occ = new Occurrence();
		    $occ = $this->populateWithUserInfo($occ, $user);
		    $occ = $this->populate($occ, $user, $csvLine);

		    // Handle the photos.
		    // Attach the photos with original names (separated by commas) in 
		    // column with header "Image(s)":
		    $imageNameAsString = $csvLine[$this->headerIndexArray["Image(s)"]];
		    $photoOriginalNames = $this->explodeAndClean($imageNameAsString);
		    $occ = $this->populateWithPhotos($occ, $user, $photoOriginalNames);

		    // Persist the occurrence alongside with photos by cascading: 
		    $em->persist($occ);

		    // Handle the user tags:
		    $tagsAsString = $csvLine[$this->headerIndexArray["Mots Clés"]];
		    $tagNames = $this->explodeAndClean($tagsAsString);
        
		    $occ = $this->populateWithUserTags($occ, $user, $tagNames);
            $this->lineCount++;

            return $occ;

        }
	}

    private function populateWithPhotos($occ, $user, $photoOriginalNames) {

        $em = $this->doctrine->getManager();
        $photoRepo = $em->getRepository('App\Entity\Photo');

        foreach($photoOriginalNames as $imageName) {
            $photos = $photoRepo->findByOriginalNameAndUserId(
                $imageName, $user->getId());
            if ( sizeof($photos)>0 ) {
                $occ->addPhoto($photo[0]);
            }
            // @todo if >1, then log a warning
        }

    	return $occ;
    }


    private function populateWithUserTags($occ, $user, $tagNames) {

        $em = $this->doctrine->getManager();
        $userOccurrenceTagRepo = $em->getRepository(
            'App\Entity\UserOccurrenceTag');

        foreach($tagNames as $tagName) {

            $tags = $userOccurrenceTagRepo->findByNameAndUserId(
                $tagName, $user->getId());

            if ( sizeof($tags)>0 ) {
                // creates and persists a new OccurrenceUserOccurrenceTag 
                // relation with 
		        $rel = new OccurrenceUserOccurrenceTagRelation(); 
                $rel->setUserOccurrenceTag($tags[0]);
                $rel->setOccurrence($occ);
		        $em->persist($rel);
            }
            else {
                $newTag = new UserOccurrenceTag();
                $newTag->setName($tagName);
		        // make it a root tag
                $newTag->setPath('/');
                $newTag->setUserId($user->getId());
                $em->persist($newTag);
		        $rel = new OccurrenceUserOccurrenceTagRelation(); 
                $rel->setUserOccurrenceTag($newTag);
                $rel->setOccurrence($occ);
		        $em->persist($rel);

            }
            // @todo if >1, then log a warning
        }
    	return $occ;
    }

    private function populateWithUserInfo($occ, $user) {
		$occ->setUserId($user->getId());
		$occ->setUserEmail($user->getEmail());
		$occ->setUserPseudo($user->getUsername());
        return $occ;
    }

    private function populate($occ, $user, $csvLine) {

        $lat  = $csvLine[$this->headerIndexArray['Latitude']];
        $long = $csvLine[$this->headerIndexArray['Longitude']];
        
        if ( ( null !== $lat ) && ( null !== $long ) ) {
    	    $occ->setGeometry('{"type" : "Point","coordinates" : [' .  $csvLine[$this->headerIndexArray['Longitude']] . ',' . $csvLine[$this->headerIndexArray['Latitude']] . ']}');
        }

        foreach (ArrayToOccurrenceTransformer::CSV_HEADER_OCC_PROP_MAP as $svHeader => $propertyName) {
	        if ( null !== $csvLine[$this->headerIndexArray[$svHeader]] ) {
                $setterMethodName = 'set' . ucfirst($propertyName);
		        $occ->$setterMethodName($csvLine[$this->headerIndexArray[$svHeader]]);
	        }         
        }

        $obsDate = $csvLine[$this->headerIndexArray["Date"]];
	    if ( null !== $obsDate ) {
		    $occ->setDateObserved(
                DateTime::createFromFormat('d/m/Y', $obsDate));
	    }

	    return $occ;	
	
    }

    // @refactor create a normalizer interface and a BooleanNormalizer
	private function booleanishToBool($booleanish) {

		if ($booleanish == 'oui') {
			return true;
		}
		return false;
	}
}



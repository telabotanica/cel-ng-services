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
 * Worst class name in OO software dev history to date.
 */
class FromArrayOccurrenceCreator
{

    private $doctrine;
    private $lineCount = 0;
    private $headerIndexArray = array();

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
	 */
	public function transform(array $csvLine, TelaBotanicaUser $user)
	{
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
		    $occ = $this->populateTaxoRepo($occ, $user, $csvLine);

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
            $photos = $photoRepo->findByOriginalNameAndUserId($imageName, $user->getId());
            if ( sizeof($photos)>0 ) {
                $occ->addPhoto($photo[0]);
            }
            // @todo if >1, then log a warning
        }

    	return $occ;
    }


    private function populateWithUserTags($occ, $user, $tagNames) {

        $em = $this->doctrine->getManager();
        $userOccurrenceTagRepo = $em->getRepository('App\Entity\UserOccurrenceTag');

        foreach($tagNames as $tagName) {

            $tags = $userOccurrenceTagRepo->findByNameAndUserId($tagName, $user->getId());

            if ( sizeof($tags)>0 ) {
                // creates and persists a new OccurrenceUserOccurrenceTag relation with 
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

    private function populateTaxoRepo($occ, $user, $csvLine) {
		if ( null !== $csvLine[$this->headerIndexArray['Referentiel taxonomique']] ) {
            $em = $this->doctrine->getManager();
            $taxoRepoRepo = $em->getRepository('App\Entity\TaxoRepo');

            $taxoRepo = $taxoRepoRepo->findOneBy(
                array('name' => $csvLine[$this->headerIndexArray['Referentiel taxonomique']])
            );
            if ( null !== $taxoRepo ) {
    			$occ->setTaxoRepo($taxoRepo);
            }
            else {
                // @todo: put this in conf
                $taxoRepo = $taxoRepoRepo->findOneBy(
                    array('name' => 'Autre/Inconnu')
                );
                if ( null !== $taxoRepo ) {
        			$occ->setTaxoRepo($taxoRepo);
                }
                // @todo: create custom exception
                else throw new \LogicException("The taxo repository 'Autre/Inconnu' cannot be found in DB.");
            }
		}

        return $occ;
    }


    private function populate($occ, $user, $csvLine) {

	    $occ->setGeometry('{"type" : "point","coordinates" : [' . $csvLine[$this->headerIndexArray['Latitude']] . ',' . $csvLine[$this->headerIndexArray['Longitude']] . ']}');

        $csvHeaderOccPropertyMap = array(
            'Transmis'      => 'isPublic',
            'Spontanéité'   => 'isWild',
            'Observateur' => 'observer',
            "Structure de l'observateur" => 'observerInstitution',
            'Espèce' => 'userSciName',
            'Numéro nomenclatural' => 'userSciNameId',
            'Abondance' => 'coef',
            "Type d'observation" => 'observationType',
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
            "Pays" => 'osmCountry'
        );

        foreach ($csvHeaderOccPropertyMap as $svHeader => $propertyName) {
	        if ( null !== $csvLine[$this->headerIndexArray[$svHeader]] ) {
                $setterMethodName = 'set' . ucfirst($propertyName);
		        $occ->$setterMethodName($csvLine[$this->headerIndexArray[$svHeader]]);
	        }         
        }

	    if ( null !== $csvLine[$this->headerIndexArray["Date"]] ) {
		    $occ->setDateObserved(DateTime::createFromFormat('d/m/Y', $csvLine[$this->headerIndexArray['Date']]));
	    }

	    return $occ;	
	
    }
    // @todo create a normalizer interface and a BooleanNormalizer
	private function booleanishToBool($booleanish) {

		if ($booleanish == 'oui') {
			return true;
		}
		return false;
	}
}


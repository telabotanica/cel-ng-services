<?php

namespace App\Service;

use App\Entity\Occurrence;
use App\Entity\Photo;
use App\Entity\PhotoPhotoTagRelation;
use App\Entity\PhotoTag;
use App\Model\PlantnetImage;
use App\Model\PlantnetOccurrence;
use App\Repository\PhotoRepository;
use App\Repository\PhotoTagPhotoRepository;
use App\Repository\PhotoTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoBuilderService
{
	private const PHOTO_TAG = [
		'leaf' => 'feuille',
		'flower' => 'fleur',
		'fruit' => 'fruit',
		'bark' => 'ecorce',
		'habit' => 'port',
		'other' => 'autre',
	];
	private $photoTagRepository;
	private $photoRepository;
	
	private $photoTagPhotoRepository;
	private $em;
	private $annuaireService;
	
	public function __construct(PhotoTagRepository $photoTagRepository, PhotoRepository $photoRepository,
								PhotoTagPhotoRepository $photoTagPhotoRepository,
								EntityManagerInterface $em,
								AnnuaireService $annuaireService)
	{
		$this->em = $em;
		$this->photoTagRepository = $photoTagRepository;
		$this->photoRepository = $photoRepository;
		$this->photoTagPhotoRepository = $photoTagPhotoRepository;
		$this->annuaireService = $annuaireService;
	}
	
	public function createPhoto(File $file, Occurrence $occurrence): Photo
    {
        $photo = new Photo();
        $photo->setImageFile(new UploadedFile($file->getPathname(), $file->getBasename(), $file->getMimeType(), null, true))
            ->setOccurrence($occurrence)
            ->setUserId($occurrence->getUserId())
            ->setUserPseudo($occurrence->getUserPseudo())
            ->setUserEmail($occurrence->getUserEmail())
            ->setDateCreated(new \DateTimeImmutable());

		$photo->setDateLinkedToOccurrence($occurrence->getDatePublished());
		/*
		if ($occurrence->getGeometry()){
			$coordinates = json_decode($occurrence->getGeometry());
			$photo->setLongitude($coordinates->coordinates[0])
				->setLatitude($coordinates->coordinates[1]);
		}
*/
        return $photo;
    }
	
	public function savePhotoTag(PhotoTag $tag, Photo $photo)
	{
		$photoTagRelation = new PhotoPhotoTagRelation();
		
		$photoTagRelation->setPhoto($photo)
			->setPhotoTag($tag);
		
		$this->em->persist($photoTagRelation);
	}
	
	public function updatePhotoTag($newTag, $photo, $existingPhotoTag){
		
		$photo->removePhotoTag($existingPhotoTag[0], $this->em);
		$this->savePhotoTag($newTag, $photo);
		
		$this->em->persist($photo);
	}
	
	public function getTag($image, $userId){
		$tagName = $image->getOrgan();
		$tagName = self::PHOTO_TAG[$tagName];
		$tag = $this->photoTagRepository->findOneBy(['name' => $tagName, 'userId' => $userId]);
		
		if (!$tag){
			$tag = $this->createTag($image->getOrgan(), $userId);
		}
		
		return $tag;
	}
	
	public function createTag($tagName, $userId){
		$tagName = self::PHOTO_TAG[$tagName];
		
		$tag = new PhotoTag();
		
		$tag->setName($tagName)
			->setPath('/')
			->setUserId($userId);
		
		$this->em->persist($tag);
		
		return $tag;
	}
	
	public function isImagesChanged(Occurrence $occurrence, PlantnetOccurrence $pnOccurrence){

		$user = $this->annuaireService->findUserInfo($pnOccurrence->getAuthor()->getEmail());
		
		foreach ($pnOccurrence->getImages() as $image) {
			if (!$occurrence->isExistingPhoto($image)) {
				// c'est une nouvelle photo
				return true;
			} else {
				// La photo existe déjà, on vérifie si le tag a changé
				$tag = $this->getTag($image, $user->getId());
				
				$photo = $this->photoRepository->findOneByOriginalNameStartingWith($image->getId());
				
				$tagChanged = $this->isTagChanged($tag, $photo);

				// Si le tag de la photo a changé; la photo a changé
				if ($tagChanged){
					return true;
				}
				
				// Si la photo existante n'a pas de tag mais la nouvelle en a la photo a changé
				if (!$photo->getPhotoTags() && $tag){
					return true;
				}
			}
		}
		return false;
	}
	
	public function isTagChanged(PhotoTag $newTag, Photo $photo){
		$existingPhotoTag = $photo->getPhotoTags();
		
		if ($existingPhotoTag && ($existingPhotoTag[0]->getName() !== $newTag->getName())){
			return true;
		}
		
		return false;
	}
}

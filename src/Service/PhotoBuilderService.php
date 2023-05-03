<?php

namespace App\Service;

use App\Entity\Occurrence;
use App\Entity\Photo;
use App\Entity\PhotoPhotoTagRelation;
use App\Entity\PhotoTag;
use App\Model\PlantnetImage;
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
	
	public function __construct(PhotoTagRepository $photoTagRepository, PhotoRepository $photoRepository,
								PhotoTagPhotoRepository $photoTagPhotoRepository,
								EntityManagerInterface $em)
	{
		$this->em = $em;
		$this->photoTagRepository = $photoTagRepository;
		$this->photoRepository = $photoRepository;
		$this->photoTagPhotoRepository = $photoTagPhotoRepository;
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

        return $photo;
    }
	
	public function savePhotoTag(PhotoTag $tag, Photo $photo)
	{
		$photoTagRelation = new PhotoPhotoTagRelation();
		
		$photoTagRelation->setPhoto($photo)
			->setPhotoTag($tag);
		
		$this->em->persist($photoTagRelation);
	}
	
	public function updatePhotoTag(PhotoTag $tag, PlantnetImage $image){
		
		$photo = $this->photoRepository->findOneBy(['originalName' => $image->getId()]);
		
		$existingPhotoTag = $this->photoTagPhotoRepository->findOneBy(['photoId' => $photo->getId()]);
		
		if ($existingPhotoTag){
			if ($existingPhotoTag !== $tag){
				$existingPhotoTag->setPhotoTag($tag);
				
				$this->em->persist($existingPhotoTag);
			}
		} else {
			$this->savePhotoTag($tag, $photo);
		}
	}
	
	public function getTag($image){
		$tagName = $image->getOrgan();
		$tagName = self::PHOTO_TAG[$tagName];
		$tag = $this->photoTagRepository->findOneBy(['name' => $tagName]);
		
		if ($tag){
			return $tag;
		}
		
		return null;
	}
}

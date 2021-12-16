<?php

namespace App\Service;

use App\Entity\Occurrence;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoBuilderService
{
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
}

<?php

namespace App\Service;

use App\Repository\PhotoRepository;

class PhotoService
{
    private $photoRepository;

    public function __construct(PhotoRepository $photoRepository)
    {
        $this->photoRepository = $photoRepository;
    }

    public function isPhotoAlreadyExists(int $photoId): bool {
        return (bool) $this->photoRepository->findOneBy(['id' => $photoId]);
    }
}

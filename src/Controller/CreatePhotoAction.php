<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\Entity\Photo;
use App\Form\PhotoType;

final class CreatePhotoAction
{
    private $validator;
    private $doctrine;
    private $factory;

    public function __construct(RegistryInterface $doctrine, FormFactoryInterface $factory, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->factory = $factory;
    }

    /**
     * //@IsGranted("ROLE_USER")
     */
    public function __invoke(Request $request): Photo
    {
        $photo = new Photo();
        $photoRepo = $this->doctrine->getRepository(Photo::class);

        $form = $this->factory->create(PhotoType::class, $photo);
        $form->handleRequest($request);
/*
        if ($form->isSubmitted() && $form->isValid()) {
*/


        $originalName = $request->files->get('file')->getClientOriginalName();
        $photoWithSameName = $photoRepo->findBy(array('originalName' => $originalName));


        if ( (sizeof($photoWithSameName)==0) ) {

            $em = $this->doctrine->getManager();

            $em->persist($photo);
            $em->flush();

            // Prevent the serialization of the file property
            $photo->file = null;

            return $photo;
        }

        // This will be handled by API Platform and returns a validation error.
        throw new \Exception("A photo with the same name is already present in the user gallery. This is not allowed.");
    }
}



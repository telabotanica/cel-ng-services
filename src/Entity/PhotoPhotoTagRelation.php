<?php


namespace App\Entity;

use App\Entity\Photo;
use App\Entity\PhotoTag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a photo tag.
 * Mot-clé photo.
 *
 * @ORM\Entity
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"read"}},
 *     "denormalization_context"={"groups"={"write"}}
 * })
 * @ORM\Table(name="photo_tag_photo", options={"comment":"Table de jointure entre Photo et PhotoTag."})
 */
class PhotoPhotoTagRelation
{

   /**
    * @Groups({"read"})
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="IDENTITY")
    * @ORM\Column(type="integer")
    */
   private $id = null;

    /**
     *
     * @Assert\NotNull
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity=Photo::class, inversedBy="photoTagRelations")
     * @ApiSubresource(maxDepth=1)
     */
    protected $photo;


    /**
     * @Assert\NotNull  
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity=PhotoTag::class, inversedBy="photoRelations")
     * @ApiSubresource(maxDepth=1)
     */
    protected $photoTag;


    /**
     * @return PhotoTag
     */
    public function getPhotoTag(): PhotoTag
    {
        return $this->photoTag;
    }


    /**
     * @return Photo
     */
    public function getPhoto(): Photo
    {
        return $this->photo;
    }


}

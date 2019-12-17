<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a notification that an entity changed outside of Doctrine.
 *
 * @internal Only used by <code>SyncDocumentIndexCommand</code>.
 *
 * @ORM\Entity()
 * @ORM\Table(name="change_log")
 */
class ChangeLog  {

   /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id = null;

   /**
     * ID of the entity the change log is about.
     *

     * @ORM\Column(name="entity_id", type="integer", nullable=true, options={"comment":"ID de l'entité"})
     */
    private $entityId = null;

   /**
     * Type of action to be mirrored in the ES index.
     *
     * @ORM\Column(name="action_type", type="string", nullable=false, options={"comment":"Action sur l'entité à répercuter dans l'index"})
     */
    private $actionType = null;

   /**
     * Name of the entity.
     *
     * @ORM\Column(name="entity_name", type="string", nullable=false, options={"comment":"Nom de l'entité sur laquelle porte l'action à répercuter."})
     */
    private $entityName = null;


    public function getEntityName(): string {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self {
        $this->entityName = $entityName;

        return $this;
    }

    public function getEntityId(): int {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self {
        $this->entityId = $entityId;

        return $this;
    }

    public function getActionType(): string {
        return $this->actionType;
    }

    public function setActionType(string $actionType): self {
        $this->actionType = $actionType;

        return $this;
    }

}

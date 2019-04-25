<?php

namespace App\EventListener;

use App\Entity\Occurrence;
use App\TelaBotanica\Eflore\Api\EfloreApiClient;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Populates various properties of <code>Occurrence</code> instances 
 * based on CEL business rules before they are persisted/updated.
 * The properties can be "family", "dateUpdated", "datePublished" and 
 * "isPublic".
 *
 * @package App\EventListener
 */
class OccurrenceEventListener {


    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage = null)  {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Populates various properties of <code>Occurrence</code> instances 
     * based on CEL business rules before they are persisted.
     *
     * @param LifecycleEventArgs $args The Lifecycle Event emitted.
     */
    public function prePersist(LifecycleEventArgs $args) { 
        $entity = $args->getEntity();

        // only act on "Occurrence" class instances:
        if (!$entity instanceof Occurrence) {
            return;
        }

        $this->doCommon($entity);

        // If isPublic status has just been set to true, set the occurrence
        // datePublished member value to "now":
        if ( $entity->getIsPublic() ) {
            $entity->setDatePublished(new \DateTime());
        }
        $entity->setIdentiplanteScore(0);

        if ( null !== $entity->getTaxoRepo() && 
            null !== $entity->getUserSciNameId()  ){

            $efClient = new EfloreApiClient();
            $userSciNameId = $entity->getUserSciNameId();
            $taxoRepoName = $entity->getTaxoRepo();
            $familyName = $efClient->getFamilyName(
                $userSciNameId, $taxoRepoName);
            if ( null !== $familyName ) {
                $entity->setFamily($familyName);
            }
        }
    }

    /**
     * Populates various properties of <code>Occurrence</code> instances 
     * based on CEL business rules before they are updated (isPublic,
     * datePublished, signature, dateUpdated.
     *
     * @param LifecycleEventArgs $args The Lifecycle Event emitted.
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        // only act on "Occurrence" class instances:
        if (!$entity instanceof Occurrence) {
            return;
        }

        $entity->setDateUpdated(new \DateTime());

        // If isPublic status has been changed to true, set the occurrence 
        // datePublished to "now":
        if ( $args->hasChangedField('isPublic') && 
            $args->getNewValue('isPublic') == true) {

            $entity->setDatePublished(new \DateTime());
        }

        $this->doCommon($entity);
    }

    private function doCommon(Occurrence $occ) {
        // If the occurrence cannot be published:
        if ( ! ($occ->isPublishable()) ) {
            // Force it to be private:
            $occ->setIsPublic(false);
        }

        if  ( null == $occ->getObserver() ) {
            if ( null !== $currentUser = $this->getUser() ) {
                $pseudo = $currentUser->getSurname() . ' ' . $currentUser->getLastName();
                $occ->setObserver($pseudo);
            }
        }   
        $occ->generateSignature();
    }


    protected function getUser() {
        if (!$this->tokenStorage) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

}

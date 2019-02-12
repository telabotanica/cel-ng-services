<?php

namespace App\EventListener;

use App\Entity\Photo;
use App\Entity\PhotoTag;
use App\Entity\Occurrence;
use App\Entity\UserOccurrenceTag;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * Populates user related properties of "owned" entities (i.e. belonging to a 
 * user) based on currently logged user.
 *
 * @package App\EventListener
 */
// @todo: find an alternate solution to populate OwnedEntityInterface entities with
// token info NOT by waiting... MAJOR IMPACT ON IMPORT DELAY! 
class OwnedEntityEventListener {

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage = null)  {
        $this->tokenStorage = $tokenStorage;
    }

    // @perf @todo: optimize this... find another workaround...
    public function prePersist(LifecycleEventArgs $args) {

        // OMG, I cannot believe this has to be done... Else, the token is not set 
        // because of a concurrency race...
        // https://stackoverflow.com/questions/37854796/token-storage-in-symfony2-has-no-token
        // https://stackoverflow.com/questions/39350442/symfony-3-doctrine-listener-service-inject-token-storage-doesnt-work
        sleep(0.01);
        $entity = $args->getEntity();

        // Why the hell doesn't the following work? 
        //  if ( !$entity instanceof OwnedEntitySimpleInterface ) {
        if  ( !( 
            ( $entity instanceof Occurrence ) || ( $entity instanceof Photo ) || 
            ( $entity instanceof UserOccurrenceTag )  || 
            ( $entity instanceof PhotoTag ) )) {
            return;
        }

        if ( null !== $currentUser = $this->getUser() ) {

            $entity->setUserId($currentUser->getId());
            //if ( $entity instanceof OwnedEntityFullInterface ) {
            if  ( ( $entity instanceof Occurrence ) || 
                ( $entity instanceof Photo ) ) {

                $entity->setUserEmail($currentUser->getEmail());
                if ( null !== $currentUser->getPseudo()) {
                    $entity->setUserPseudo($currentUser->getPseudo());
                }
                else {
                    $pseudo = $currentUser->getSurname() . ' ' . $currentUser->getLastName();
                    $entity->setUserPseudo($pseudo);
                }
                if  ( $entity instanceof Occurrence ) {
                    $entity->setObserver($entity->getUserPseudo());
                }
            }
        } else {
            $entity->setUserId(-1);
        }
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

<?php

namespace App\EventListener;

use App\Entity\Photo;
use App\TelaBotanica\Eflore\Api\EfloreApiClient;

use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Routing\RequestContext;

/**
 * Populates various properties of <code>Photo</code> instances before they 
 * are persisted. The properties are 'url' and exif data. Also updates 
 * other properties based on the values passed in the uploaded JSON file if 
 * any.
 *
 * @package App\EventListener
 */
class PhotoEventListener {

    private $uploaderHelper;
    private $requestContext;

    public function __construct(UploaderHelper $uploaderHelper, RequestContext $requestContext) {
        $this->uploaderHelper = $uploaderHelper;
		$this->requestContext = $requestContext;
    }

    /**
     * Populates 'url' and exif related properties of <code>Photo</code>
     * instances  before they are persisted. Also updates other properties 
     * based on values passed in the uploaded JSON file if any.
     *
     * @param LifecycleEventArgs $args The Lifecycle Event emitted.
     */
    public function prePersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        // only act on some "Photo" entity
        if (!$entity instanceof Photo) {
            return;
        }

      // All properties can be updated with the ones in the JSON file
      // So we purge the associative array of all entries with keys belonging
      // to the set of property names which are not overwritten right after 
      // and that can cause issues. This is a bit bit paranoid cos there is no
      // interest for an evil user to fuck his own records up plus it should
      // have no nasty side effects but hey! let's do it anyway!
      $forbiddenKeys = array(
         "occurrence",
         "photoTags",
         "userEmail",
         "userId",
         "userPseudo",
         "c",
         "d",
      );
      if ( isset($entity->json) ) {
         $entity->fillPropertiesFromJsonFile($entity->json->getRealPath(), $forbiddenKeys);
      }
      $entity->fillPropertiesWithImageExif();
      $imgUrl = $this->getHostUrl() . getenv('APP_PREFIX_PATH') . $this->uploaderHelper->asset($entity, 'file');
      $entity->setUrl($imgUrl);
    }

    /**
     * Returns the host URL (<scheme>://<host>:<port>).
     *
     * @return string the host URL.
     */
    private function getHostUrl(): string {
        $scheme = $this->requestContext->getScheme();
        $url = $scheme.'://'.$this->requestContext->getHost();
        $httpPort = $this->requestContext->getHttpPort();
        if ('http' === $scheme && $httpPort && 80 !== $httpPort) {
            return $url.':'.$httpPort;
        }
        $httpsPort = $this->requestContext->getHttpsPort();
        if ('https' === $scheme && $httpsPort && 443 !== $httpsPort) {
            return $url.':'.$httpsPort;
        }
        return $url;
    }

}

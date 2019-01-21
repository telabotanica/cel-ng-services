<?php

namespace App\Controller;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Photo;
use App\Controller\AbstractBulkAction;

/**
 * Custom operation on Photo resources for bulk actions. Currently, only 
 * DELETE, PUT and PATCH HTTP verbs/REST actions are offered. 
 *
 * AT impl an endpoint for bulk operations is not supported in API-platform yet. 
 * The strategy planned to implement them is to POST a multipart HTTP 
 * request contains inner HTTP requests to a bulk endpoint as announced 
 * here https://github.com/api-platform/core/pull/1645.
 * As, at the time of this writing, Angular can't generate such requests, we 
 * chose to implement an approach like the one described here instead: 
 * https://apihandyman.io/api-design-tips-and-tricks-getting-creating-updating-or-deleting-multiple-resources-in-one-api-call/#different-actions-on-resources-of-different-types
 *
 */
class PhotoBulkAction extends AbstractBulkAction
{

    /**
     * @inheritdoc
     */
    protected function initForm($entity) {
        $this->form = $this->factory->create(Photo::class, $entity);
    }

    /**
     * @inheritdoc
     */
    protected function initRepo() {
        $this->repo = $this->doctrine->getRepository('App\Entity\Photo');
    }

  
}



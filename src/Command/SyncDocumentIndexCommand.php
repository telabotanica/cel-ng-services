<?php

namespace App\Command;

use App\Entity\ChangeLog;
use App\Elastica\Client\ElasticsearchClient;
use App\Command\UnknownEntityNameException;

use App\Entity\Occurrence;
use App\Entity\Photo;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\ElasticaBundle\Persister\ObjectPersister;

/** 
 * Simple command which loads change notifications from change_log table and 
 * mirrors DB changes in ES indexes. change_log table is populated using
 * SQL triggers.
 */
class SyncDocumentIndexCommand  extends Command {

    private $changeLogsAsIterable;
    private $entityManager;
    private $occurrencePersister;
    private $photoPersister;
    private $elasticsearchClient;

    private const ALLOWED_ENTITY_NAMES = [Occurrence::RESOURCE_NAME, Photo::RESOURCE_NAME];
    private const OCC_PERSISTER_ALIAS = 'fos_elastica.object_persister.' . 
        'occurrences.occurrence';
    private const PHOTO_PERSISTER_ALIAS = 'fos_elastica.object_persister.' . 
        'photos.photo';

    public function __construct(ContainerInterface $container, ElasticsearchClient $elasticsearchClient) {
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->occurrencePersister = $container->get(
            SyncDocumentIndexCommand::OCC_PERSISTER_ALIAS);
        $this->photoPersister = $container->get(
            SyncDocumentIndexCommand::PHOTO_PERSISTER_ALIAS);
        $this->elasticsearchClient = $elasticsearchClient;

        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('cel:sync-es')
            ->setDescription('Keep in sync the elasticsearch index using notifications stored in the change_log table.');
    }

    private function init() {
        $this->changeLogsAsIterable = $this->loadChangeLogsAsIterable();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
	    $counter = 0;
        $output->writeln("Loading change logs...");
        $this->init();
        $output->writeln("Change logs loaded.");

        foreach ($this->changeLogsAsIterable as $row) {
            /**
             * @var ChangeLog $changeLog
             */
            $changeLog = $row[0];

            if ( in_array($changeLog->getEntityName(), SyncDocumentIndexCommand::ALLOWED_ENTITY_NAMES) ) {
                try {
                    $this->executeAction($changeLog);
                } catch (\Exception $e) {
                    $syncInfo = sprintf('%s %s %d',
                        $changeLog->getActionType(),
                        $changeLog->getEntityName(),
                        $changeLog->getEntityId()
                    );
                    $subject = 'CEL-services : Erreur de synchro ES : '.$syncInfo;
                    $exceptionInfo = sprintf("Message : %s \n Fichier : %s \n Ligne : %d",
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    );
                    $message = $subject.'\n'.$exceptionInfo;

                    $changeLog->setActionType('error');
                    $this->entityManager->flush();

                    mail('webmestre@tela-botanica.org', $subject, $message);

                    continue;
                }
                //$output->writeln("Change log mirrored in ES index for entity/document with ID = " . $changeLog->getEntityId());
                $this->entityManager->remove($changeLog);
                // Should not be required, removing should detach
                //$this->entityManager->detach($changeLog);
                $counter++;
		        if ( $counter%10000 === 0 ) {
			        $s = microtime(true);
			        $this->entityManager->flush();
			        $e = microtime(true);
			        $output->writeln("Flushed $counter rows in " . ($e - $s));
			        $this->entityManager->clear();
			        $counter = 0;
                    $output->writeln("Change log mirrored in ES index for entity/document with ID = " . $changeLog->getEntityId());    
		        }
            }
            else {
                $ex = new UnknownEntityNameException('Unknwown entity name: ' . $changeLog->getEntityName());
                throw $ex;
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        $output->writeln("All changes have been mirrored.");
    }

    private function loadChangeLogsAsIterable() {
        $q = $this->entityManager->createQuery("select u from App\Entity\ChangeLog u where u.actionType != 'error'");
        return $q->iterate();
    }

    private function executeAction(ChangeLog $changeLog){
        switch ($changeLog->getActionType()) {
            case "create":
                $entity = $this->getRepository($changeLog->getEntityName())->find($changeLog->getEntityId());
                if ($entity !== null) {
                    $this->createDocument($entity, $changeLog->getEntityName());
                }
                break;
            case "update":
                $entity = $this->getRepository($changeLog->getEntityName())->find($changeLog->getEntityId());
                if ($entity !== null) {
                    $this->updateDocument($entity, $changeLog->getEntityName());
                }
                break;
            case "delete":
                    $this->deleteDocument($changeLog->getEntityId(), $changeLog->getEntityName());
                break;
            default:
                break;
        }
    }

    private function getRepository($entityClassName) {
        return $this->entityManager->getRepository('App:' . ucfirst($entityClassName));
    }

    private function getPersister($entityClassName) {
        switch ($entityClassName) {
            case Occurrence::RESOURCE_NAME:
                return $this->occurrencePersister;
                break;
            case Photo::RESOURCE_NAME:
                return $this->photoPersister;
                break;
            default:
                throw new \LogicException(sprintf('you shoud not land here, class "%s" not supported', $entityClassName));
                break;
        }
    }

    private function deleteDocument(int $id, string $resourceTypeName) {
        $this->elasticsearchClient->deleteById($id, $resourceTypeName);
    }

    /**
     * @param Occurrence|Photo $entity
     * @param string $entityName
     */
    private function updateDocument($entity, string $entityName) {
        $this->getPersister($entityName)->replaceOne($entity);
    }

    /**
     * @param Occurrence|Photo $entity
     * @param string $entityName
     */
    private function createDocument($entity, string $entityName) {
        $this->getPersister($entityName)->replaceOne($entity);
    }

}   

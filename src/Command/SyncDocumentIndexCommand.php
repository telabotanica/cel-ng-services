<?php

namespace App\Command;

use App\Entity\ChangeLog;
use App\Utils\ElasticsearchClient;
use App\Command\UnknownEntityNameException;

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
    private $em;
    private $occurrencePersister;
    private $photoPersister;
    private const ALLOWED_ENTITY_NAMES = ['occurrence', 'photo'];


    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $entityManager
    ) {
        $this->em = $entityManager;
        $this->occurrencePersister = $container->get('fos_elastica.object_persister.occurrences.occurrence');
        $this->photoPersister = $container->get('fos_elastica.object_persister.photos.photo');
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
        $output->writeln("Loading change logs...");
        $this->init();
        $output->writeln("Change logs loaded.");

        $counter = 0;

        foreach($this->changeLogsAsIterable as $row) {

            /**
             * @var ChangeLog $changeLog
             */
            $changeLog = $row[0];

            try {
                $this->executeAction($changeLog);
            } catch (\Exception $e) {
                $changeLog->setErrorCount($changeLog->getErrorCount() + 1);
                if (10 <= $changeLog->getErrorCount()) {
                    $subject = sprintf('CEL-services : Erreur de synchro ES : %s %s %d',
                        $changeLog->getActionType(),
                        $changeLog->getEntityName(),
                        $changeLog->getEntityId()
                    );

                    $text = 'Abandon après '.$changeLog->getErrorCount().' essais.';

                    $exceptionInfo = sprintf("Message d'erreur : %s \n Fichier : %s \n Ligne : %d \n EntityName : %s \n EntityId : %d \n Action : %s",
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine(),
                        $changeLog->getEntityName(),
                        $changeLog->getEntityId(),
                        $changeLog->getActionType()
                    );

                    $message = $subject."\n".$text."\n".$exceptionInfo;

                    mail('webmestre@tela-botanica.org', $subject, $message);

                    $changeLog->setActionType('error');
                }

                continue;
            }
            //$output->writeln("Change log mirrored in ES index for entity/document with ID = " . $changeLog->getEntityId());
            $this->em->remove($changeLog);
            // Should not be required, removing should detach
            //$this->entityManager->detach($changeLog);
            $counter++;
            if ($counter%10000 === 0) {
                $s = microtime(true);
                $this->em->flush();
                $e = microtime(true);
                $output->writeln("Flushed $counter rows in " . ($e - $s));
                $this->em->clear();
                $counter = 0;

                $output->writeln("Change log mirrored in ES index for entity/document with ID = " . $changeLog->getEntityId());
            }
        }
        $this->em->flush();
        $this->em->clear();
        $output->writeln("All changes have been mirrored.");

    }

    private function loadChangeLogsAsIterable() {
        // this should be done in a dedicated ChangelogRepository method
        $entities = implode('\',\'', self::ALLOWED_ENTITY_NAMES);
        $q = sprintf(
            "SELECT u FROM App\Entity\ChangeLog u WHERE u.actionType != 'error' AND u.entityName IN ('%s')",
            $entities
        );
        $q = $this->em->createQuery($q);
        return $q->iterate();
    }

    private function executeAction($changeLog){
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
        return $this->em->getRepository('App:' . ucfirst($entityClassName));
    }

    private function getPersister($entityClassName) {
        if ($entityClassName === 'occurrence') {
            return $this->occurrencePersister;
        }

        if ($entityClassName === 'photo') {
            return $this->photoPersister;
        }
    }

    private function deleteDocument(int $id, string $resourceTypeName) {
        ElasticsearchClient::deleteById($id, $resourceTypeName);
    }

    private function updateDocument($entity, $entityName) {
        $this->getPersister($entityName)->replaceOne($entity);
    }

    private function createDocument($entity, $entityName) {
        $this->getPersister($entityName)->replaceOne($entity);
    }
}   

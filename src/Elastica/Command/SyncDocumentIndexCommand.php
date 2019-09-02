<?php

namespace App\Elastica\Command;

use App\Utils\ElasticsearchClient;
use App\Elastica\Command\UnknownEntityNameException;

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

    private $changeLogs;
    private $entityManager;
    private $occurrencePersister;
    private $photoPersister;
    private const ALLOWED_ENTITY_NAMES = ['occurrence', 'photo'];


    public function __construct(ContainerInterface $container) {
        $this->entityManager = $container->get('doctrine')->getManager();
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
        $this->changeLogs = $this->loadChangeLogs();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("Loading change logs...");
        $this->init();
        $output->writeln("Change logs loaded.");
        foreach( $this->changeLogs as $changeLog) {
            if ( in_array($changeLog->getEntityName(), SyncDocumentIndexCommand::ALLOWED_ENTITY_NAMES) ) {
                $this->executeAction($changeLog);   
                $output->writeln("Change log mirrored in ES index for entity/document with ID = " . $changeLog->getEntityId());    
            }
            else {
                $ex = new UnknownEntityNameException('Unknwown entity name: ' . $changeLog->getEntityName());
                throw $ex;
            }
        }
        $output->writeln("All changes have been mirrored.");
        $this->deleteChangeLogs();
        $output->writeln("Associated ChangeLog records have been deleted.");
    }

    private function deleteChangeLogs() {   
        foreach( $this->changeLogs as $changeLog) {
            $this->entityManager->remove($changeLog);     
        }
        $this->entityManager->flush();
    }

    private function loadChangeLogs() {
        return $this->entityManager->getRepository('App:ChangeLog')->findAll();
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
        }


    }

    private function getRepository($entityClassName) {
        return $this->entityManager->getRepository('App:' . ucfirst($entityClassName));
    }

    private function getPersister($entityClassName) {
        if ( $entityClassName == 'occurrence') {
            return $this->occurrencePersister;
        }
        return $this->photoPersister;
    }

    private function deleteDocument(int $id, string $resourceTypeName) {
        ElasticsearchClient::deleteById($id, $resourceTypeName);
    }

    private function updateDocument(object $entity, $entityName) {
        $this->getPersister($entityName)->replaceOne($entity);
    }


    private function createDocument(object $entity, $entityName) {
        $this->getPersister($entityName)->replaceOne($entity);
    }

}   

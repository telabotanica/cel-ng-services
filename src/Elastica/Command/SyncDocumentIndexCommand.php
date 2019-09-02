<?php

namespace App\Elastica\Command;

use App\Utils\ElasticsearchClient;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;


use FOS\ElasticaBundle\Index\IndexManager;

/** 
 * Simple command which loads change notifications from change_log table and 
 * mirrors DB changes in ES indexes. change_log table is populated using
 * SQL triggers.
 */
class SyncDocumentIndexCommand  extends Command {

    private $changeLogs;
    private $entityManager;


    public function __construct(ContainerInterface $container) {
        $this->entityManager = $container->get('doctrine')->getManager();
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
            $this->executeAction($changeLog);   
            $output->writeln("Change log mirrored in ES index.");    
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
                $this->createDocument($entity);
            break;
            case "update":
                $entity = $this->getRepository($changeLog->getEntityName())->find($changeLog->getEntityId());
                $this->updateDocument($entity);
            break;
            case "delete":
                $this->deleteDocument($changeLog->getEntityId(), $changeLog->getEntityName());
            break;        
        }


    }

    private function getRepository($entityClassName) {
        return $this->entityManager->getRepository('App:' . ucfirst($entityClassName));
    }



    private function deleteDocument(int $id, string $resourceTypeName) {
        ElasticsearchClient::deleteById($id, $resourceTypeName);
    }

    private function updateDocument(object $entity) {
        // Just loads the entity, updates its new dateUpdated and  persists it. It will trigger index insertion
        $entity->setDateUpdated(new \DateTime());
        $this->entityManager->flush();        
        //$this->persister->replaceOne($entity);

    }


    private function createDocument(object $entity) {
        $this->updateDocument($entity);

    }

}

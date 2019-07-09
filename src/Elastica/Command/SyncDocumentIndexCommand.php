<?php

namespace App\Elastica\Command;

use App\Utils\ElasticsearchClient;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** 
 * Simple command which loads change notifications from change_log table and 
 * mirrors DB changes in ES indexes. change_log table is populated using
 * SQL triggers.
 */
class SyncDocumentIndexCommand extends ContainerAwareCommand {

    private $changeLogs;
    private $persister;
    private $entityManager;
    private $container;


    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->entityManager = $this->container->get('Doctrine\ORM\EntityManagerInterface');
        $this->persister = $this->container->get('FOS\ElasticaBundle\Persister\ObjectPersisterInterface');
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
        $this->init();
        foreach( $this->changeLogs as $changeLog) {
            $this->executeAction($changeLog, $entity);       
        }
        $this->deleteChangeLogs();
        $output->writeln("All changes have been mirrored and associated ChangeLog records have been deleted.");
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

    private function executeAction($changeLog, $entity){
        switch ($changeLog->getActionType()) {
            case "create":
                $entity = $this->getRepository($changeLog->getEntityName());
                $this->createDocument($entity);
            break;
            case "update":
                $entity = $this->getRepository($changeLog->getEntityName());
                $this->updateDocument($entity);
            break;
            case "delete":
                $this->deleteDocument($changeLog->getEntityId());
            break;        
        }


    }

    private function getRepository($entityClassName) {
        return $this->entityManager->getRepository('App:' . entityClassName);
    }



    private function deleteDocument(int $id) {
        ElasticsearchClient::deleteById($id, $resourceTypeName);
    }

    private function updateDocument(object $entity) {
        // Just loads the entity, updates its new dateUpdated and  persists it. It will trigger indexinsertion
        $this->persister->replaceOne($entity);

    }


    private function createDocument(object $entity) {
        $this->updateDocument($entity);

    }

}

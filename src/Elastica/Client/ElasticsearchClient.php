<?php

namespace App\Elastica\Client;

use App\Entity\Occurrence;
use App\Entity\Photo;
use Elastica\Query;

use Symfony\Component\Dotenv\Dotenv;

/**
 * Elasticsearch HTTP API client. Only used to retrieve the total number 
 * of hits for given <code>Query</code> and index name of the resource in 
 * ES. Introduced because of an elastica bug (failing at counting 
 * total number of hits).
 *
 * @internal the conf is in the .env file while it should be retrieved from 
 *           foselastica yaml config. Unfortunately, no *clean* way has been 
 *           found to access foselastica conf...
 * @package App\Utils
 */
/*
Elastica bug:
//
// getNbResults() returns 1... howmanyever the number of actual hits...
// elastica is buggy on this... 
$results = $this->findPaginated($esQuery);
$results->setMaxPerPage(10);
$results->setCurrentPage(1);
return $results->getNbResults();

// This workaround has also been tried but the searchable also returns 1...     
// https://stackoverflow.com/questions/27146787/count-query-with-php-elastica-and-symfony2-foselasticabundle/31162189
*/
//@refactor if not int throw ElasticsearchCountException
//@refactor: avoid magic strings! use public static const variables 
//           for 'occurrence' and 'photo' + use them in ImportOccurrenceAction 
//           + syncdoc command
class ElasticsearchClient {

    private $elasticsearchUrl;

    private $occurrencesIndexName;

    private $photosIndexName;

    public function __construct($elasticsearchUrl, $occurrencesIndexName, $photosIndexName)
    {
        $this->elasticsearchUrl = $elasticsearchUrl;
        $this->occurrencesIndexName = $occurrencesIndexName;
        $this->photosIndexName = $photosIndexName;
    }

    /**
     * Returns the total number of hits for given <code>Query</code> and type
     * name of the resource/entity in ES.
     *
     * @param Query $esQuery The Query to get thecount of.
     * @param string $resourceTypeName The name of the resource type 
     *        (occurrence or photo).
     */
    public function count(
        Query $esQuery, string $resourceTypeName): int {
        $queryAsArray = $esQuery->getQuery()->toArray();
        $strQuery = json_encode(["query" => $queryAsArray]);
        $ch = curl_init($this->buildCountUrl($resourceTypeName));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($strQuery))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);
        $resp = json_decode($result);
        //close connection
        curl_close($ch);

        return intVal($resp->count);
    }


    /**
     * Deletes the ES document associated with given id and of given 
     * resource type.
     *
     * @param int $id The ID of the document/entity to be deleted.
     * @param string $resourceTypeName The name of the resource type 
     *        (occurrence or photo).
     */
    public function deleteById(
        int $id, string $resourceTypeName): string {

        $ch = curl_init($this->buildDeleteByIdUrl($resourceTypeName, $id));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute delete using ES API
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);

        return $result;
    }


    /**
     * Deletes the ES documents associated with given ids and of given 
     * resource type.
     *
     * @param array $ids The IDs of the documents/entities to be deleted.
     * @param string $resourceTypeName The name of the resource type 
     *        (occurrence or photo).
     */
    public function deleteByIds(
        array $ids, string $resourceTypeName): array {

        $responses = array();
        foreach ($ids as $id){
            $responses[] = $this->deleteById($id, $resourceTypeName);
        }

        return $responses;
    }

    private function buildCountUrl(string $resourceTypeName): string {
        $url = $this->buildBaseUrl($resourceTypeName);
        $url .= '/_count';

        return $url;
    }
 

    private function buildDeleteByIdUrl(string $resourceTypeName, int $id): string {
        $url = $this->buildBaseUrl($resourceTypeName);
        $url .= '/'.$id;

        return $url;
    }

    private function buildBaseUrl(string $resourceTypeName): string {
        $url = $this->elasticsearchUrl;

        switch ($resourceTypeName) {
            case Occurrence::RESOURCE_NAME:
                $url .= $this->occurrencesIndexName;
                break;
            case Photo::RESOURCE_NAME:
                $url .= $this->photosIndexName;
                break;
            default:
                throw new \LogicException(sprintf('you shoud not land here, resource "%s" not supported', $resourceTypeName));
                break;
        }

        $url .= '/'.$resourceTypeName;

        return $url;
    }

}



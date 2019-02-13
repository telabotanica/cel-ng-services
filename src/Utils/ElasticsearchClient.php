<?php

namespace App\Utils;

use Elastica\Query;

use Symfony\Component\Dotenv\Dotenv;


/**
 * Elasticsearch HTTP API client. Only used to retrieve the total number 
 * of hits for given <code>Query</code> and index name of the resource in 
 * ES. Introduced because of an elastica bug (failing at counting 
 * total number of hits).
 *
 * @internal the conf is in the .env file while it should be retrieved from 
 *           foselastica yaml config... this housld not be duplicated but no
 *           clean way has been found to access foselastica conf
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
class ElasticsearchClient {
    
    /**
     * Returns the toal number of hits for given <code>Query</code> and type
     * name of the resource/entity in ES.
     */
    public static function count(
        Query $esQuery, string $resourceTypeName): int {
        $queryAsArray = $esQuery->getQuery()->toArray();
        $strQuery = json_encode(["query" => $queryAsArray]);
        $ch = curl_init(ElasticsearchClient::buildUrl($resourceTypeName));
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

    public static function buildUrl(string $resourceTypeName): string {
        $url = getenv('ELASTICSEARCH_INDEX_URL');
        $url .= $resourceTypeName;
        $url .= '/_count';

        return $url;
    }
 
}



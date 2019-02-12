<?php

namespace App\TelaBotanica\Eflore\Api;

use App\DBAL\TaxoRepoEnumType;

use Symfony\Component\HttpFoundation\Request;

/**
 * Client service for eflore Web service API. Currently, only offers methods 
 * to retrieve ancestor name for a given taxon.
 *
 * @package App\TelaBotanica\Eflore\Api
 */
class EfloreApiClient {

    // The base URL for eflore Web service API:
    const BASE_URL = 'http://api.tela-botanica.org/service:eflore:0.1/';
    const RESOURCE_NAME = 'taxons';
    const SERVICE_URL_POSTFIX = '/relations/superieurs';
    // The allowed repository names (also called 'projets' but it can be 
    // misleading):
    const ALLOWED_REPO_NAMES = array(
        'bdtfxr', 'aublet', 'florical', 'bdtre', 'commun', 'sophy', 'apd', 
        'sptba', 'nvps', 'bdnt', 'bdtfx', 'bdtxa', 'chorodep', 'coste', 
        'eflore', 'fournier', 'insee-d', 'iso-3166-1', 'iso-639-1', 'nvjfl', 
        'cel', 'lion1906', 'liste-rouge', 'wikipedia', 'osm', 'prometheus', 
        'bibliobota', 'photoflora', 'baseflor', 'baseveg', 'sptb', 'isfan', 
        'nva', 'moissonnage', 'nasa-srtm', 'coord-transfo', 'lbf');
    // Constants for rank names as used in the Web services:
    const RANK_FAMILY = 'Famille';
    const RANK_ORDER  = 'Ordre';

    private function buildGetUpperTaxaHierarchyUrl(
        int $taxonId, string $taxoRepo): string {

        $url = EfloreApiClient::BASE_URL;
        $url .= $taxoRepo;
        $url .= '/';
        $url .= EfloreApiClient::RESOURCE_NAME;
        $url .= '/';
        $url .= $taxonId;
        $url .= EfloreApiClient::SERVICE_URL_POSTFIX;

        return $url;
    }

    /**
     * Returns an array containing the upper taxa hierarchy in the $taxoRepo 
     * repository for the taxon with ID = $taxonId.
     *
     * @param int $taxonId The ID of the taxon to retrieve the ancestor names
     *        for.
     * @param string $taxoRepo The name of the taxonomic repository to retrieve
     *        the taxon ancestor names from.
     *
     * @return array The ancestor descriptions for the taxon with ID = $taxonId
     *         in the $taxoRepo repository. Returns null if it cannot be 
     *         retrieved.
     */
    protected function getUpperTaxaHierarchy(int $taxonId, string $taxoRepo) {


        if ( in_array($taxoRepo, EfloreApiClient::ALLOWED_REPO_NAMES) ) {

            $url = $this->buildGetUpperTaxaHierarchyUrl($taxonId, $taxoRepo);

            try {
                $curl_request = curl_init($url);

                curl_setopt($curl_request, CURLOPT_HEADER, 0);
                curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_request, CURLOPT_TIMEOUT, 5);
                curl_setopt($curl_request, CURLOPT_CONNECTTIMEOUT, 5);
                $result = curl_exec($curl_request); // execute the request
                
                curl_close($curl_request);

                return json_decode($result);
            }
            catch (\Exception $ex) {
                return null;
            }
        }
        return null;

    }

    /**
     * Returns the name of the family for the taxon with ID = $taxonId in the 
     * $taxoRepo repository.  Returns null if it cannot be found.
     *
     * @param int $taxonId The ID of the taxon to retrieve the family name
     *        for.
     * @param string $taxoRepo The name of the taxonomic repository to retrieve
     *        the taxon family name from.
     *
     * @return string the name of the family of the taxon with ID = $taxonId in
     *          the $taxoRepo repository. Returns null if it cannot be found.
     */
    public function getFamilyName(int $taxonId, string $taxoRepo) {

        return $this->getAncestorName(
            $taxonId, $taxoRepo, EfloreApiClient::RANK_FAMILY);
    }

    /**
     * Returns the name of the order for the taxon with ID = $taxonId in the 
     * $taxoRepo repository. Returns null if it cannot be found.
     *
     * @param int $taxonId The ID of the taxon to retrieve the order name
     *        for.
     * @param string $taxoRepo The name of the taxonomic repository to retrieve
     *        the taxon order name from.
     *
     * @return string the name of the order of the taxon with ID = $taxonId in
     *          the $taxoRepo repository. Returns null if it cannot be found.
     */
    public function getOrderName(int $taxonId, string $taxoRepo) {

        return $this->getAncestorName(
            $taxonId, $taxoRepo, EfloreApiClient::RANK_ORDER);
    }

    /**
     * Returns the name of the ancestor with rank named $taxoRank for the 
     * taxon with ID = $taxonId in the $taxoRepo repository. Returns 
     * null if it cannot be found.
     *
     * @param int $taxonId The ID of the taxon to retrieve the ancestor name
     *        for.
     * @param string $taxoRepo The name of the taxonomic repository to retrieve
     *        the taxon ancestor name from.
     * @param string $taxoRank The name of the taxonomic rank to retrieve.
     *
     * @return string Returns the name of the ancestor with rank named 
     *         $taxoRank for the taxon with ID = $taxonId in the $taxoRepo  
     *         repository. Returns null if it cannot be found.
     *
     */
    protected function getAncestorName(
        int $taxonId, string $taxoRepo, string $taxoRank) : ?string {

        if ( null !== $taxoRank ) {    
            $data = $this->getUpperTaxaHierarchy($taxonId, $taxoRepo);

            if ( null !== $data ) {   

                $ancestorArray = (array)$data->$taxonId;

                if ( null !== $ancestorArray ) {
                    foreach ($ancestorArray as $ancestor) {
                        if ( $ancestor->{'rang.libelle'} == $taxoRank) {
                            return $ancestor->nom_sci;
                        }
                    }
                }
            }
        }

        return null;
    }

}




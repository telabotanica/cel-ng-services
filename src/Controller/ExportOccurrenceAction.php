<?php

namespace App\Controller;

use App\Entity\PhotoTag;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

/** 
 * Simple proxy to the export widget API.
 */
// @todo do tags mots_cles + prj, privé, certitude + ids
class ExportOccurrenceAction {
/*
https://api.tela-botanica.org/service:cel:CelWidgetExport/export?courriel_utilisateur=delphine%40tela-botanica.org&pays=FR%2CFX%2CGF%2CPF%2CTF&zone_geo=Montpellier&departement=34&mots_cles=defiPhoto&programme=missions-flore&taxon=viola&annee=2019&mois=08&jour=22&validation_identiplante=1&standard=1&date_debut=19%2F06%2F2019&debut=0&limite=20000&format=csv&colonnes=standardexport,avance,etendu,baseflor,auteur,standard
*/

    // the <code>Security</code> service to retrieve the current user:
    protected $security;
    protected $entityManager;

    // Mapping between CEL2 filter params and the occurrence export Web service
    // ones - only params that justs needs to be directly translated to the 
    // target params. Those that need processing (project, tags)
    private const PARAM_MAPPING = array(
        'freeTextQuery'   => 'recherche',
        'frenchDep' => 'departement',
        "isIdentiplanteValidated" => 'validation_identiplante',
        'locality' => 'commune',
        'osmCountry' => 'pays',
        "dateObservedDay" => 'jour',
        "dateObservedMonth" => 'mois',
        "dateObservedYear" => 'annee',
        'certainty'      => 'certitude',
        'isPublic' => 'transmission',
    );


    private const PARAM_VALUE_MAPPING = array(
        'true'   => 1,
        'false' => 0
    );

    private $paramsAsString;

    /**
     * Returns a new <code>BaseCollectionDataProvider</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param Security $security The injected <code>Security</code> service.<     * @param RepositoryManagerInterface $repositoryManager The injected 
     *        <code>RepositoryManagerInterface</code> service.
     * @param RequestStack $requestStack The injected <code>RequestStack</code>
     *        service.
     *
     * @return BaseCollectionDataProvider Returns a new  
     *         <code>BaseCollectionDataProvider</code> instance initialized 
     *         with (injected) services passed as parameters.
     */
    public function __construct(
        Security $security, 
        EntityManagerInterface $entityManager) {

        $this->security = $security;
        $this->entityManager = $entityManager;
    }


    public function __invoke(Request $request) {        

        set_time_limit(0);
        $url = $this->buildUrl($request);

        try {
            $fp = fopen($url, 'rb');
            header("Content-Disposition:attachment;filename=cel_export.csv");
            header("Content-Type:text/csv; charset=UTF-8");
            header("Cache-Control:no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Keep-Alive:timeout=30");
            header("Pragma:no-cache");
            fpassthru($fp);
        } catch (\Throwable $t) {
            $jsonResp = array('errorMessage' => $t.getMessage());
            return new Response(json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, []);
        }   
        set_time_limit(30);
        exit;
    }

    private function buildParamString($params) {
        $this->paramsAsString = '';
        $paramNames = array_keys($params);
        foreach($paramNames as $paramName) {

            
            if ( array_key_exists($paramName, ExportOccurrenceAction::PARAM_MAPPING) ) {  
                $wsParamName = ExportOccurrenceAction::PARAM_MAPPING[$paramName];                
                $this->addParamToTargetUrl($wsParamName, $params[$paramName]);
            }
            if ( $paramName == "projectId" ) {
                $this->processProject($params['projectId']);
            }
            if ( $paramName == "ids" ) {
                $this->processIds($params['ids']);
            }
            if ( $paramName == "tags" ) {
                $this->processTags($params['tags']);
            }
        }
        $this->paramsAsString = substr($this->paramsAsString, 1);

        return $this->paramsAsString; 
    }

    private function processTags($tags) {
        $wsTags = "";
        foreach($tags as $tag) {
            $wsTags = $wsTags . $tag . "ET"; 
        }
        $wsTags =  substr(trim($wsTags), 0, -2);
        $this->addParamToTargetUrl("mots-cles", $wsTags);
    }

   private function processProject($projectId) {
        $prj = $this->entityManager->getRepository('App:TelaBotanicaProject')->find($projectId);
        $this->addParamToTargetUrl("programme", $prj->getLabel());
    }

   private function processIds($ids) {
        foreach($ids as $id) {
            $wsIds = $wsIds . $id . ","; 
        }
        $wsIds =  substr(trim($wsIds), 0, -1);
        $this->addParamToTargetUrl("obsids", $wsIds);
    }

   private function addAccessControlParameter() {
        $user =  $this->security->getToken()->getUser();

        if (!$user->isTelaBotanicaAdmin()) {
            // Project admins: limit to occurrence belonging to the project
            if ($user->isProjectAdmin()) {
                $this->addParamToTargetUrl("prj", $user->getAdministeredProjectId());
            }
            // Simple users: limit to her/his occurrences
            else if (!is_null($user)){
                $this->addParamToTargetUrl("courriel_utilisateur", $user->getEmail());
            }
            // Not even logged in user: limit to only public occurrences
            else {
                $this->addParamToTargetUrl("transmission", 1);
            }
        }
        // else, Tela-botanica admin: no restrictions!

    }

    protected function addParamToTargetUrl($name, $value) {
        $this->paramsAsString = $this->paramsAsString . '&' . $name . '=' . $this->translateParamValue($value);
    }


    protected function translateParamValue($value) {
        if ( array_key_exists($value, ExportOccurrenceAction::PARAM_VALUE_MAPPING) ) {   
            return ExportOccurrenceAction::PARAM_VALUE_MAPPING[$value];
        }
        else {
            return $value;
        }
    }

    private function buildUrl($request) {
        $params = $request->query->all();
        $this->buildParamString($params);
        $this->addAccessControlParameter();
        $this->paramsAsString = $this->paramsAsString . "&debut=0&limite=20000&format=csv&colonnes=standardexport,standard";

        return getenv('EXPORT_SERVICE_URL') . '?' . $this->paramsAsString; 
    }

}

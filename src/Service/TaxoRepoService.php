<?php

namespace App\Service;

use App\DBAL\TaxoRepoEnumType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;

class TaxoRepoService
{
    // Référentiels tela botanica indexés par référentiels PN (version novembre 2021)
    // à revoir en fonction des nouvelles mises en prod de nouveaux référentiels coté Tela et nouveaux projets PN :
    // https://my-api.plantnet.org/v2/projects
    private const PLANTNET_PROJECTS_BY_TELABOTANICA_TAXO_REPOS = [
        'afn' => ['isfan'], // North Africa
        'aft' => ['apd'], // Tropical Africa
        'alpes-maritimes' => ['taxref', 'bdtfx'], // Flore remarquable des Alpes-Maritimes
        'antilles' => ['taxref'], // Caribbean
        'canada' => ['vascan'], // Plants of Canada
        'central-america' => [], // Plants of Costa Rica
        'cevennes' => ['taxref', 'bdtfx'], // Flora of the Cévennes National Park
        'comores' => ['apd'], // Comoro Islands
        'endemia' => ['taxref'], // New Caledonia
        'gbb-cf' => [], // Singapore : Gardens by the Bay - Cloud Forest
        'gbb-fd' => [], // Singapore : Gardens by the Bay - Flower Dome
        'guyane' => ['aublet'], // Amazonia
        'hawai' => [], // Plants of Hawaii
        'invasion' => ['taxref', 'bdtfx'], // Invasive plants
        'iscantree' => ['apd'], // Trees of South Africa
        'k-world-flora' => ['taxref'], // World flora
        'k-eastern-europe' => ['taxref'], // Eastern Europe
        'k-middle-europe' => ['taxref'], // Middle Europe
        'k-southwestern-europe' => ['taxref'], // Southwestern Europe
        'k-southeastern-europe' => ['taxref'], // Southestern Europe
        'k-northern-europe' => ['taxref'], // Northern Europe
        'k-southern-africa' => ['apd'], // Southern Africa
        'k-northeast-tropical-africa' => ['apd'], // Tropical Africa
        'k-west-central-tropical-africa' => ['apd'], // Tropical Africa
        'k-east-tropical-africa' => ['apd'], // Tropical Africa
        'k-west-tropical-africa' => ['apd'], // Tropical Africa
        'k-south-tropical-africa' => ['apd'], // Tropical Africa
        'k-middle-atlantic-ocean' => [], // Atlantic Ocean
        'k-macaronesia' => [], // Macaronesia
        'k-western-indian-ocean' => [], // Indian Ocean
        'k-northern-africa' => ['isfan'], // North Africa
        'k-caribbean' => ['taxref'], // caribbean
        'k-caucasus' => [],
        'k-russian-far-east' => [],
        'k-siberia' => [],
        'k-western-asia' => [],
        'k-middle-asia' => [],
        'k-arabian-peninsula' => [],
        'k-eastern-asia' => [],
        'k-china' => [],
        'k-mongolia' => [],
        'k-malesia' => [],
        'k-indo-china' => [],
        'k-indian-subcontinent' => [],
        'k-papuasia' => [],
        'k-australia' => [],
        'k-new-zealand' => [],
        'k-north-central-pacific' => [],
        'k-southwestern-pacific' => [],
        'k-south-central-pacific' => [],
        'k-northwestern-pacific' => [],
        'k-northwestern-u-s-a' => [],
        'k-mexico' => [],
        'k-southwestern-u-s-a' => [],
        'k-south-central-u-s-a' => [],
        'k-subarctic-america' => [],
        'k-northeastern-u-s-a' => [],
        'k-southeastern-u-s-a' => [],
        'k-north-central-u-s-a' => [],
        'k-eastern-canada' => ['vascan'],
        'k-western-canada' => ['vascan'],
        'k-northern-south-america' => [],
        'k-western-south-america' => [],
        'k-brazil' => [],
        'k-southern-south-america' => [],
        'k-central-america' => [],
        'k-antarctic-continent' => [],
        'k-subantarctic-islands' => [],
        'esalq' => [], // Trees and shrubs of ESALQ park and surrounding areas
        'eu-crops' => [], // Planted and cultivated crops
        'clcpro' => [], // Small flora of the Desert Locust biotopes in West Africa
        'japan' => [], // Plants of Japan
        'lapaz' => ['taxref'], // Tropical Andes
        'lewa' => [], // LEWA in KENYA
        'malaysia' => [], // Plants of Malaysia
        'martinique' => ['taxref'], // Martinique
        'maurice' => ['apd'], // Plants of Mauritius Island
        'medor' => ['lbf'], // Eastern Mediterranean
        'monver' => [], // Mediterranean ornamental trees
        'namerica' => [], // USA
        'ordesa' => ['taxref', 'bdtfx'], // Espagne - Ordesa National Park
        'polynesiefr' => ['taxref'], // French Polynesia
        'prosea' => [], // Plant Resources of South East Asia
        'prota' => ['isfan', 'apd'], // Useful plants of Tropical Africa
        'provence' => ['taxref', 'bdtfx'], // Provence, France
        'reunion' => ['taxref'], // Plants of Réunion Island
        'salad' => ['taxref', 'bdtfx'], // Les Ecologistes de l'Euzière
        'the-plant-list' => ['taxref', 'bdtfx'], // World flora
        'useful' => [], // Cultivated and ornamental plants
        'weeds' => ['taxref', 'bdtfx'], // Weeds in agricultural fields of Europe
        'weurope' => ['taxref'], // Western Europe
    ];

    private const TELABOTANICA_TAXO_REPOS = [
        'apd',
        'aublet',
        'bdtfx',
        'bdtfxr',
        'bdtxa',
        'bdtre',
        'florical',
        'isfan',
        'lbf',
        'taxref',
        'taxreflich',
    ];

    private $client;
    private $baseNamesearchUrl;
    private $taxonInfoUrl;
	
	private $taxonRechercheNomUrl;

	private $taxrefLiens;
    public function __construct(string $baseNamesearchUrl, string $taxonInfoUrl, bool $useNativeHttpClient, string
	$taxonRechercheNomUrl, string $taxrefLiens)
    {
        if ($useNativeHttpClient) {
            $this->client = new NativeHttpClient();
        } else {
            $this->client = HttpClient::create();
        }
        $this->baseNamesearchUrl = $baseNamesearchUrl;
        $this->taxonInfoUrl = $taxonInfoUrl;
		$this->taxonRechercheNomUrl = $taxonRechercheNomUrl;
		$this->taxrefLiens = $taxrefLiens;
    }
	
	// On récupère l'ipni id en fonction du powo Id
	public function getTaxonInfoFromPowo(string $powoId)
	{
		$taxonNameId = null;
		$cheminFichier = $this->taxrefLiens.'TAXREF_LIENS.csv';

		// Ouvrir le fichier en mode lecture
		$fichier = fopen($cheminFichier, 'r');

		if ($fichier) {
			// Lire la première ligne (entête)
			$entete = fgetcsv($fichier);

			// Trouver l'index des colonnes F et G
			$ipniIndex = array_search('F', $entete);
			$powoIndex = array_search('G', $entete);

			// Parcourir les lignes du fichier
			while (($ligne = fgetcsv($fichier)) !== false) {
				// Vérifier si la valeur de la colonne G correspond à $powoId
				if ($ligne[$powoIndex] == $powoId) {
					// Récupérer l'ipni id correspondant au powoId et sortir de la boucle
					$taxonNameId = $ligne[$ipniIndex];
					break;
				}
			}
			fclose($fichier);
		} else {
			echo "Erreur lors de l'ouverture du fichier TAXREF_LIENS.";
		}

		return $taxonNameId;
	}

    /**
     * @return array{taxoRepo: string, sciNameId: ?int, sciName: ?string, acceptedSciNameId: ?int, acceptedSciName: ?string, family: ?string}
     */
    public function getTaxonInfo(string $taxonNameId, string $project): array
    {
        $info = [
            'taxoRepo' => TaxoRepoEnumType::OTHERUNKNOWN,
            'sciNameId' => null,
            'sciName' => null,
            'acceptedSciNameId' => null,
            'acceptedSciName' => null,
            'family' => null,
        ];

        $taxoRepo = in_array($project, self::TELABOTANICA_TAXO_REPOS)
            ? $project
            : (self::PLANTNET_PROJECTS_BY_TELABOTANICA_TAXO_REPOS[$project][0] ?? null);
		
        if (!$taxoRepo) {
            return $info;
        }
		
        // eg. https://api.tela-botanica.org/service:eflore:0.1/taxref/taxons/125328
        $response = $this->client->request('GET', $this->taxonInfoUrl.'/'.$taxoRepo.'/taxons/'.$taxonNameId);
		
		if (200 !== $response->getStatusCode()) {
			// Si on ne reçoit pas d'info avec le taxon Id / referentiel on recherche par nom
			$recherche = ['recherche' => $taxonNameId, 'referentiel' => $taxoRepo];
			$infos = $this->consulterRechercheNomsSciEflore($recherche);
			if (isset($infos['resultat'])){
				foreach ($infos['resultat'] as $taxonInfo){
					if ($this->startsWith($taxonInfo['nom_sci_complet'], $taxonNameId) || $taxonInfo['nom_sci'] ===
						$taxonNameId){
						$taxonId = $taxonInfo['id'];
						$response = $this->client->request('GET', $this->taxonInfoUrl.'/'.$taxoRepo.'/taxons/'.$taxonId);
						break;
					}
				}
			}
		}
		
        $response = json_decode($response->getContent(), true) ?? [];
        if (!$response) {
            return $info;
        }

        $info['taxoRepo'] = $taxoRepo;
        $info['sciNameId'] = $response['id'] ?? null;
        $info['sciName'] = $response['nom_sci_complet'];
        $info['acceptedSciNameId'] = $response['nom_retenu.id'] ?? null;
        $info['acceptedSciName'] = $response['nom_retenu_complet'] ?? '';
        $info['family'] = $response['famille'] ?? '';

        return $info;
    }
	
	public function consulterRechercheNomsSciEflore($recherche) {
		$url = $this->taxonInfoUrl.'/%s/noms?recherche=%s&masque=%s&retour.champs=id,nom_sci,auteur,nom_retenu.id,famille,num_taxonomique,nom_retenu_complet';
		$urlRecherche = sprintf($url, strtolower($recherche['referentiel']), 'floue', urlencode
($recherche['recherche'].'%'));
		// Quand il n'y pas de résultats eflore renvoie une erreur 404 (l'imbécile !)
		// or le cas où l'on n'a pas de résultats est parfaitement valide
		$infos = @file_get_contents($urlRecherche);
		$infos = json_decode($infos, true);
		return $infos;
	}

	public function consulterRechercheNomsVernaEflore($recherche) {
		$url = $this->taxonInfoUrl."/%s/noms-vernaculaires?masque=%s&recherche=etendue&retour.champs=num_taxon&masque.lg=fra";
		$url_verna = sprintf($url, strtolower($recherche['referentiel']), urlencode($recherche['recherche']));
		
		// Quand il n'y pas de résultats eflore renvoie une erreur 404 (l'imbécile !)
		// or le cas où l'on n'a pas de résultats est parfaitement valide
		$infos_verna = @file_get_contents($url_verna);
		$infos_verna = json_decode($infos_verna, true);
		
		return $infos_verna;
	}
	
	function startsWith ($string, $startString)
	{
		$len = strlen($startString);
		return (substr($string, 0, $len) === $startString);
	}

}

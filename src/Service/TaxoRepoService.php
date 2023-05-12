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
        'alpes-maritimes' => ['bdtfx'], // Flore remarquable des Alpes-Maritimes
        'antilles' => ['taxref'], // Caribbean
        'canada' => ['vascan'], // Plants of Canada
        'central-america' => [], // Plants of Costa Rica
        'cevennes' => ['bdtfx'], // Flora of the Cévennes National Park
        'comores' => ['apd'], // Comoro Islands
        'endemia' => ['taxref'], // New Caledonia
        'gbb-cf' => [], // Singapore : Gardens by the Bay - Cloud Forest
        'gbb-fd' => [], // Singapore : Gardens by the Bay - Flower Dome
        'guyane' => ['aublet'], // Amazonia
        'hawai' => [], // Plants of Hawaii
        'invasion' => ['bdtfx'], // Invasive plants
        'iscantree' => ['apd'], // Trees of South Africa
        'japan' => [], // Plants of Japan
        'lapaz' => ['taxref'], // Tropical Andes
        'lewa' => [], // LEWA in KENYA
        'malaysia' => [], // Plants of Malaysia
        'martinique' => ['taxref'], // Martinique
        'maurice' => ['apd'], // Plants of Mauritius Island
        'medor' => ['lbf'], // Eastern Mediterranean
        'monver' => [], // Mediterranean ornamental trees
        'namerica' => [], // USA
        'ordesa' => ['bdtfx'], // Espagne - Ordesa National Park
        'polynesiefr' => ['taxref'], // French Polynesia
        'prosea' => [], // Plant Resources of South East Asia
        'prota' => ['isfan', 'apd'], // Useful plants of Tropical Africa
        'provence' => ['bdtfx'], // Provence, France
        'reunion' => ['taxref'], // Plants of Réunion Island
        'salad' => ['bdtfx'], // Les Ecologistes de l'Euzière
        'the-plant-list' => ['bdtfx'], // World flora
        'useful' => [], // Cultivated and ornamental plants
        'weeds' => ['bdtfx'], // Weeds in agricultural fields of Europe
        'weurope' => ['bdtfx'], // Western Europe
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

    public function __construct(string $baseNamesearchUrl, string $taxonInfoUrl, bool $useNativeHttpClient, string $taxonRechercheNomUrl)
    {
        if ($useNativeHttpClient) {
            $this->client = new NativeHttpClient();
        } else {
            $this->client = HttpClient::create();
        }
        $this->baseNamesearchUrl = $baseNamesearchUrl;
        $this->taxonInfoUrl = $taxonInfoUrl;
		$this->taxonRechercheNomUrl = $taxonRechercheNomUrl;
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

<?php

namespace App\Repository;

use App\Model\ExportTotal;
use Doctrine\DBAL\Connection;

class ExportTotalRepository
{
    private $connection;
    private $parametres;

    public function __construct(Connection $connection, Array $parametres = [])
    {
        $this->connection = $connection;
        $this->parametres = $parametres;
    }

    public function findAll($parametres = []): array
    {
        $this->setParametres($parametres);

        $limite = isset($this->parametres['navigation.limite']) ? intval($this->parametres['navigation.limite']) : 10;
        $depart = isset($this->parametres['navigation.depart']) ? intval($this->parametres['navigation.depart']) : 0;
        $ordre = $this->parametres['ordre'];

        $date_debut = date('Y-m-d H:i:s', $this->parametres['date.debut']);
        $date_fin = date('Y-m-d H:i:s', $this->parametres['date.fin']);
        $date_debut = "'{$date_debut}'";
        $date_fin = "'{$date_fin}'";

        $query = 'SELECT * FROM cel_export_total AS ce LEFT JOIN cel_images_export AS i ON ce.id_observation = i.ce_observation WHERE ce.date_modification >= '.$date_debut.' AND ce.date_modification <= '.$date_fin.' AND ce.images IS NOT NULL AND ce.transmission = "1"';

        $queryTotal = 'SELECT COUNT(*) FROM cel_export_total 
         WHERE date_modification >= '.$date_debut.' 
         AND date_modification <= '.$date_fin.' 
         AND images IS NOT NULL
         AND transmission = "1"';

        if (isset($this->parametres['masque.referentiel']) && $this->parametres['masque.referentiel'] != '') {
            $query .= ' AND nom_referentiel = "' . $this->parametres['masque.referentiel'] .'"';
            $queryTotal .= ' AND nom_referentiel = "' . $this->parametres['masque.referentiel'] .'"';
        }

        if (isset($this->parametres['masque.nom_ret']) && $this->parametres['masque.nom_ret'] != '') {
            $query .= ' AND nom_ret LIKE "%' . $this->parametres['masque.nom_ret'] .'%"';
            $queryTotal .= ' AND nom_ret LIKE "%' . $this->parametres['masque.nom_ret'] .'%"';
        }

        if (isset($this->parametres['masque.nom_ret_nn']) && $this->parametres['masque.nom_ret_nn'] != '') {
            $query .= ' AND nom_ret_nn = "' . $this->parametres['masque.nom_ret_nn'] .'"';
            $queryTotal .= ' AND nom_ret_nn = "' . $this->parametres['masque.nom_ret_nn'] .'"';
        }

        if (isset($this->parametres['masque.famille']) && $this->parametres['masque.famille'] != '') {
            $query .= ' AND famille LIKE "%' . $this->parametres['masque.famille'] .'%"';
            $queryTotal .= ' AND famille LIKE "%' . $this->parametres['masque.famille'] .'%"';
        }

        if (isset($this->parametres['masque.projet']) && $this->parametres['masque.projet'] != '') {
            $query .= ' AND programme LIKE "%' . $this->parametres['masque.projet'] .'%"';
            $queryTotal .= ' AND programme LIKE "%' . $this->parametres['masque.projet'] .'%"';
        }

        if (isset($this->parametres['masque.mots_cles']) && $this->parametres['masque.mots_cles'] != '') {
            $query .= ' AND ce.mots_cles_texte LIKE "%' . $this->parametres['masque.mots_cles'] .'%"';
            $queryTotal .= ' AND mots_cles_texte LIKE "%' . $this->parametres['masque.mots_cles'] .'%"';
        }

        if (isset($this->parametres['masque.cp']) && $this->parametres['masque.cp'] != '') {
            $query .= ' AND ce_zone_geo = "' . $this->parametres['masque.cp'] .'"';
            $queryTotal .= ' AND ce_zone_geo = "' . $this->parametres['masque.cp'] .'"';
        }

        if (isset($this->parametres['masque']) && $this->parametres['masque'] != '') {
            $query .= ' AND ( nom_ret LIKE "%' . $this->parametres['masque'] .'%"
            OR famille LIKE "%' . $this->parametres['masque'] .'%"
            OR courriel_utilisateur LIKE "%' . $this->parametres['masque'] .'%"
            OR pseudo_utilisateur LIKE "%' . $this->parametres['masque'] .'%")
            ';
            $queryTotal .= ' AND ( nom_ret LIKE "%' . $this->parametres['masque'] .'%"
            OR famille LIKE "%' . $this->parametres['masque'] .'%"
            OR courriel_utilisateur LIKE "%' . $this->parametres['masque'] .'%"
            OR pseudo_utilisateur LIKE "%' . $this->parametres['masque'] .'%")
            ';
        }

        $query .=  ' GROUP BY ce.id_observation ORDER BY ce.date_modification ' . $ordre . ' 
         LIMIT '.$depart.','.$limite .' -- '.
            __FILE__.':'.__LINE__;

        $resultats = $this->connection->fetchAllAssociative($query);
        $total = $this->connection->fetchOne($queryTotal);

        foreach ($resultats as &$resultat) {
            $resultat = $this->transformImagesToArray($resultat);
            unset($resultat);
        }

        $exportTotals = [];
        foreach ($resultats as $row) {
            $exportTotals[] = $this->mapRowToExportTotal($row);
        }

        return ['total' => $total, 'resultats' => $exportTotals];
    }

    public function find(int $id): ?ExportTotal
    {
        $query = 'SELECT * FROM cel_export_total AS ce LEFT JOIN cel_images_export AS i ON ce.id_observation = i.ce_observation WHERE id_observation = ' . $id;
        $result = $this->connection->fetchAssociative($query, ['id_observation' => $id]);

        $result = $this->transformImagesToArray($result);
        $result = $this->mapRowToExportTotal($result);

        return $result ? $result : null;
    }
/*
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        // Construction de la requête SQL avec les critères dynamiques
        $sql = 'SELECT * FROM cel_export_total WHERE 1 = 1';
        $params = [];

        // Ajout des critères dans la requête
        foreach ($criteria as $field => $value) {
            $sql .= " AND {$field} = :{$field}";
            $params[$field] = $value;
        }

        // Ajouter l'ordre, la limite et le décalage
        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $sql .= " ORDER BY {$field} {$direction}";
            }
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        if ($offset) {
            $sql .= " OFFSET {$offset}";
        }

        // Exécuter la requête SQL et retourner les résultats
        $result = $this->connection->fetchAllAssociative($sql, $params);

        return result;

//        return array_map(function ($row) {
//            return new ExportTotal(
//                $row['id_observation'],
//                $row['guid']
//            );
//        }, $result);
    }
*/
    /**
     * Mapper une ligne de résultat de la base de données vers un objet ExportTotal
     *
     * @param array $row
     * @return ExportTotal
     */
    private function mapRowToExportTotal(array $row): ExportTotal
    {
        return new ExportTotal(
            $row['id_observation'] ?? 0,
            $row['guid'] ?? '',
            $row['donnees_standard'] ?? false,
            $row['transmission'] ?? false,
            $row['id_plantnet'] ?? null,
            $row['ce_utilisateur'] ?? null,
            $row['pseudo_utilisateur'] ?? null,
            $row['courriel_utilisateur'] ?? null,
            $row['nom_sel'] ?? null,
            $row['nom_sel_nn'] ?? null,
            $row['nom_ret'] ?? null,
            $row['nom_ret_nn'] ?? null,
            $row['famille'] ?? null,
            $row['nom_referentiel'] ?? null,
            $row['pays'] ?? null,
            $row['ce_zone_geo'] ?? null,
            $row['dept'] ?? null,
            $row['zone_geo'] ?? null,
            $row['lieudit'] ?? null,
            $row['station'] ?? null,
            $row['milieu'] ?? null,
            $row['latitude'] ?? null,
            $row['longitude'] ?? null,
            $row['altitude'] ?? null,
            $row['geodatum'] ?? null,
            $row['geometry'] ?? null,
            $row['lat_prive'] ?? null,
            $row['long_prive'] ?? null,
            $row['localisation_precision'] ?? null,
            $row['localisation_floutage'] ?? null,
            $row['localisation_coherence'] ?? null,
            new \DateTime($row['date_observation'] ?? null),
            $row['programme'] ?? null,
            $row['mots_cles_texte'] ?? null,
            $row['commentaire'] ?? null,
            new \DateTime($row['date_creation'] ?? null),
            new \DateTime($row['date_modification'] ?? null),
            new \DateTime($row['date_transmission'] ?? null),
            $row['abondance'] ?? null,
            $row['certitude'] ?? null,
            $row['phenologie'] ?? null,
            $row['spontaneite'] ?? null,
            $row['observateur'] ?? null,
            $row['observateur_structure'] ?? null,
            $row['type_donnees'] ?? null,
            $row['biblio'] ?? null,
            $row['source'] ?? null,
            $row['herbier'] ?? null,
            $row['determinateur'] ?? null,
            $row['url_identiplante'] ?? null,
            $row['validation_identiplante'] ?? null,
            new \DateTime($row['date_validation'] ?? null),
            $row['score_identiplante'] ?? null,
            $row['images'] ?? '[]',
            $row['cd_nom'] ?? null,
            $row['grade'] ?? null
        );;
    }

    private function convertToDate(?string $dateString): ?\DateTime
    {
        return $dateString ? new \DateTime($dateString) : null;
    }

    private function setParametres($parametres = []){
        $this->parametres = $parametres;

        if (!isset($parametres['date.debut'])) {
            $this->parametres['date.debut'] = '0';
        }

        if (!isset($parametres['date.fin'])) {
            $this->parametres['date.fin'] = time();
        }

        if (! isset($parametres['ordre'])) {
            $this->parametres['ordre'] = 'desc';
        } else {
            $parametres['ordre'] = strtolower($parametres['ordre']);
            if (! in_array($parametres['ordre'], array('asc', 'desc'))) {
                $this->parametres['ordre'] = 'desc';
            }
        }
    }

    private function transformImagesToArray(array $resultat): array {
        if (isset($resultat['images'])) {
            $images = [];
            $idimg = explode(',', $resultat['images']);
            foreach ($idimg as $img) {
                if ($img !== '') {
                    $images[] = [
                        'url' => $img,
                        'id_image' => $resultat['id_image'],
                        'nom_original' => $resultat['nom_original'],
                        'date_prise_de_vue' => $resultat['date_prise_de_vue'],
                    ];
                }
            }
            $resultat['images'] = $images;
        }

        return $resultat;
    }

//    public function findVerifiedObservations(int $page,int $limit=12): array
//    {
//        $limit = abs($limit);
//        $connex = new PDOConnection();
//        $offset = ($page * $limit)-$limit;
//        $query = "SELECT * FROM del_observation o WHERE (certitude = 'certain' AND score_max >= 4) OR score_max >= 4 ORDER BY id_observation DESC LIMIT $limit OFFSET $offset";
//
//        $stmt = $connex->prepare($query);
//        $stmt->execute();
//        $stmt->setFetchMode(PDO::FETCH_OBJ);
//        $res1 = $stmt->fetchAll();
//        $obs_tab=[];
//        foreach ($res1 as $obs_bdd){
//            $obs = $this->findOneById($obs_bdd->id_observation);
//
//            array_push($obs_tab,$obs);
//        }
//        return $obs_tab;
//    }
}

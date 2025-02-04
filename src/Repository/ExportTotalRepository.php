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

        $query = 'SELECT * FROM cel_export_total 
         WHERE date_modification >= '.$date_debut.' 
         AND date_modification <= '.$date_fin.' 
         AND images IS NOT NULL
         AND transmission = "1"';

        if (isset($this->parametres['masque.referentiel']) && $this->parametres['masque.referentiel'] != '') {
            $query .= ' AND nom_referentiel = "' . $this->parametres['masque.referentiel'] .'"';
        }

        if (isset($this->parametres['masque.nom_ret']) && $this->parametres['masque.nom_ret'] != '') {
            $query .= ' AND nom_ret LIKE "%' . $this->parametres['masque.nom_ret'] .'%"';
        }

        if (isset($this->parametres['masque.nom_ret_nn']) && $this->parametres['masque.nom_ret_nn'] != '') {
            $query .= ' AND nom_ret_nn = "' . $this->parametres['masque.nom_ret_nn'] .'"';
        }

        if (isset($this->parametres['masque.famille']) && $this->parametres['masque.famille'] != '') {
            $query .= ' AND famille LIKE "%' . $this->parametres['masque.famille'] .'%"';
        }

        if (isset($this->parametres['masque.projet']) && $this->parametres['masque.projet'] != '') {
            $query .= ' AND programme LIKE "%' . $this->parametres['masque.projet'] .'%"';
        }

        if (isset($this->parametres['masque.mots_cles']) && $this->parametres['masque.mots_cles'] != '') {
            $query .= ' AND mots_cles_texte LIKE "%' . $this->parametres['masque.mots_cles'] .'%"';
        }

        if (isset($this->parametres['masque.cp']) && $this->parametres['masque.cp'] != '') {
            $query .= ' AND ce_zone_geo = "' . $this->parametres['masque.cp'] .'"';
        }

        if (isset($this->parametres['masque']) && $this->parametres['masque'] != '') {
            $query .= ' AND ( nom_ret LIKE "%' . $this->parametres['masque'] .'%"
            OR famille LIKE "%' . $this->parametres['masque'] .'%"
            OR courriel_utilisateur LIKE "%' . $this->parametres['masque'] .'%"
            OR pseudo_utilisateur LIKE "%' . $this->parametres['masque'] .'%")
            ';
        }

        $query .=  ' ORDER BY date_modification ' . $ordre . ' 
         LIMIT '.$depart.','.$limite .' -- '.
            __FILE__.':'.__LINE__;

        //TODO: mapper infos et resultats dans un tableau
        $stmt = $this->connection->executeQuery($query);

        return $stmt->fetchAllAssociative();
    }

    public function find(int $id): ?array
    {
        $query = 'SELECT * FROM cel_export_total WHERE id_observation = :id';
        $result = $this->connection->fetchAssociative($query, ['id' => $id]);

        return $result ? $result : null;
    }

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

    /**
     * Mapper une ligne de résultat de la base de données vers un objet ExportTotal
     *
     * @param array $row
     * @return ExportTotal
     */
    private function mapRowToExportTotal(array $row): ExportTotal
    {
        return new ExportTotal(
            $row['id_observation'],
            $row['guid'],
            $row['donnees_standard'],
            $row['transmission'],
            $row['id_plantnet'],
            $row['ce_utilisateur'],
            $row['pseudo_utilisateur'],
            $row['courriel_utilisateur'],
            $row['nom_sel'],
            $row['nom_sel_nn'],
            $row['nom_ret'],
            $row['nom_ret_nn'],
            $row['famille'],
            $row['nom_referentiel'],
            $row['pays'],
            $row['ce_zone_geo'],
            $row['dept'],
            $row['zone_geo'],
            $row['lieudit'],
            $row['station'],
            $row['milieu'],
            $row['latitude'],
            $row['longitude'],
            $row['altitude'],
            $row['geodatum'],
            $row['geometry'],
            $row['lat_prive'],
            $row['long_prive'],
            $row['localisation_precision'],
            $row['localisation_floutage'],
            $row['localisation_coherence'],
            $this->convertToDate($row['date_observation']),
            $row['programme'],
            $row['mots_cles_texte'],
            $row['commentaire'],
            $this->convertToDate($row['date_creation']),
            $this->convertToDate($row['date_modification']),
            $this->convertToDate($row['date_transmission']),
            $row['abondance'],
            $row['certitude'],
            $row['phenologie'],
            $row['spontaneite'],
            $row['observateur'],
            $row['observateur_structure'],
            $row['type_donnees'],
            $row['biblio'],
            $row['source'],
            $row['herbier'],
            $row['determinateur'],
            $row['url_identiplante'],
            $row['validation_identiplante'],
            $row['date_validation'],
            $row['score_identiplante'],
            $row['images'],
            $row['cd_nom'],
            $row['grade']
        );
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

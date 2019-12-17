<?php

namespace App\Security\Elastica;

use App\Security\User\TelaBotanicaUser;
use App\Security\User\UnloggedAccessException;

use Elastica\Query\Match;

/*
 * Builds the elastica access control <code>Match</code> query for a given 
 * user depending on her/his access level.
 *
 * @package App\Security\Elastica
 */
class AccessControlQueryBuilder {

    /**
     * Returns the elastica access control <code>Match</code> query for a given user
     * based on her/his access level.
     *
     * @param TelaBotanicaUser $user The user to generate the access controle
     *        query for.
     * @return the elastica access control <code>Match</code> query for
     *          given <code>TelaBotanicaUser</code> based on her/his access level.
     */ 
    public function build(TelaBotanicaUser $user): Match {

        $acQuery = null;
 
        if (!$user->isTelaBotanicaAdmin()) {
            // Project admins: limit to occurrence belonging to the project
            if ($user->isProjectAdmin()) {
                $acQuery = new Match();
                $acQuery->setField("projectId", $user->getAdministeredProjectId());
            }
            // Simple users: limit to her/his occurrences
            else if (!is_null($user)){

                $acQuery = new Match();
                $acQuery->setField("userId", $user->getId());
            }
            // Not even logged in user: limit to public occurrences. 
            // This should never happen in CEL2 as the API is registered only)
            else {
                $acQuery = new Match();
                $acQuery->setField("isPublic", true);
            }
        }
        // Tela-botanica admin: no other filter added...public function build(?TelaBotanicaUser $user, QueryInterface $occSearch) : Query

        return $acQuery;
    }

}




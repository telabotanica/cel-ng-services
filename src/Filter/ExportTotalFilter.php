<?php

namespace App\Filter;

use ApiPlatform\Core\Api\FilterInterface;

class ExportTotalFilter implements FilterInterface
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'navigation.depart' => [
                'property' => 'navigation.depart',
                'type' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'offset',
                    'name' => 'navigation.depart',
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
            'navigation.limite' => [
                'property' => '',
                'type' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'Nombre maximum de résultat',
                    'name' => 'navigation.limite',
                    'type' => 'integer',
                    'default' => 10,
                ],
            ],
            'ordre' => [
                'property' => 'ordre',
                'type' => 'select',
                'required' => false,
                'swagger' => [
                    'description' => 'ordre ascendant ou descendant (desc par défaut)',
                    'name' => 'ordre',
                    'type' => 'select',
                    'enum' => ['asc', 'desc'],
                ],
            ],
            'date.debut' => [
                'property' => 'date.debut',
                'type' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'Date à partir de laquelle effectuer la recherche (unixtime en secondes)',
                    'name' => 'date.debut',
                    'type' => 'integer',
                ],
            ],
            'date.fin' => [
                'property' => 'date.fin',
                'type' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'Date jusqu\'à laquelle effectuer la recherche (unixtime en secondes)',
                    'name' => 'date.fin',
                    'type' => 'integer',
                ],
            ],
            'masque' => [
                'property' => 'masque',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par nom retenu, famille, email ou pseudo',
                    'name' => 'masque',
                    'type' => 'string',
                ],
            ],
            'masque.cp' => [
                'property' => 'masque.cp',
                'type' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par code postal',
                    'name' => 'masque.cp',
                    'type' => 'integer',
                ],
            ],
            'masque.projet' => [
                'property' => 'masque.projet',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par projet',
                    'name' => 'masque.projet',
                    'type' => 'string',
                ],
            ],
            'masque.mots_clefs' => [
                'property' => 'masque.mots_clefs',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par mots_clefs',
                    'name' => 'masque.mots_cles',
                    'type' => 'string',
                ],
            ],
            'masque.referentiel' => [
                'property' => 'masque.referentiel',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par réferentiel',
                    'name' => 'masque.referentiel',
                    'type' => 'string',
                ],
            ],
            'masque.nom_ret' => [
                'property' => 'masque.nom_ret',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par nom retenue',
                    'name' => 'masque.nom_ret',
                    'type' => 'string',
                ],
            ],
            'masque.nom_ret_nn' => [
                'property' => 'masque.nom_ret_nn',
                'type' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par nom_ret_nn',
                    'name' => 'masque.nom_ret_nn',
                    'type' => 'integer',
                ],
            ],
            'masque.famille' => [
                'property' => 'masque.famille',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche d\'obs par famille',
                    'name' => 'masque.famille',
                    'type' => 'string',
                ],
            ],
        ];
    }
}
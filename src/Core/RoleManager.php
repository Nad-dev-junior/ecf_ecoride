<?php

namespace Ecoride\Ecoride\Core;

class RoleManager
{
    // Definition des bits pour chaque role
    const VISITEUR = 1; // 1 (0001)
    const UTILISATEUR = 2; // 2 (0010)
    const EMPLOYE = 4; // 4 (0100)
    const  ADMINISTRATEUR = 8; // 8 (1000)


    // Masques combines pour la hierarchie
    const ROLE_UTILISATEUR = self::VISITEUR | self::UTILISATEUR; // 1+2 = 3 (0011)
    const ROLE_EMPLOYE = self::ROLE_UTILISATEUR | self::EMPLOYE; // 3 + 4 = 7
    const ROLE_ADMIN = self::ROLE_EMPLOYE | self::ADMINISTRATEUR; // 1 + 2 + 4 + 8 = 15

    // Masques pour les mapping des roles vers leur masque complets
    const MASQUES = [
        'visiteur' => self::VISITEUR,
        'utilisateur' => self::UTILISATEUR,
        'employe' => self::EMPLOYE,
        'administrateur' => self::ADMINISTRATEUR
    ];
    const MASQUES_INVERSES = [
        self::VISITEUR => 'visiteur',
        self::UTILISATEUR => 'utilisateur',
        self::EMPLOYE => 'employe',
        self::ADMINISTRATEUR => 'administrateur'
    ];

    public static function has_role(int $mask, $role): bool
    {
        if (is_int($role)) {
            $requiredRole = $role;
        } else {
            $requiredRole = self::MASQUES[$role] ?? self::VISITEUR;
        }
        return ($mask & $requiredRole) === $requiredRole;
    }

    public function get_role_name(int $mask): string
    {
        return self::MASQUES_INVERSES[$mask] ?? 'Visiteur';
    }

    public static function get_admin_role_name(int $mask): string
    {
        if (self::has_role($mask, self::ADMINISTRATEUR)) return 'Administrateur';
        if (self::has_role($mask, self::EMPLOYE)) return 'Employe';
        if (self::has_role($mask, self::UTILISATEUR)) return 'Utilisateur';
        return 'Visiteur';
    }

    public static function get_admin_roles(int $mask): array
    {
        $roles = [];
        if (self::has_role($mask, self::ADMINISTRATEUR)) $roles[] =  'Administrateur';
        if (self::has_role($mask, self::EMPLOYE)) {
            $roles[] = 'Employe';
        }
        if (self::has_role($mask, self::UTILISATEUR)) $roles[] = 'Utilisateur';
        if (self::has_role($mask, self::VISITEUR)) $roles[] = 'Visiteur';

        return $roles;
    }

    public static function is_valid_mask(int $mask): bool
    {
        return in_array($mask, [1, 3, 7, 15]);
    }
}
<?php

namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\Service;

class AddressAutocompleteService extends Service
{
    private \PDO $connection;

    public function __construct()
    {
        parent::__construct();
        $this->connection = Database::getInstance()->getConnection();
    }

    public function search_address(string $query): array
    {
        // Nettoyer query
         $query = trim($query);
        // $query = sanitize($query);

        if (mb_strlen($query) < 3) return [];

        // 1. Recuperation des resultats en BDD
        $dbResults = $this->get_database_suggestions($query);

        // 2. Recuperation des resultats de l'API
        $apiResults = $this->get_api_suggestions($query);

        // 3. Fusionner ces deux resultats
        return $this->merge_and_deduplicate($dbResults, $apiResults);
    }

    private function get_database_suggestions(string $query): array
    {
        try {// Recherche lieu depart et lieu arrivee des covoiturages dans notre BDD
            $sql = "
            SELECT DISTINCT lieu_depart as label, 'database' as source
            FROM covoiturage
            WHERE lieu_depart LIKE ? AND statut = 'prevu'
            UNION
            SELECT DISTINCT lieu_arrivee as label, 'database' as source
            FROM covoiturage
            WHERE lieu_arrivee LIKE ? AND statut = 'prevu'
            ORDER BY label
            LIMIT 8
        ";

            $stmt = $this->connection->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $results = $stmt->fetchAll();

            return array_map(function($result) {
                return [
                    'label' => $result->label,
                    'source' => $result->source,
                    'type' => 'covoiturage', // Pour indiquer que ca provient de la table covoiturage
                ];
            }, $results);
        } catch (\PDOException $e) {
            error_log("Erreur recherche de base de donnees: {$e->getMessage()}");
            return [];
        }

    }

    private function get_api_suggestions(string $query): array
    {
        $url = "https://api-adresse.data.gouv.fr/search/?q=". urlencode($query) . "&limit=8&type=street&autocomplete=1";

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => APP_NAME . '/1.0'
                ],
                'ssl' =>[
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            $data = json_decode($response, true);

            $results = [];
            $addedLabels = [];

            foreach ($data['features'] as $feature) {
                if (!isset($feature['properties'])) {
                    continue;
                }

                $properties = $feature['properties'];
                $label = $properties['label'] ?? '';
                $city = $properties['city'] ?? '';
                $postcode = $properties['postcode'] ?? '';
                $context = $properties['context'] ?? '';

                if ($label && !in_array($label, $addedLabels)) {
                    $results[] = [
                        'label' => $label,
                        'city' => $city,
                        'postcode' => $postcode,
                        'context' => $context,
                        'source' => 'api',
                        'type' => $properties['type'] ?? 'street'
                    ];

                    $addedLabels[] = $label;
                }

                if (count($results) > 7) {
                    break;
                }
            }

            return $results ?? [];
        } catch (\Exception $e) {
            error_log("Erreur API adresse: {$e->getMessage()}");
            return [];
        }
    }

    private function merge_and_deduplicate(array $dbResults, array $apiResults): array
    {
        $merged = [];
        $usedLabels = [];
        
        // D'abord les dbResults
        foreach ($dbResults as $dbResult) {
            $label = trim(strtolower($dbResult['label']));

            if (!in_array($label, $usedLabels)) {
                $merged[] = $dbResult;
                $usedLabels[] = $label;
            }
        }

        // Ensuite les apiResults
        foreach ($apiResults as $apiResult) {
            $label = trim(strtolower($apiResult['label']));

            if (!in_array($label, $usedLabels)) {
                $merged[] = $apiResult;
                $usedLabels[] = $label;
            }
        }

        // On va trier le tableau pour une meilleur presentation
        // ['label' => 'paris', 'city' => 'paris V']
        usort($merged, function($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        // Limiter au 10 premiers resultats
        return array_slice($merged, 0, 8);
    }
}
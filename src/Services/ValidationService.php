<?php

namespace Ecoride\Ecoride\Services;

class ValidationService
{

    private array $errors = [];

    public function validate_required(string $field, mixed $value): bool
    {
        if (empty($value) || trim($value) === '') {
            $this->errors[$field] = "Le champ $field est obligatoire.";
            // ['pseudo' => "Le champ pseudo est obligatoire."]
            return false;
        }

        return true;
    }

    public function validate_email(string $field, mixed $value)
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "l'adresse email n'est pas valide.";
            return false;
        }

        return true;
    }

    public function validate_max(string $field, $value, $max)
    {
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field] = "Le champ $field doit contenir au plus $max caracteres.";
            return false;
        }

        return true;
    }

    public function validate_min(string $field, $value, $min)
    {
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field] = "Le champ $field doit contenir au plus $min caracteres.";
            return false;
        }

        return true;
    }

    public function validate_numeric(string $field, $value)
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "Le champ $field doit etre une valeur numerique.";
            return false;
        }

        return true;
    }

    public function validate_date($field, $value) {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date && $date->format('Y-m-d') === $value) {
            return true;
        }
        
        $this->errors[$field] = "Le champ $field doit être une date valide 'YYYY-mm-dd'";
        return false;
     
    }

    /**
     * @throws \Exception
     */
    public function validate_adult(string $field, $value) {
        if (!empty($value)) {
            $birthdate = new \DateTime($value);
            $today = new \DateTime();
            $age = $today->diff($birthdate)->y;

            if ($age < 18) {
                $this->errors[$field] = "Vous devez avoir au moins au moins 18 ans pour postuler.";
                return false;
            }
        }

        return true;
    }

    public function validate_phone($field, $value) {
        if (!empty($value)) {
            $value = preg_replace('/[ .-]/', '', $value); // +33 3 03 69 74 12 => +33303697412
            if (!preg_match('/^(0|\+33|0033)[1-9]([0-9]{2}){4}$/', $value)) {
                $this->errors[$field] = "Le numero de telephone n'est pas valide.";
                return false;
            }
        }

        return true;
    }

    public function validate_license_plate($field, $value) {
        if (!empty($value)) {
            $value = strtoupper(preg_replace('/[ .-]/', '', $value));
            if (!preg_match('/^[A-HJ-NP-TV-Z]{2}\d{3}[A-HJ-NP-TV-Z]{2}$/', $value)) {
                $this->errors[$field] = "Le numero d'immatriculation n'est pas valide.";
                return false;
            }
        }

        return true;
    }

    public function validate_unique($field, $value, $table, $column = null, $exceptedId = null) {
        if (!empty($value)) {
            $column = $column ?? $field;
            $db = \Ecoride\Ecoride\Core\Database::getInstance()->getConnection();

            $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?"; //SELECT COUNT(*) FROM voiture WHERE
            // immatrculation = pt 725 lp
            $params = [$value];

            if ($exceptedId) {
                $sql .= " AND user_id != ?";
                $params[] = $exceptedId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->fetchColumn() > 0) {
                $this->errors[$field] = "Cette valeur existe deja.";
                return false;
            }
        }

        return true;
    }

    public function get_errors(): array
    {
        return $this->errors;
    }

    public function has_errors(): bool
    {
        return !empty($this->errors);
    }

    public function get_error(string $field) {
        return $this->errors[$field] ?? null;
    }

    public function clear_errors(): void
    {
        $this->errors = [];
    }
}
<?php
/**
 * Tax Configuration Model
 * Handles all database operations related to tax configurations.
 * Location: app/models/TaxConfigurationModel.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class TaxConfigurationModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get active tax configuration
     *
     * @return array|null
     */
    public function getActiveConfiguration(): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tax_configurations 
             WHERE is_active = 1 
             ORDER BY effective_date DESC 
             LIMIT 1"
        );
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get tax rules for a configuration
     *
     * @param int $configurationId
     * @return array
     */
    public function getTaxRules(int $configurationId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tax_rules 
             WHERE tax_configuration_id = :config_id AND is_active = 1 
             ORDER BY priority ASC"
        );
        $stmt->execute(['config_id' => $configurationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate tax for an amount
     *
     * @param float $amount
     * @param int|null $configurationId
     * @return array
     */
    public function calculateTax(float $amount, ?int $configurationId = null): array
    {
        if (!$configurationId) {
            $config = $this->getActiveConfiguration();
            $configurationId = $config['id'] ?? null;
        }

        if (!$configurationId) {
            return [
                'total_tax' => 0,
                'tax_breakdown' => []
            ];
        }

        $rules = $this->getTaxRules($configurationId);
        $totalTax = 0;
        $breakdown = [];

        foreach ($rules as $rule) {
            $taxAmount = ($amount * $rule['rate']) / 100;
            $totalTax += $taxAmount;
            
            $breakdown[] = [
                'name' => $rule['name'],
                'rate' => $rule['rate'],
                'amount' => $taxAmount,
                'amount_formatted' => DataStandardizationHelper::formatCurrency($taxAmount)
            ];
        }

        return [
            'total_tax' => $totalTax,
            'total_tax_formatted' => DataStandardizationHelper::formatCurrency($totalTax),
            'tax_breakdown' => $breakdown
        ];
    }
}

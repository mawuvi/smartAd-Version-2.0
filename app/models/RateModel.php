<?php
require_once __DIR__ . '/../../bootstrap.php';

class RateModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function findRate(array $criteria): ?array
    {
        try {
            $sql = "SELECT * FROM vw_rate_details 
                    WHERE publication_id = ? 
                    AND color_type_id = ? 
                    AND ad_category_id = ? 
                    AND ad_size_id = ? 
                    AND page_position_id = ?
                    AND status = 'active'
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $criteria['publication_id'],
                $criteria['color_type_id'],
                $criteria['ad_category_id'],
                $criteria['ad_size_id'],
                $criteria['page_position_id']
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;

        } catch (PDOException $e) {
            error_log("RateModel Error (findRate): " . $e->getMessage());
            return null;
        }
    }

    public function calculateTaxBreakdown(float $baseRate, int $publicationId): array
    {
        try {
            // Get tax configuration for the publication
            $sql = "SELECT tc.*, t.name as tax_name, t.rate as tax_rate
                    FROM tax_configurations tc
                    LEFT JOIN taxes t ON tc.tax_id = t.id
                    WHERE tc.publication_id = ? 
                    AND tc.status = 'active'
                    AND tc.deleted_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$publicationId]);
            $taxConfigs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $taxBreakdown = [];
            foreach ($taxConfigs as $config) {
                $taxAmount = ($baseRate * $config['tax_rate']) / 100;
                $taxBreakdown[] = [
                    'id' => $config['tax_id'],
                    'name' => $config['tax_name'],
                    'rate' => $config['tax_rate'],
                    'amount' => $taxAmount
                ];
            }

            return $taxBreakdown;

        } catch (PDOException $e) {
            error_log("RateModel Error (calculateTaxBreakdown): " . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT r.*, 
                           p.name as publication_name,
                           ct.name as color_type_name,
                           ac.name as ad_category_name,
                           ads.name as ad_size_name,
                           pp.name as page_position_name
                    FROM rates r
                    LEFT JOIN publications p ON r.publication_id = p.id
                    LEFT JOIN color_types ct ON r.color_type_id = ct.id
                    LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
                    LEFT JOIN ad_sizes ads ON r.ad_size_id = ads.id
                    LEFT JOIN page_positions pp ON r.page_position_id = pp.id
                    WHERE r.id = ? AND r.deleted_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;

        } catch (PDOException $e) {
            error_log("RateModel Error (findById): " . $e->getMessage());
            return null;
        }
    }

    public function findAll(array $filters = []): array
    {
        try {
            $sql = "SELECT r.*, 
                           p.name as publication_name,
                           ct.name as color_type_name,
                           ac.name as ad_category_name,
                           ads.name as ad_size_name,
                           pp.name as page_position_name
                    FROM rates r
                    LEFT JOIN publications p ON r.publication_id = p.id
                    LEFT JOIN color_types ct ON r.color_type_id = ct.id
                    LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
                    LEFT JOIN ad_sizes ads ON r.ad_size_id = ads.id
                    LEFT JOIN page_positions pp ON r.page_position_id = pp.id
                    WHERE r.deleted_at IS NULL";

            $params = [];
            $conditions = [];

            if (!empty($filters['publication_id'])) {
                $conditions[] = "r.publication_id = ?";
                $params[] = $filters['publication_id'];
            }

            if (!empty($filters['color_type_id'])) {
                $conditions[] = "r.color_type_id = ?";
                $params[] = $filters['color_type_id'];
            }

            if (!empty($filters['ad_category_id'])) {
                $conditions[] = "r.ad_category_id = ?";
                $params[] = $filters['ad_category_id'];
            }

            if (!empty($filters['ad_size_id'])) {
                $conditions[] = "r.ad_size_id = ?";
                $params[] = $filters['ad_size_id'];
            }

            if (!empty($filters['page_position_id'])) {
                $conditions[] = "r.page_position_id = ?";
                $params[] = $filters['page_position_id'];
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY r.created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . intval($filters['limit']);
                if (!empty($filters['offset'])) {
                    $sql .= " OFFSET " . intval($filters['offset']);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("RateModel Error (findAll): " . $e->getMessage());
            return [];
        }
    }
}

<?php
/**
 * Client Model
 * Handles all database operations related to the clients table.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class ClientModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function create(array $data): array
    {
        try {
            $clientNumber = $this->generateClientNumber();
            
            $sql = "INSERT INTO clients (
                        client_number, company_name, client_type, contact_person,
                        contact_phone, contact_email, email, address, client_address, 
                        city, region, postal_code, country, website, 
                        industry, company_size, annual_revenue, 
                        credit_limit, payment_terms, preferred_contact_method,
                        notes, status, created_by, created_at
                    ) VALUES (
                        :client_number, :company_name, :client_type, :contact_person,
                        :contact_phone, :contact_email, :email, :address, :client_address,
                        :city, :region, :postal_code, :country, :website,
                        :industry, :company_size, :annual_revenue,
                        :credit_limit, :payment_terms, :preferred_contact_method,
                        :notes, :status, :created_by, NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':client_number' => $clientNumber,
                ':company_name' => $data['company_name'],
                ':client_type' => $data['client_type'],
                ':contact_person' => $data['contact_person'],
                ':contact_phone' => $data['contact_phone'],
                ':contact_email' => $data['contact_email'],
                ':email' => $data['email'],
                ':address' => $data['address'],
                ':client_address' => $data['client_address'],
                ':city' => $data['city'],
                ':region' => $data['region'],
                ':postal_code' => $data['postal_code'],
                ':country' => $data['country'],
                ':website' => $data['website'],
                ':industry' => $data['industry'],
                ':company_size' => $data['company_size'],
                ':annual_revenue' => $data['annual_revenue'],
                ':credit_limit' => $data['credit_limit'],
                ':payment_terms' => $data['payment_terms'],
                ':preferred_contact_method' => $data['preferred_contact_method'],
                ':notes' => $data['notes'],
                ':status' => $data['status'] ?? 'active',
                ':created_by' => $data['created_by']
            ]);
            
            $clientId = (int)$this->db->lastInsertId();
            return $this->findById($clientId);
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (create): " . $e->getMessage());
            throw new Exception("Failed to create client: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT c.*, cc.credit_limit, cc.available_credit, cc.used_credit, cc.credit_status
                    FROM clients c
                    LEFT JOIN client_credit cc ON c.id = cc.client_id
                    WHERE c.id = ? AND c.deleted_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (findById): " . $e->getMessage());
            return null;
        }
    }

    public function search(string $query, int $limit = 20): array
    {
        try {
            $sql = "SELECT c.*, cc.credit_limit, cc.available_credit, cc.used_credit, cc.credit_status
                    FROM clients c
                    LEFT JOIN client_credit cc ON c.id = cc.client_id
                    WHERE c.deleted_at IS NULL 
                    AND (c.company_name LIKE ? OR c.contact_person LIKE ? OR c.client_number LIKE ?)
                    ORDER BY c.company_name
                    LIMIT ?";

            $searchTerm = "%{$query}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (search): " . $e->getMessage());
            return [];
        }
    }

    public function findAll(array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM vw_client_summary WHERE 1=1";

            $params = [];
            $conditions = [];

            if (!empty($filters['client_type'])) {
                $conditions[] = "client_type = ?";
                $params[] = $filters['client_type'];
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY company_name";

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
            error_log("ClientModel Error (findAll): " . $e->getMessage());
            return [];
        }
    }

    public function update(int $id, array $data): ?array
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            $allowedFields = [
                'company_name', 'client_type', 'contact_person', 'contact_phone', 
                'contact_email', 'email', 'address', 'client_address', 'city', 
                'region', 'postal_code', 'country', 'website', 'industry', 
                'company_size', 'annual_revenue', 'credit_limit', 'payment_terms', 
                'preferred_contact_method', 'notes', 'status'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($fields)) {
                throw new Exception("No valid fields to update");
            }

            $sql = "UPDATE clients SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return null;
            }
            
            return $this->findById($id);

        } catch (PDOException $e) {
            error_log("ClientModel Error (update): " . $e->getMessage());
            throw new Exception("Failed to update client: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE clients SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (delete): " . $e->getMessage());
            throw new Exception("Failed to delete client: " . $e->getMessage());
        }
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['company_name'])) {
            $errors[] = 'Company name is required';
        }

        if (empty($data['client_type'])) {
            $errors[] = 'Client type is required';
        }

        if (empty($data['contact_person'])) {
            $errors[] = 'Contact person is required';
        }

        if (empty($data['contact_phone'])) {
            $errors[] = 'Contact phone is required';
        }

        if (empty($data['contact_email'])) {
            $errors[] = 'Contact email is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get all clients with pagination and advanced filtering
     * @param array $filters - Filter options including search, status, type, etc.
     * @return array - Array with 'clients' and 'total' count
     */
    public function getAllWithPagination(array $filters = []): array
    {
        try {
            // Build base query with calculated fields
            $query = "SELECT c.*, 
                            u.username as created_by_name,
                            COALESCE(debt.total_outstanding, 0) as outstanding_debt,
                            (c.credit_limit - COALESCE(debt.total_outstanding, 0)) as available_credit
                        FROM clients c
                        LEFT JOIN users u ON c.created_by = u.id
                        LEFT JOIN (
                            SELECT client_id, SUM(total) as total_outstanding
                      FROM bookings
                            WHERE status IN ('confirmed', 'placed') AND deleted_at IS NULL
                            GROUP BY client_id
                        ) debt ON c.id = debt.client_id
                        WHERE c.deleted_at IS NULL";
            
            $params = [];
            $conditions = [];
            
            // Apply filters
            if (!empty($filters['search'])) {
                $conditions[] = "(c.company_name LIKE ? OR c.contact_person LIKE ? OR c.client_number LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['status'])) {
                $conditions[] = "c.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['client_type'])) {
                $conditions[] = "c.client_type = ?";
                $params[] = $filters['client_type'];
            }
            
            if (!empty($filters['credit_rating'])) {
                $conditions[] = "c.credit_rating = ?";
                $params[] = $filters['credit_rating'];
            }
            
            // Date range filters
            if (!empty($filters['date_from'])) {
                $conditions[] = "DATE(c.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $conditions[] = "DATE(c.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) FROM clients c WHERE c.deleted_at IS NULL";
            if (!empty($conditions)) {
                $countQuery .= " AND " . implode(" AND ", $conditions);
            }
            
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Apply sorting
            $sort = $filters['sort'] ?? 'company_name';
            $sortDirection = strtoupper($filters['sort_direction'] ?? 'ASC');
            $allowedSorts = ['company_name', 'client_number', 'created_at', 'status', 'client_type'];
            
            if (in_array($sort, $allowedSorts)) {
                $query .= " ORDER BY c.{$sort} {$sortDirection}";
            } else {
                $query .= " ORDER BY c.company_name ASC";
            }
            
            // Apply pagination
            $page = intval($filters['page'] ?? 1);
            $limit = intval($filters['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $query .= " LIMIT {$limit} OFFSET {$offset}";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format currency values using DataStandardizationHelper
            if (class_exists('SmartAdVault\\Helpers\\DataStandardizationHelper')) {
                foreach ($clients as &$client) {
                    $client['outstanding_debt'] = DataStandardizationHelper::formatCurrency($client['outstanding_debt']);
                    $client['available_credit'] = DataStandardizationHelper::formatCurrency($client['available_credit']);
                    $client['credit_limit'] = DataStandardizationHelper::formatCurrency($client['credit_limit']);
                }
            }
            
            return [
                'clients' => $clients,
                'total' => (int)$total
            ];
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (getAllWithPagination): " . $e->getMessage());
            return [
                'clients' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Get client statistics for dashboard
     * @return array - Statistics including total, active, credit warnings, revenue
     */
    public function getStatistics(): array
    {
        try {
            // Total clients
            $totalStmt = $this->db->query("SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL");
            $totalClients = $totalStmt->fetchColumn();
            
            // Active clients
            $activeStmt = $this->db->query("SELECT COUNT(*) FROM clients WHERE status = 'active' AND deleted_at IS NULL");
            $activeClients = $activeStmt->fetchColumn();
            
            // Clients with credit warnings (outstanding debt > 80% of credit limit)
            $warningStmt = $this->db->query("
                SELECT COUNT(*) 
                FROM clients c
                LEFT JOIN (
                    SELECT client_id, SUM(total) as total_outstanding
                    FROM bookings 
                    WHERE status IN ('confirmed', 'placed') AND deleted_at IS NULL
                    GROUP BY client_id
                ) debt ON c.id = debt.client_id
                WHERE c.deleted_at IS NULL 
                AND c.credit_limit > 0
                AND COALESCE(debt.total_outstanding, 0) > (c.credit_limit * 0.8)
            ");
            $creditWarnings = $warningStmt->fetchColumn();
            
            // Total revenue from bookings
            $revenueStmt = $this->db->query("
                SELECT COALESCE(SUM(total), 0) 
                FROM bookings 
                WHERE status IN ('confirmed', 'placed') AND deleted_at IS NULL
            ");
            $totalRevenue = $revenueStmt->fetchColumn();
            
            // Format currency using DataStandardizationHelper
            if (class_exists('SmartAdVault\\Helpers\\DataStandardizationHelper')) {
                $totalRevenue = DataStandardizationHelper::formatCurrency($totalRevenue);
            }
            
            return [
                'total_clients' => (int)$totalClients,
                'active_clients' => (int)$activeClients,
                'credit_warnings' => (int)$creditWarnings,
                'total_revenue' => $totalRevenue
            ];
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (getStatistics): " . $e->getMessage());
            return [
                'total_clients' => 0,
                'active_clients' => 0,
                'credit_warnings' => 0,
                'total_revenue' => 'GHS 0.00'
            ];
        }
    }

    /**
     * Get all clients for export (no pagination)
     * @param array $filters - Filter options
     * @return array - All matching clients
     */
    public function getAllForExport(array $filters = []): array
    {
        try {
            $query = "SELECT c.*, 
                            u.username as created_by_name,
                            COALESCE(debt.total_outstanding, 0) as outstanding_debt,
                            (c.credit_limit - COALESCE(debt.total_outstanding, 0)) as available_credit
                        FROM clients c
                        LEFT JOIN users u ON c.created_by = u.id
                        LEFT JOIN (
                            SELECT client_id, SUM(total) as total_outstanding
                            FROM bookings 
                            WHERE status IN ('confirmed', 'placed') AND deleted_at IS NULL
                            GROUP BY client_id
                        ) debt ON c.id = debt.client_id
                        WHERE c.deleted_at IS NULL";
            
            $params = [];
            $conditions = [];
            
            // Apply same filters as getAllWithPagination
            if (!empty($filters['search'])) {
                $conditions[] = "(c.company_name LIKE ? OR c.contact_person LIKE ? OR c.client_number LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['status'])) {
                $conditions[] = "c.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['client_type'])) {
                $conditions[] = "c.client_type = ?";
                $params[] = $filters['client_type'];
            }
            
            if (!empty($filters['credit_rating'])) {
                $conditions[] = "c.credit_rating = ?";
                $params[] = $filters['credit_rating'];
            }
            
            if (!empty($filters['date_from'])) {
                $conditions[] = "DATE(c.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $conditions[] = "DATE(c.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY c.company_name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("ClientModel Error (getAllForExport): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate CSV data for client export
     * @param array $clients - Client data array
     * @return string - CSV formatted string
     */
    public function generateClientsCSV(array $clients): string
    {
        $output = fopen('php://temp', 'r+');
        
        // CSV headers
        $headers = [
            'Client Number',
            'Company Name',
            'Client Type',
            'Contact Person',
            'Contact Phone',
            'Contact Email',
            'Industry',
            'Status',
            'Credit Limit',
            'Outstanding Debt',
            'Available Credit',
            'Created Date',
            'Created By'
        ];
        
        fputcsv($output, $headers);
        
        // Add client data
        foreach ($clients as $client) {
            $row = [
                $client['client_number'] ?? '',
                $client['company_name'] ?? '',
                $client['client_type'] ?? '',
                $client['contact_person'] ?? '',
                $client['contact_phone'] ?? '',
                $client['contact_email'] ?? '',
                $client['industry'] ?? '',
                $client['status'] ?? '',
                $client['credit_limit'] ?? '0',
                $client['outstanding_debt'] ?? '0',
                $client['available_credit'] ?? '0',
                $client['created_at'] ?? '',
                $client['created_by_name'] ?? ''
            ];
            
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);
        
        return $csvData;
    }
    
    /**
     * Get date range condition for SQL queries
     * @param string $field - Database field name
     * @param string $from - Start date
     * @param string $to - End date
     * @return array - Array with condition and parameters
     */
    private function getDateRangeCondition(string $field, string $from, string $to): array
    {
        $conditions = [];
        $params = [];
        
        if (!empty($from)) {
            $conditions[] = "DATE({$field}) >= ?";
            $params[] = $from;
        }
        
        if (!empty($to)) {
            $conditions[] = "DATE({$field}) <= ?";
            $params[] = $to;
        }
            
            return [
            'conditions' => $conditions,
            'params' => $params
        ];
    }

    private function generateClientNumber(): string
    {
        $year = date('Y');
        $prefix = "CLI-{$year}-";
        
        $sql = "SELECT COUNT(*) FROM clients WHERE client_number LIKE ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        
        $count = $stmt->fetchColumn();
        $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $nextNumber;
    }
}

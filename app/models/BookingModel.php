<?php
require_once __DIR__ . '/../../bootstrap.php';

use smartAdVault\config\Config;

class BookingModel
{
    private $db;

    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function create(array $data): array
    {
        try {
            // Check draft limit if status is draft
            if (($data['status'] ?? self::STATUS_DRAFT) === self::STATUS_DRAFT) {
                $maxDrafts = Config::get('booking.max_drafts_per_client', 1);
                $currentDraftCount = $this->getDraftCount($data['client_id']);
                
                if ($currentDraftCount >= $maxDrafts) {
                    throw new Exception("Maximum draft limit ({$maxDrafts}) reached for this client. Please complete or delete existing drafts before creating new ones.");
                }
            }

            $bookingNumber = $this->generateBookingNumber();
            
            $sql = "INSERT INTO bookings (
                        booking_number, client_id,
                        publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id,
                        rate_id, publication_date, insertions,
                        base_rate, total_tax, discount_amount, discount_reason, total_amount,
                        notes, status, created_by, created_at
                    ) VALUES (
                        :booking_number, :client_id,
                        :publication_id, :color_type_id, :ad_category_id, :ad_size_id, :page_position_id,
                        :rate_id, :publication_date, :insertions,
                        :base_rate, :total_tax, :discount_amount, :discount_reason, :total_amount,
                        :notes, :status, :created_by, NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':booking_number' => $bookingNumber,
                ':client_id' => $data['client_id'],
                ':publication_id' => $data['publication_id'],
                ':color_type_id' => $data['color_type_id'],
                ':ad_category_id' => $data['ad_category_id'],
                ':ad_size_id' => $data['ad_size_id'],
                ':page_position_id' => $data['page_position_id'],
                ':rate_id' => $data['rate_id'],
                ':publication_date' => $data['publication_date'],
                ':insertions' => $data['insertions'] ?? 1,
                ':base_rate' => $data['base_rate'],
                ':total_tax' => $data['tax_total'],
                ':discount_amount' => $data['discount_amount'] ?? 0.00,
                ':discount_reason' => $data['discount_reason'] ?? null,
                ':total_amount' => $data['total'],
                ':notes' => $data['special_instructions'] ?? null,
                ':status' => $data['status'] ?? self::STATUS_DRAFT,
                ':created_by' => $data['created_by']
            ]);
            
            $bookingId = (int)$this->db->lastInsertId();
            
            // Log the booking creation
            error_log("Booking created: ID {$bookingId}, Client ID {$data['client_id']}, Status: " . ($data['status'] ?? self::STATUS_DRAFT));
            
            return $this->findById($bookingId);

        } catch (PDOException $e) {
            error_log("BookingModel Error (create): " . $e->getMessage());
            throw new Exception("Failed to create booking: " . $e->getMessage());
        }
    }

    public function getDraftCount(int $clientId): int
    {
        try {
            $query = "SELECT COUNT(*) FROM bookings 
                      WHERE client_id = ? 
                      AND status = 'draft' 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clientId]);
            return (int)$stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("BookingModel Error (getDraftCount): " . $e->getMessage());
            return 0;
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM vw_booking_details WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
            
        } catch (PDOException $e) {
            error_log("BookingModel Error (findById): " . $e->getMessage());
            return null;
        }
    }

    public function findAll(array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM vw_booking_details WHERE 1=1";

            $params = [];
            $conditions = [];
            
            if (!empty($filters['client_id'])) {
                $conditions[] = "client_id = ?";
                $params[] = $filters['client_id'];
            }
            
            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['publication_id'])) {
                $conditions[] = "publication_id = ?";
                $params[] = $filters['publication_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $conditions[] = "publication_date >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $conditions[] = "publication_date <= ?";
                $params[] = $filters['date_to'];
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY created_at DESC";
            
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
            error_log("BookingModel Error (findAll): " . $e->getMessage());
            return [];
        }
    }

    public function update(int $id, array $data): ?array
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            $allowedFields = [
                'publication_id', 'color_type_id', 'ad_category_id', 'ad_size_id', 
                'page_position_id', 'rate_id', 'publication_date', 'insertions',
                'base_rate', 'total_tax', 'discount_amount', 'discount_reason', 
                'total_amount', 'notes', 'status'
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

            $sql = "UPDATE bookings SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return null;
            }

            return $this->findById($id);

        } catch (PDOException $e) {
            error_log("BookingModel Error (update): " . $e->getMessage());
            throw new Exception("Failed to update booking: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE bookings SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("BookingModel Error (delete): " . $e->getMessage());
            throw new Exception("Failed to delete booking: " . $e->getMessage());
        }
    }

    public function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['client_id'])) {
            $errors[] = 'Client ID is required';
        }

        if (empty($data['publication_id'])) {
            $errors[] = 'Publication is required';
        }

        if (empty($data['color_type_id'])) {
            $errors[] = 'Color type is required';
        }

        if (empty($data['ad_category_id'])) {
            $errors[] = 'Ad category is required';
        }

        if (empty($data['ad_size_id'])) {
            $errors[] = 'Ad size is required';
        }

        if (empty($data['page_position_id'])) {
            $errors[] = 'Page position is required';
        }

        if (empty($data['rate_id'])) {
            $errors[] = 'Rate ID is required - please calculate rate first';
        }

        if (empty($data['publication_date'])) {
            $errors[] = 'Publication date is required';
        }
        
        if (empty($data['base_rate']) || $data['base_rate'] <= 0) {
            $errors[] = 'Base rate must be greater than 0';
        }
        
        if (empty($data['total']) || $data['total'] <= 0) {
            $errors[] = 'Total amount must be greater than 0';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function generateBookingNumber(): string
    {
        $year = date('Y');
        $prefix = "BK-{$year}-";
        
        $sql = "SELECT COUNT(*) FROM bookings WHERE booking_number LIKE ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        
        $count = $stmt->fetchColumn();
        $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $nextNumber;
    }
}

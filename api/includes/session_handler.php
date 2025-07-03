<?php
/**
 * Custom Session Handler for Vercel/Serverless Environment
 * Stores session data in database instead of files
 */

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $db;
    private $table = 'sessions';
    private $lifetime;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->lifetime = ini_get('session.gc_maxlifetime') ?: 1440; // Default 24 minutes
        
        // Create sessions table if not exists
        $this->createTable();
    }

    private function createTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS sessions (
                session_id VARCHAR(128) PRIMARY KEY,
                user_id INT(11) DEFAULT NULL,
                data TEXT,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_last_activity (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $this->db->exec($sql);
        } catch (Exception $e) {
            error_log("Failed to create sessions table: " . $e->getMessage());
        }
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        try {
            $stmt = $this->db->prepare("
                SELECT data FROM sessions 
                WHERE session_id = ? 
                AND last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$id, $this->lifetime]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['data'] : '';
        } catch (Exception $e) {
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }

    public function write($id, $data): bool {
        try {
            // Extract user_id from session data if available
            $user_id = null;
            $decoded = session_decode($data);
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO sessions (session_id, user_id, data, last_activity) 
                VALUES (?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                    user_id = VALUES(user_id),
                    data = VALUES(data), 
                    last_activity = NOW()
            ");
            return $stmt->execute([$id, $user_id, $data]);
        } catch (Exception $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }

    public function destroy($id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE session_id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }

    public function gc($maxlifetime): int|false {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM sessions 
                WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$maxlifetime]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Session GC error: " . $e->getMessage());
            return false;
        }
    }
    
    // Clean up old sessions for a specific user
    public function cleanupUserSessions($user_id, $keep_current = true) {
        try {
            $current_session_id = session_id();
            $sql = "DELETE FROM sessions WHERE user_id = ?";
            $params = [$user_id];
            
            if ($keep_current && $current_session_id) {
                $sql .= " AND session_id != ?";
                $params[] = $current_session_id;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log("User session cleanup error: " . $e->getMessage());
        }
    }
} 
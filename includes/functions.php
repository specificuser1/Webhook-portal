<?php
require_once 'db.php';

class UserFunctions {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        
        // Check if connection is successful
        if (!$this->conn) {
            die("Database connection failed. Please check your database configuration.");
        }
    }

    public function register($username, $email, $password) {
        try {
            // Check connection first
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        } catch(Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        try {
            // Check connection first
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $query = "SELECT * FROM users WHERE username = :username OR email = :username";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['credits'] = $user['credits'];
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        } catch(Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserCredits($user_id) {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $query = "SELECT credits, last_credit_earn FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("GetUserCredits error: " . $e->getMessage());
            return ['credits' => 0, 'last_credit_earn' => date('Y-m-d H:i:s')];
        }
    }

    public function updateCredits($user_id, $new_credits) {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $query = "UPDATE users SET credits = :credits, last_credit_earn = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':credits', $new_credits);
            $stmt->bindParam(':id', $user_id);
            return $stmt->execute();
        } catch(Exception $e) {
            error_log("UpdateCredits error: " . $e->getMessage());
            return false;
        }
    }

    public function deductCredits($user_id, $amount) {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $query = "UPDATE users SET credits = credits - :amount WHERE id = :id AND credits >= :amount";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':id', $user_id);
            return $stmt->execute();
        } catch(Exception $e) {
            error_log("DeductCredits error: " . $e->getMessage());
            return false;
        }
    }

    public function addToHistory($user_id, $webhook_url, $message, $status) {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $query = "INSERT INTO webhook_history (user_id, webhook_url, message, status) VALUES (:user_id, :webhook_url, :message, :status)";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':webhook_url', $webhook_url);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch(Exception $e) {
            error_log("AddToHistory error: " . $e->getMessage());
            return false;
        }
    }

    public function getHistory($user_id) {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            $query = "SELECT * FROM webhook_history WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("GetHistory error: " . $e->getMessage());
            return [];
        }
    }

    public function checkAndAddPassiveCredits($user_id) {
        try {
            $userData = $this->getUserCredits($user_id);
            
            if (!$userData || !isset($userData['last_credit_earn'])) {
                return 0;
            }
            
            $lastEarn = strtotime($userData['last_credit_earn']);
            $now = time();
            $minutesPassed = floor(($now - $lastEarn) / 60);
            
            if($minutesPassed >= 1) {
                $creditsToAdd = $minutesPassed * CREDITS_PER_MINUTE;
                $newCredits = $userData['credits'] + $creditsToAdd;
                $this->updateCredits($user_id, $newCredits);
                $_SESSION['credits'] = $newCredits;
                return $creditsToAdd;
            }
            return 0;
        } catch(Exception $e) {
            error_log("CheckAndAddPassiveCredits error: " . $e->getMessage());
            return 0;
        }
    }
}

class WebhookSender {
    public function sendMessage($webhook_url, $message) {
        try {
            $data = ['content' => $message];
            $options = [
                'http' => [
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data),
                    'ignore_errors' => true
                ]
            ];
            
            $context = stream_context_create($options);
            $result = file_get_contents($webhook_url, false, $context);
            
            // Check if request was successful
            if ($result === false) {
                $error = error_get_last();
                error_log("Webhook error: " . print_r($error, true));
                return false;
            }
            
            return true;
        } catch(Exception $e) {
            error_log("Webhook send error: " . $e->getMessage());
            return false;
        }
    }
}
?>

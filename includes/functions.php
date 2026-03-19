<?php
require_once 'db.php';

class UserFunctions {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    public function register($username, $email, $password) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username OR email = :username";
        $stmt = $this->conn->prepare($query);
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
    }

    public function getUserCredits($user_id) {
        $query = "SELECT credits, last_credit_earn FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCredits($user_id, $new_credits) {
        $query = "UPDATE users SET credits = :credits, last_credit_earn = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':credits', $new_credits);
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    }

    public function deductCredits($user_id, $amount) {
        $query = "UPDATE users SET credits = credits - :amount WHERE id = :id AND credits >= :amount";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    }

    public function addToHistory($user_id, $webhook_url, $message, $status) {
        $query = "INSERT INTO webhook_history (user_id, webhook_url, message, status) VALUES (:user_id, :webhook_url, :message, :status)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':webhook_url', $webhook_url);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function getHistory($user_id) {
        $query = "SELECT * FROM webhook_history WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkAndAddPassiveCredits($user_id) {
        $userData = $this->getUserCredits($user_id);
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
    }
}

class WebhookSender {
    public function sendMessage($webhook_url, $message) {
        $data = ['content' => $message];
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($webhook_url, false, $context);
        
        return $result !== false;
    }
}
?>

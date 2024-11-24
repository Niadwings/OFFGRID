<?php
require_once 'config.php';

class Registration {
    private $conn;
    private $messages = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
        session_start();
    }
    
    public function handleRegistration() {
        if (!isset($_POST['submit'])) {
            return;
        }
        
        try {
            // Sanitize and validate inputs
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $cpassword = $_POST['cpassword'] ?? '';
            
            // Validate inputs
            if (!$name || !$email || !$password || !$cpassword) {
                throw new Exception('All fields are required!');
            }
            
            if (strlen($password) < 8) {
                throw new Exception('Password must be at least 8 characters long!');
            }
            
            if (!preg_match('/^[a-zA-Z0-9\s]{2,50}$/', $name)) {
                throw new Exception('Name contains invalid characters!');
            }
            
            if ($password !== $cpassword) {
                throw new Exception('Passwords do not match!');
            }
            
            // Check if user exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('Email already registered!');
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            
            // Insert new user
            $stmt = $this->conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            
            if (!$stmt->execute()) {
                throw new Exception('Registration failed! Please try again.');
            }
            
            $_SESSION['registration_success'] = true;
            header('Location: login.php');
            exit;
            
        } catch (Exception $e) {
            $this->messages[] = $e->getMessage();
        }
    }
    
    private function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    public function getMessages() {
        return $this->messages;
    }
}

// Initialize registration handler
$registration = new Registration($conn);
$registration->handleRegistration();
$messages = $registration->getMessages();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sarili.css">
    <style>
        /* Modal/Popup Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            animation: slideIn 0.3s ease-in-out;
        }

        .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-btn:hover {
            color: #000;
        }

        .modal-message {
            margin: 20px 0;
            font-size: 16px;
            color: #333;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Modal/Popup -->
    <div id="validationModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div class="modal-message"></div>
        </div>
    </div>

    <div class="form-container" style="background: url('./images/logs.jpg') no-repeat center center fixed; background-size: cover; width: 100%; height: 500px;">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="registrationForm" novalidate>
            <h3>Register Now</h3>
            <input type="text" name="name" placeholder="Enter your name" required 
                   class="box" minlength="2" maxlength="50" 
                   pattern="[a-zA-Z0-9\s]+" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            
            <input type="email" name="email" placeholder="Enter your email" required 
                   class="box" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            
            <input type="password" name="password" placeholder="Enter your password" 
                   required class="box" minlength="8">
            
            <input type="password" name="cpassword" placeholder="Confirm your password" 
                   required class="box" minlength="8">
            
            <input type="submit" name="submit" value="Register Now" class="btn">
            
            <p>Already have an account? <a href="login.php">Login now</a></p>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('validationModal');
            const modalMessage = modal.querySelector('.modal-message');
            const closeBtn = modal.querySelector('.close-btn');
            const form = document.getElementById('registrationForm');

            // Show modal with message
            function showModal(message) {
                modalMessage.textContent = message;
                modal.style.display = 'block';
            }

            // Close modal
            function closeModal() {
                modal.style.display = 'none';
            }

            // Close modal when clicking the close button
            closeBtn.addEventListener('click', closeModal);

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                const name = form.querySelector('[name="name"]').value;
                const email = form.querySelector('[name="email"]').value;
                const password = form.querySelector('[name="password"]').value;
                const cpassword = form.querySelector('[name="cpassword"]').value;

                // Client-side validation
                if (!name || !email || !password || !cpassword) {
                    e.preventDefault();
                    showModal('All fields are required!');
                    return;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    showModal('Password must be at least 8 characters long!');
                    return;
                }

                if (!/^[a-zA-Z0-9\s]{2,50}$/.test(name)) {
                    e.preventDefault();
                    showModal('Name contains invalid characters!');
                    return;
                }

                if (password !== cpassword) {
                    e.preventDefault();
                    showModal('Passwords do not match!');
                    return;
                }
            });

            // Show PHP validation messages if any
            <?php if (!empty($messages)): ?>
                showModal(<?php echo json_encode($messages[0]); ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>
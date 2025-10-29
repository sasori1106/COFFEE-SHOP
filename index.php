<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $user;
        $_SESSION['role'] = $row['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coffee Shop TPS - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6B4423 0%, #3E2723 50%, #1A0F0A 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 69, 19, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(101, 67, 33, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 50px 45px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 35px;
        }

        .coffee-icon {
            font-size: 64px;
            margin-bottom: 10px;
            display: inline-block;
            animation: steam 2s ease-in-out infinite;
        }

        @keyframes steam {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        h2 {
            text-align: center;
            color: #4E342E;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            color: #8D6E63;
            margin-bottom: 35px;
            font-size: 14px;
            font-weight: 400;
        }

        .error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            border: 1px solid #ef9a9a;
            font-size: 14px;
            font-weight: 500;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #5D4037;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #D7CCC8;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #FAFAFA;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #8D6E63;
            background: white;
            box-shadow: 0 0 0 4px rgba(141, 110, 99, 0.1);
        }

        input[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6B4423 0%, #8D6E63 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(107, 68, 35, 0.3);
        }

        input[type="submit"]:hover {
            background: linear-gradient(135deg, #8D6E63 0%, #6B4423 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 68, 35, 0.4);
        }

        input[type="submit"]:active {
            transform: translateY(0px);
        }

        .info {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            padding: 18px;
            border-radius: 12px;
            margin-top: 25px;
            font-size: 13px;
            color: #F57C00;
            border: 1px solid #FFD54F;
            line-height: 1.6;
        }

        .info strong {
            display: block;
            margin-bottom: 8px;
            color: #E65100;
            font-size: 14px;
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #D7CCC8;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #8D6E63;
            font-size: 13px;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            h2 {
                font-size: 24px;
            }

            .coffee-icon {
                font-size: 52px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="coffee-icon">☕</div>
            <h2>Coffee Shop TPS</h2>
            <p class="subtitle">Point of Sale System</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error">🚫 <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="username" placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            </div>
            <input type="submit" value="Sign In">
        </form>
        
        <div class="info">
            <strong>🔐 Demo Credentials</strong>
            <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                <span><strong>Admin:</strong> admin / admin123</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span><strong>Cashier:</strong> cashier / cashier123</span>
            </div>
        </div>
    </div>
</body>
</html>
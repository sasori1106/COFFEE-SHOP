<?php
session_start();
include "db.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $role = $conn->real_escape_string($_POST['role']);

    $check = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        if ($conn->query($sql)) {
            header("Location: users.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User - Coffee Shop TPS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        :root {
            --coffee-primary: #6B4423;
            --coffee-secondary: #8D6E63;
            --coffee-light: #D7CCC8;
            --coffee-dark: #4E342E;
            --coffee-accent: #BCAAA4;
            --success: #66BB6A;
            --warning: #FFA726;
            --danger: #EF5350;
            --info: #42A5F5;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #EFEBE9 0%, #F5F5F5 100%);
            color: #3E2723;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(62, 39, 35, 0.15);
            border: 1px solid #EFEBE9;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        h2 {
            color: var(--coffee-dark);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--coffee-secondary);
            font-size: 0.95rem;
        }

        .error {
            background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
            color: #C62828;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 2px solid #EF9A9A;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--coffee-dark);
            font-size: 0.9rem;
        }

        input, select {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--coffee-primary);
            box-shadow: 0 0 0 3px rgba(107, 68, 35, 0.1);
        }

        small {
            display: block;
            margin-top: 0.4rem;
            color: var(--coffee-secondary);
            font-size: 0.85rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        button, .cancel-btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        button {
            background: linear-gradient(135deg, var(--success) 0%, #4CAF50 100%);
            color: white;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 187, 106, 0.4);
        }

        .cancel-btn {
            background: #EFEBE9;
            color: var(--coffee-dark);
        }

        .cancel-btn:hover {
            background: var(--coffee-light);
        }

        .info-box {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            border: 2px solid #FFE082;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            color: #F57C00;
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .info-box strong {
            display: block;
            margin-bottom: 0.3rem;
            color: #E65100;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">👤</div>
            <h2>Add New User</h2>
            <p class="subtitle">Create a new system account</p>
        </div>

        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required minlength="3" maxlength="50" 
                       pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed"
                       placeholder="Enter username">
                <small>Only letters, numbers, and underscores allowed</small>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required minlength="6"
                       placeholder="Enter password">
                <small>Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="">Select a role...</option>
                    <option value="cashier">Cashier - Limited Access</option>
                    <option value="admin">Admin - Full Access</option>
                </select>
            </div>

            <div class="button-group">
                <a href="users.php" class="cancel-btn">Cancel</a>
                <button type="submit">Create User</button>
            </div>
        </form>
    </div>
</body>
</html>
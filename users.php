<?php
session_start();
include "db.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE username LIKE '%$search%' OR role LIKE '%$search%'" : "";
$users = $conn->query("SELECT * FROM users $where ORDER BY id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users - Coffee Shop TPS</title>
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
        }

        .header {
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(62, 39, 35, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 { 
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.6rem 1.4rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid rgba(255,255,255,0.3);
            font-size: 14px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.4);
        }

        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--coffee-dark);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--coffee-secondary);
            font-size: 15px;
        }

        .controls {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
        }

        .controls form {
            display: flex;
            gap: 1rem;
            flex: 1;
        }

        .controls input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .controls input:focus {
            outline: none;
            border-color: var(--coffee-primary);
        }

        .controls button, .add-btn {
            padding: 0.75rem 1.5rem;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .search-btn { 
            background: linear-gradient(135deg, var(--info) 0%, #1E88E5 100%);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.3);
        }

        .add-btn {
            background: linear-gradient(135deg, var(--success) 0%, #4CAF50 100%);
            display: inline-block;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
        }

        .panel {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #EFEBE9;
        }

        th {
            background: linear-gradient(135deg, #FAFAFA 0%, #F5F5F5 100%);
            color: var(--coffee-secondary);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background: #FFF8E1;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .role-badge {
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .role-badge.admin { 
            background: #FFEBEE; 
            color: #C62828; 
        }

        .role-badge.cashier { 
            background: #E3F2FD; 
            color: #1565C0; 
        }

        .action-btn {
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-block;
        }

        .edit-btn { 
            background: linear-gradient(135deg, var(--info) 0%, #1E88E5 100%);
            color: white; 
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(66, 165, 245, 0.3);
        }

        .delete-btn { 
            background: linear-gradient(135deg, var(--danger) 0%, #E53935 100%);
            color: white; 
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(239, 83, 80, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--coffee-secondary);
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .password-display {
            font-family: monospace;
            color: var(--coffee-secondary);
            letter-spacing: 0.2em;
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.15rem;
            }

            .container {
                padding: 1rem;
            }

            .controls {
                flex-direction: column;
            }

            .controls form {
                width: 100%;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 User Management</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Manage Users</h2>
            <p>Add, edit, or remove system users</p>
        </div>

        <div class="controls">
            <form method="GET">
                <input type="text" name="search" placeholder="Search users by username or role..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">🔍 Search</button>
            </form>
            <a href="add_user.php" class="add-btn">+ Add User</a>
        </div>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $user['id']; ?></strong></td>
                            <td><strong style="color: var(--coffee-dark);"><?php echo $user['username']; ?></strong></td>
                            <td><span class="password-display"><?php echo str_repeat('•', min(strlen($user['password']), 12)); ?></span></td>
                            <td><span class="role-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <?php if ($user['username'] != $_SESSION['username']): ?>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this user? This action cannot be undone.')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">👥</div>
                                    <h3>No users found</h3>
                                    <p>Try adjusting your search or add a new user</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
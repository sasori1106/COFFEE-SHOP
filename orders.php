<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Search and filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $conn->real_escape_string($_GET['filter']) : '';

$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (order_number LIKE '%$search%' OR customer_name LIKE '%$search%')";
}
if ($filter) {
    $where .= " AND order_status='$filter'";
}

$orders = $conn->query("SELECT * FROM orders $where ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders - Coffee Shop TPS</title>
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
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
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
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
        }

        .controls form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .controls input, .controls select {
            padding: 0.75rem 1rem;
            border: 2px solid #D7CCC8;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .controls input {
            flex: 1;
        }

        .controls input:focus, .controls select:focus {
            outline: none;
            border-color: var(--coffee-primary);
            box-shadow: 0 0 0 4px rgba(107, 68, 35, 0.1);
        }

        .controls button {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
        }

        .controls button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 68, 35, 0.3);
        }

        .orders-table {
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
            padding: 1rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid #EFEBE9;
        }

        th {
            background: #FAFAFA;
            font-weight: 600;
            color: var(--coffee-secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #FFF8E1;
        }

        .badge {
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge.completed { background: #E8F5E9; color: #2E7D32; }
        .badge.pending { background: #FFF3E0; color: #E65100; }
        .badge.cancelled { background: #FFEBEE; color: #C62828; }

        .action-btn {
            padding: 0.4rem 0.75rem;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
            transition: all 0.2s;
            display: inline-block;
        }

        .view-btn {
            background: #E3F2FD;
            color: #1565C0;
        }

        .view-btn:hover {
            background: #1E88E5;
            color: white;
            transform: translateY(-1px);
        }

        .cancel-btn {
            background: #FFEBEE;
            color: #C62828;
        }

        .cancel-btn:hover {
            background: #EF5350;
            color: white;
            transform: translateY(-1px);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--coffee-secondary);
        }

        .no-data .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .controls form {
                flex-direction: column;
            }

            .controls input, .controls select, .controls button {
                width: 100%;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Order Records</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Order Management</h2>
            <p>View and manage all customer orders</p>
        </div>

        <div class="controls">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by order number or customer name..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="filter">
                    <option value="">All Status</option>
                    <option value="completed" <?php echo $filter=='completed'?'selected':''; ?>>Completed</option>
                    <option value="pending" <?php echo $filter=='pending'?'selected':''; ?>>Pending</option>
                    <option value="cancelled" <?php echo $filter=='cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
                <button type="submit">🔍 Search</button>
            </form>
        </div>

        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Cashier</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                            <td><?php echo $order['customer_name'] ?: 'Walk-in'; ?></td>
                            <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            <td><?php echo $order['payment_method']; ?></td>
                            <td><span class="badge <?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                            <td><?php echo $order['cashier_username']; ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></td>
                            <td>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">👁️ View</a>
                                <?php if ($order['order_status'] == 'pending'): ?>
                                <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="action-btn cancel-btn" onclick="return confirm('Cancel this order?')">❌ Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="no-data">
                                    <div class="icon">📋</div>
                                    <p>No orders found</p>
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
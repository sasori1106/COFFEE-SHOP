<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Get today's sales statistics
$today = date('Y-m-d');
$sales_result = $conn->query("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_sales FROM orders WHERE DATE(order_date) = '$today' AND order_status = 'completed'");
$sales_data = $sales_result->fetch_assoc();

// Get product count
$product_count = $conn->query("SELECT COUNT(*) as count FROM products WHERE status='available'")->fetch_assoc()['count'];

// Get low stock items
$low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity < 10")->fetch_assoc()['count'];

// Get pending orders
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='pending'")->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Coffee Shop TPS</title>
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

        /* Header */
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
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header h1 { 
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-info { 
            display: flex; 
            align-items: center; 
            gap: 1.5rem;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.15);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFE0B2 0%, #FFCC80 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--coffee-dark);
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #EF5350 0%, #E53935 100%);
            color: white;
            padding: 0.6rem 1.4rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
            font-size: 14px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 83, 80, 0.4);
            border-color: rgba(255,255,255,0.3);
        }

        /* Layout */
        .container {
            display: flex;
            min-height: calc(100vh - 73px);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            padding: 1.5rem 0;
            box-shadow: 2px 0 15px rgba(62, 39, 35, 0.08);
            position: sticky;
            top: 73px;
            height: calc(100vh - 73px);
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0 1.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--coffee-secondary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            color: #5D4037;
            padding: 0.9rem 1.5rem;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
            border-left: 3px solid transparent;
            font-size: 15px;
        }
        
        .sidebar a:hover {
            background: linear-gradient(90deg, #FFF8E1 0%, #FFECB3 100%);
            color: var(--coffee-primary);
            border-left-color: var(--coffee-accent);
        }

        .sidebar a.active {
            background: linear-gradient(90deg, #EFEBE9 0%, #D7CCC8 100%);
            color: var(--coffee-primary);
            border-left-color: var(--coffee-primary);
            font-weight: 600;
        }

        .sidebar a .icon {
            font-size: 1.3rem;
            width: 28px;
            text-align: center;
        }

        /* Main Content */
        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
        }

        .stat-card.sales::before { background: linear-gradient(90deg, #66BB6A, #4CAF50); }
        .stat-card.orders::before { background: linear-gradient(90deg, #42A5F5, #1E88E5); }
        .stat-card.products::before { background: linear-gradient(90deg, var(--coffee-secondary), var(--coffee-primary)); }
        .stat-card.alert::before { background: linear-gradient(90deg, #FFA726, #F57C00); }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(62, 39, 35, 0.15);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.2rem;
        }

        .stat-card h3 {
            color: var(--coffee-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .stat-card.sales .stat-icon { background: #E8F5E9; color: #4CAF50; }
        .stat-card.orders .stat-icon { background: #E3F2FD; color: #1E88E5; }
        .stat-card.products .stat-icon { background: #EFEBE9; color: var(--coffee-primary); }
        .stat-card.alert .stat-icon { background: #FFF3E0; color: #F57C00; }
        
        .stat-card .value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--coffee-dark);
            line-height: 1;
        }

        .stat-card .trend {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.7rem;
            font-size: 0.85rem;
            color: #4CAF50;
            font-weight: 500;
        }

        /* Content Panels */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .panel {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.2rem;
            border-bottom: 2px solid #EFEBE9;
        }

        .panel-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--coffee-dark);
        }

        .panel-link {
            color: var(--coffee-primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .panel-link:hover {
            color: var(--coffee-secondary);
        }

        /* Tables */
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

        /* Quick Actions */
        .action-card {
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 14px;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(107, 68, 35, 0.3);
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(107, 68, 35, 0.4);
        }

        .action-card .icon {
            font-size: 2.2rem;
        }

        .action-card h3 {
            font-size: 1.05rem;
            font-weight: 600;
        }

        .action-card p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .action-card.success {
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
        }

        .action-card.warning {
            background: linear-gradient(135deg, #FFA726 0%, #F57C00 100%);
        }

        .alert-box {
            background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
            color: #C62828;
            padding: 1.2rem;
            border-radius: 12px;
            text-decoration: none;
            border: 2px solid #EF9A9A;
            display: block;
        }

        .alert-box:hover {
            background: linear-gradient(135deg, #FFCDD2 0%, #EF9A9A 100%);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .header {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.15rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 1rem;
            }

            .user-badge span:last-child {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>☕ Coffee Shop TPS</h1>
        </div>
        <div class="user-info">
            <div class="user-badge">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <div>
                    <div style="font-weight: 600; font-size: 14px;"><?php echo $_SESSION['username']; ?></div>
                    <div style="font-size: 0.75rem; opacity: 0.9;"><?php echo ucfirst($_SESSION['role']); ?></div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <div class="nav-section">
                <div class="nav-section-title">Main Menu</div>
                <a href="dashboard.php" class="active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="new_order.php">
                    <span class="icon">🛒</span>
                    <span>New Order</span>
                </a>
                <a href="orders.php">
                    <span class="icon">📋</span>
                    <span>Orders</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Inventory</div>
                <a href="products.php">
                    <span class="icon">☕</span>
                    <span>Products</span>
                </a>
                <a href="inventory.php">
                    <span class="icon">📦</span>
                    <span>Stock Management</span>
                </a>
            </div>

            <?php if ($_SESSION['role'] == 'admin'): ?>
            <div class="nav-section">
                <div class="nav-section-title">Administration</div>
                <a href="reports.php">
                    <span class="icon">📈</span>
                    <span>Reports</span>
                </a>
                <a href="users.php">
                    <span class="icon">👥</span>
                    <span>User Management</span>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="content">
            <div class="page-header">
                <h2>Dashboard Overview</h2>
                <p>Welcome back, <?php echo $_SESSION['username']; ?>! Here's what's happening today.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card sales">
                    <div class="stat-card-header">
                        <h3>Today's Sales</h3>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="value">₱<?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></div>
                    <div class="trend">
                        <span>↑ Growing</span>
                    </div>
                </div>

                <div class="stat-card orders">
                    <div class="stat-card-header">
                        <h3>Orders Today</h3>
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="value"><?php echo $sales_data['total_orders'] ?? 0; ?></div>
                    <div class="trend">
                        <span>Orders completed</span>
                    </div>
                </div>

                <div class="stat-card products">
                    <div class="stat-card-header">
                        <h3>Available Products</h3>
                        <div class="stat-icon">☕</div>
                    </div>
                    <div class="value"><?php echo $product_count; ?></div>
                    <div class="trend" style="color: var(--coffee-secondary);">
                        <span>Active in menu</span>
                    </div>
                </div>

                <div class="stat-card alert">
                    <div class="stat-card-header">
                        <h3>Low Stock Alert</h3>
                        <div class="stat-icon">⚠️</div>
                    </div>
                    <div class="value"><?php echo $low_stock; ?></div>
                    <div class="trend" style="color: var(--coffee-secondary);">
                        <span>Items need restock</span>
                    </div>
                </div>
            </div>

            <div class="content-grid">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Recent Orders</h2>
                        <a href="orders.php" class="panel-link">View All →</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders->num_rows > 0): ?>
                                <?php while($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                                    <td><?php echo $order['customer_name'] ?: 'Walk-in'; ?></td>
                                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    <td><span class="badge <?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                    <td><?php echo date('h:i A', strtotime($order['order_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--coffee-secondary); padding: 2rem;">No orders yet today</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div>
                        <a href="new_order.php" class="action-card success">
                            <span style="font-size: 2.2rem;">🛒</span>
                            <div>
                                <h3>Create New Order</h3>
                                <p>Start a new transaction</p>
                            </div>
                        </a>

                        <a href="inventory.php" class="action-card warning">
                            <span style="font-size: 2.2rem;">📦</span>
                            <div>
                                <h3>Manage Inventory</h3>
                                <p>Update stock levels</p>
                            </div>
                        </a>

                        <?php if ($low_stock > 0): ?>
                        <a href="inventory.php" class="alert-box">
                            <div style="font-weight: 600; margin-bottom: 0.3rem;">⚠️ Stock Alert</div>
                            <div style="font-size: 0.875rem;"><?php echo $low_stock; ?> items running low</div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
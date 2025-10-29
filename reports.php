<?php
session_start();
include "db.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Date range filter
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-7 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Sales Summary
$sales_summary = $conn->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_sales,
        AVG(total_amount) as avg_order_value,
        SUM(CASE WHEN order_status='completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN order_status='cancelled' THEN 1 ELSE 0 END) as cancelled_orders
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$date_from' AND '$date_to'
")->fetch_assoc();

// Daily Sales
$daily_sales = $conn->query("
    SELECT 
        DATE(order_date) as sale_date,
        COUNT(*) as orders,
        SUM(total_amount) as sales
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$date_from' AND '$date_to' 
    AND order_status = 'completed'
    GROUP BY DATE(order_date)
    ORDER BY sale_date DESC
");

// Top Products
$top_products = $conn->query("
    SELECT 
        oi.product_name,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.subtotal) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.order_date) BETWEEN '$date_from' AND '$date_to'
    AND o.order_status = 'completed'
    GROUP BY oi.product_name
    ORDER BY total_revenue DESC
    LIMIT 10
");

// Payment Method Distribution
$payment_methods = $conn->query("
    SELECT 
        payment_method,
        COUNT(*) as count,
        SUM(total_amount) as total
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$date_from' AND '$date_to'
    AND order_status = 'completed'
    GROUP BY payment_method
    ORDER BY total DESC
");

// Category Performance
$category_performance = $conn->query("
    SELECT 
        p.category,
        SUM(oi.quantity) as items_sold,
        SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.order_date) BETWEEN '$date_from' AND '$date_to'
    AND o.order_status = 'completed'
    GROUP BY p.category
    ORDER BY revenue DESC
");

// Cashier Performance
$cashier_performance = $conn->query("
    SELECT 
        cashier_username,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_sales
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$date_from' AND '$date_to'
    AND order_status = 'completed'
    GROUP BY cashier_username
    ORDER BY total_sales DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - Coffee Shop TPS</title>
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

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--coffee-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group input {
            padding: 0.75rem 1rem;
            border: 2px solid #D7CCC8;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--coffee-primary);
            box-shadow: 0 0 0 4px rgba(107, 68, 35, 0.1);
        }

        .filter-btn {
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 68, 35, 0.3);
        }

        .export-btn {
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
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

        .stat-card.primary::before { background: linear-gradient(90deg, var(--coffee-secondary), var(--coffee-primary)); }
        .stat-card.success::before { background: linear-gradient(90deg, #66BB6A, #4CAF50); }
        .stat-card.info::before { background: linear-gradient(90deg, #42A5F5, #1E88E5); }
        .stat-card.warning::before { background: linear-gradient(90deg, #FFA726, #F57C00); }
        .stat-card.danger::before { background: linear-gradient(90deg, #EF5350, #E53935); }

        .stat-card h3 {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--coffee-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--coffee-dark);
            line-height: 1;
        }

        .stat-card .label {
            font-size: 0.85rem;
            color: var(--coffee-secondary);
            margin-top: 0.5rem;
        }

        /* Report Panels */
        .report-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .report-panel {
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
            padding-bottom: 1rem;
            border-bottom: 2px solid #EFEBE9;
        }

        .panel-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--coffee-dark);
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
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

        /* Progress Bar */
        .progress-bar {
            background: #EFEBE9;
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--coffee-primary) 0%, var(--coffee-secondary) 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        /* Charts Placeholder */
        .chart-container {
            background: #FAFAFA;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: var(--coffee-secondary);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
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

        /* Badges */
        .badge {
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge.primary { background: #EFEBE9; color: var(--coffee-primary); }
        .badge.success { background: #E8F5E9; color: #2E7D32; }
        .badge.warning { background: #FFF3E0; color: #E65100; }

        /* Print Styles */
        @media print {
            .header, .filter-section, .export-btn {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .report-panel {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📈 Sales Reports & Analytics</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Business Intelligence</h2>
            <p>Comprehensive reports and analytics for your coffee shop</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-grid">
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>" required>
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>" required>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="filter-btn">📊 Generate Report</button>
                    <button type="button" onclick="window.print()" class="export-btn">🖨️ Print</button>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>Total Sales</h3>
                <div class="value">₱<?php echo number_format($sales_summary['total_sales'] ?? 0, 2); ?></div>
                <div class="label">Revenue Generated</div>
            </div>

            <div class="stat-card info">
                <h3>Total Orders</h3>
                <div class="value"><?php echo $sales_summary['total_orders'] ?? 0; ?></div>
                <div class="label">All Transactions</div>
            </div>

            <div class="stat-card success">
                <h3>Completed Orders</h3>
                <div class="value"><?php echo $sales_summary['completed_orders'] ?? 0; ?></div>
                <div class="label">Successful Transactions</div>
            </div>

            <div class="stat-card warning">
                <h3>Average Order Value</h3>
                <div class="value">₱<?php echo number_format($sales_summary['avg_order_value'] ?? 0, 2); ?></div>
                <div class="label">Per Transaction</div>
            </div>

            <div class="stat-card danger">
                <h3>Cancelled Orders</h3>
                <div class="value"><?php echo $sales_summary['cancelled_orders'] ?? 0; ?></div>
                <div class="label">Unsuccessful Transactions</div>
            </div>
        </div>

        <!-- Daily Sales Report -->
        <div class="report-grid">
            <div class="report-panel">
                <div class="panel-header">
                    <h3>📅 Daily Sales Performance</h3>
                </div>
                <div class="table-container">
                    <?php if ($daily_sales->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Total Sales</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $max_sales = 0;
                            $daily_data = [];
                            while($day = $daily_sales->fetch_assoc()) {
                                $daily_data[] = $day;
                                if ($day['sales'] > $max_sales) $max_sales = $day['sales'];
                            }
                            foreach($daily_data as $day): 
                                $percentage = $max_sales > 0 ? ($day['sales'] / $max_sales) * 100 : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo date('M d, Y', strtotime($day['sale_date'])); ?></strong></td>
                                <td><?php echo $day['orders']; ?> orders</td>
                                <td><strong>₱<?php echo number_format($day['sales'], 2); ?></strong></td>
                                <td style="width: 200px;">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <div class="icon">📊</div>
                        <p>No sales data for selected period</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Products -->
            <div class="report-panel">
                <div class="panel-header">
                    <h3>🏆 Top Selling Products</h3>
                </div>
                <div class="table-container">
                    <?php if ($top_products->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product</th>
                                <th>Quantity Sold</th>
                                <th>Revenue</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            while($product = $top_products->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <?php if($rank == 1): ?>
                                        <span style="font-size: 1.5rem;">🥇</span>
                                    <?php elseif($rank == 2): ?>
                                        <span style="font-size: 1.5rem;">🥈</span>
                                    <?php elseif($rank == 3): ?>
                                        <span style="font-size: 1.5rem;">🥉</span>
                                    <?php else: ?>
                                        <strong><?php echo $rank; ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $product['product_name']; ?></strong></td>
                                <td><?php echo $product['total_quantity']; ?> units</td>
                                <td><strong>₱<?php echo number_format($product['total_revenue'], 2); ?></strong></td>
                                <td><?php echo $product['order_count']; ?></td>
                            </tr>
                            <?php 
                            $rank++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <div class="icon">☕</div>
                        <p>No product sales for selected period</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Category Performance -->
            <div class="report-panel">
                <div class="panel-header">
                    <h3>📊 Category Performance</h3>
                </div>
                <div class="table-container">
                    <?php if ($category_performance->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Items Sold</th>
                                <th>Revenue</th>
                                <th>Market Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_category_revenue = 0;
                            $category_data = [];
                            while($cat = $category_performance->fetch_assoc()) {
                                $category_data[] = $cat;
                                $total_category_revenue += $cat['revenue'];
                            }
                            foreach($category_data as $cat): 
                                $share = $total_category_revenue > 0 ? ($cat['revenue'] / $total_category_revenue) * 100 : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo $cat['category']; ?></strong></td>
                                <td><?php echo $cat['items_sold']; ?> units</td>
                                <td><strong>₱<?php echo number_format($cat['revenue'], 2); ?></strong></td>
                                <td>
                                    <span class="badge primary"><?php echo number_format($share, 1); ?>%</span>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $share; ?>%;"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <div class="icon">📦</div>
                        <p>No category data for selected period</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="report-panel">
                <div class="panel-header">
                    <h3>💳 Payment Method Distribution</h3>
                </div>
                <div class="table-container">
                    <?php if ($payment_methods->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Transactions</th>
                                <th>Total Amount</th>
                                <th>Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_payment_amount = 0;
                            $payment_data = [];
                            while($pm = $payment_methods->fetch_assoc()) {
                                $payment_data[] = $pm;
                                $total_payment_amount += $pm['total'];
                            }
                            foreach($payment_data as $pm): 
                                $distribution = $total_payment_amount > 0 ? ($pm['total'] / $total_payment_amount) * 100 : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo $pm['payment_method']; ?></strong></td>
                                <td><?php echo $pm['count']; ?> orders</td>
                                <td><strong>₱<?php echo number_format($pm['total'], 2); ?></strong></td>
                                <td>
                                    <span class="badge success"><?php echo number_format($distribution, 1); ?>%</span>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $distribution; ?>%;"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <div class="icon">💰</div>
                        <p>No payment data for selected period</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cashier Performance -->
            <div class="report-panel">
                <div class="panel-header">
                    <h3>👥 Cashier Performance</h3>
                </div>
                <div class="table-container">
                    <?php if ($cashier_performance->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Cashier</th>
                                <th>Orders Processed</th>
                                <th>Total Sales</th>
                                <th>Average Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cashier = $cashier_performance->fetch_assoc()): 
                                $avg = $cashier['total_orders'] > 0 ? $cashier['total_sales'] / $cashier['total_orders'] : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo $cashier['cashier_username']; ?></strong></td>
                                <td><?php echo $cashier['total_orders']; ?> orders</td>
                                <td><strong>₱<?php echo number_format($cashier['total_sales'], 2); ?></strong></td>
                                <td>₱<?php echo number_format($avg, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <div class="icon">👤</div>
                        <p>No cashier data for selected period</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
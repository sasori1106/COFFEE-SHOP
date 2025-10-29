<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$order = $conn->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Order - Coffee Shop TPS</title>
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
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .container { 
            padding: 2rem; 
            max-width: 1200px; 
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

        .panel {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
            margin-bottom: 1.5rem;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: #FAFAFA;
            padding: 1.2rem;
            border-radius: 12px;
            border-left: 4px solid var(--coffee-primary);
        }

        .info-item label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--coffee-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-item .value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--coffee-dark);
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge.completed { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .badge.pending { 
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }

        .badge.cancelled { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.2rem;
            border-bottom: 2px solid #EFEBE9;
        }

        .panel-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--coffee-dark);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        thead {
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:first-child {
            border-radius: 12px 0 0 0;
        }

        th:last-child {
            border-radius: 0 12px 0 0;
        }

        td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #EFEBE9;
            color: var(--coffee-dark);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: #FAFAFA;
        }

        .total-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #EFEBE9 0%, #E0E0E0 100%);
            border-radius: 12px;
            text-align: right;
        }

        .total-section h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--coffee-dark);
        }

        .total-section .amount {
            color: var(--success);
            font-size: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--info) 0%, #1E88E5 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 165, 245, 0.4);
        }

        @media print {
            .header, .back-btn, .action-buttons {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .panel {
                box-shadow: none;
                border: 1px solid #ddd;
            }
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

            .order-info {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📄 Order Details</h1>
        <a href="orders.php" class="back-btn">← Back to Orders</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Order #<?php echo $order['order_number']; ?></h2>
            <p>Complete order information and items</p>
        </div>

        <div class="panel">
            <div class="order-info">
                <div class="info-item">
                    <label>Order Number</label>
                    <div class="value"><?php echo $order['order_number']; ?></div>
                </div>
                <div class="info-item">
                    <label>Customer</label>
                    <div class="value"><?php echo $order['customer_name'] ?: 'Walk-in'; ?></div>
                </div>
                <div class="info-item">
                    <label>Order Date</label>
                    <div class="value"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></div>
                </div>
                <div class="info-item">
                    <label>Payment Method</label>
                    <div class="value"><?php echo $order['payment_method']; ?></div>
                </div>
                <div class="info-item">
                    <label>Cashier</label>
                    <div class="value"><?php echo $order['cashier_username']; ?></div>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <div class="value">
                        <span class="badge <?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h3>Order Items</h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td style="font-weight: 700; color: var(--success);">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="total-section">
                <h3>Total Amount: <span class="amount">₱<?php echo number_format($order['total_amount'], 2); ?></span></h3>
            </div>
        </div>

        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Order</button>
        </div>
    </div>
</body>
</html>
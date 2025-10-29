<?php
session_start();
include "db.php";

if (!isset($_SESSION['username']) || !isset($_GET['order'])) {
    header("Location: dashboard.php");
    exit();
}

$order_number = $conn->real_escape_string($_GET['order']);
$order_result = $conn->query("SELECT * FROM orders WHERE order_number='$order_number'");
$order = $order_result->fetch_assoc();

$items_result = $conn->query("SELECT * FROM order_items WHERE order_id={$order['id']}");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Success - Coffee Shop TPS</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .receipt {
            background: white;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(62, 39, 35, 0.15);
            border: 1px solid #EFEBE9;
        }

        .success-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--success) 0%, #4CAF50 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
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

        .order-info {
            background: linear-gradient(135deg, #EFEBE9 0%, #F5F5F5 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 2px solid #D7CCC8;
        }

        .order-info p {
            margin-bottom: 0.75rem;
            color: var(--coffee-dark);
            font-size: 0.95rem;
            display: flex;
            justify-content: space-between;
        }

        .order-info p:last-child {
            margin-bottom: 0;
        }

        .order-info strong { 
            color: var(--coffee-secondary);
            font-weight: 600;
        }

        .order-number {
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .items-list {
            border-top: 2px dashed var(--coffee-light);
            border-bottom: 2px dashed var(--coffee-light);
            padding: 1.5rem 0;
            margin: 1.5rem 0;
        }

        .items-header {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--coffee-dark);
            margin-bottom: 1rem;
        }

        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.875rem;
            padding: 0.5rem 0;
        }

        .item-name {
            color: var(--coffee-dark);
            font-weight: 500;
        }

        .item-price {
            color: var(--coffee-secondary);
            font-weight: 600;
        }

        .total-section {
            background: linear-gradient(135deg, var(--coffee-primary) 0%, var(--coffee-dark) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            text-align: center;
        }

        .total-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .total-amount {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--info) 0%, #1E88E5 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 165, 245, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #4CAF50 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 187, 106, 0.4);
        }

        @media print {
            body {
                background: white;
            }
            .actions {
                display: none;
            }
            .receipt {
                box-shadow: none;
                border: none;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .receipt {
                padding: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .total-amount {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="success-header">
            <div class="success-icon">✓</div>
            <h2>Order Successful!</h2>
            <p class="subtitle">Thank you for your purchase</p>
        </div>
        
        <div class="order-info">
            <p>
                <strong>Order Number:</strong>
                <span class="order-number"><?php echo $order['order_number']; ?></span>
            </p>
            <p>
                <strong>Customer:</strong>
                <span><?php echo $order['customer_name'] ?: 'Walk-in'; ?></span>
            </p>
            <p>
                <strong>Date & Time:</strong>
                <span><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></span>
            </p>
            <p>
                <strong>Payment:</strong>
                <span><?php echo $order['payment_method']; ?></span>
            </p>
            <p>
                <strong>Cashier:</strong>
                <span><?php echo $order['cashier_username']; ?></span>
            </p>
        </div>

        <div class="items-list">
            <div class="items-header">Order Items</div>
            <?php while($item = $items_result->fetch_assoc()): ?>
            <div class="item">
                <span class="item-name">
                    <?php echo $item['quantity']; ?>x <?php echo $item['product_name']; ?>
                </span>
                <span class="item-price">₱<?php echo number_format($item['subtotal'], 2); ?></span>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="total-section">
            <div class="total-label">Total Amount</div>
            <div class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></div>
        </div>

        <div class="actions">
            <a href="new_order.php" class="btn btn-success">New Order</a>
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
        </div>
    </div>

    <script>
        // Auto print receipt
        setTimeout(() => {
            if (confirm('Print receipt?')) {
                window.print();
            }
        }, 500);
    </script>
</body>
</html>
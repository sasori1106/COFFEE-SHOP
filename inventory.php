<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Handle stock adjustment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adjust_stock'])) {
    $product_id = intval($_POST['product_id']);
    $adjustment = intval($_POST['adjustment']);
    $type = $conn->real_escape_string($_POST['type']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $conn->query("UPDATE products SET stock_quantity = stock_quantity + $adjustment WHERE id = $product_id");
    
    $conn->query("INSERT INTO inventory_logs (product_id, transaction_type, quantity_change, notes, created_by) 
                 VALUES ($product_id, '$type', $adjustment, '$notes', '{$_SESSION['username']}')");
    
    header("Location: inventory.php");
    exit();
}

$products = $conn->query("SELECT * FROM products ORDER BY stock_quantity ASC");
$recent_logs = $conn->query("SELECT il.*, p.name as product_name 
                            FROM inventory_logs il 
                            JOIN products p ON il.product_id = p.id 
                            ORDER BY il.created_at DESC LIMIT 20");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory - Coffee Shop TPS</title>
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
            max-width: 1600px;
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

        .grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #EFEBE9;
        }

        th {
            background: #FAFAFA;
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

        .low-stock { 
            color: var(--danger);
            font-weight: 700;
        }

        .adjust-btn {
            background: linear-gradient(135deg, var(--info) 0%, #1E88E5 100%);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .adjust-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(66, 165, 245, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(62, 39, 35, 0.6);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            max-width: 500px;
            margin: 80px auto;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(62, 39, 35, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h2 {
            color: var(--coffee-dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--coffee-dark);
            font-size: 0.9rem;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--coffee-primary);
        }

        .form-group input[readonly] {
            background: #FAFAFA;
            color: var(--coffee-secondary);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .button-group button {
            flex: 1;
            padding: 0.9rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .save-btn { 
            background: linear-gradient(135deg, var(--success) 0%, #4CAF50 100%);
            color: white; 
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
        }

        .cancel-btn { 
            background: #EFEBE9;
            color: var(--coffee-dark);
        }

        .cancel-btn:hover {
            background: var(--coffee-light);
        }

        .log-type {
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .log-type.restock { 
            background: #E8F5E9; 
            color: #2E7D32; 
        }

        .log-type.sale { 
            background: #FFF3E0; 
            color: #E65100; 
        }

        .log-type.adjustment { 
            background: #E3F2FD; 
            color: #1565C0; 
        }

        .scrollable-table {
            max-height: 500px;
            overflow-y: auto;
        }

        .scrollable-table::-webkit-scrollbar {
            width: 6px;
        }

        .scrollable-table::-webkit-scrollbar-track {
            background: #EFEBE9;
            border-radius: 10px;
        }

        .scrollable-table::-webkit-scrollbar-thumb {
            background: var(--coffee-accent);
            border-radius: 10px;
        }

        @media (max-width: 1024px) {
            .grid {
                grid-template-columns: 1fr;
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
        <h1>📦 Inventory Management</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Stock Management</h2>
            <p>Monitor and adjust product inventory levels</p>
        </div>

        <div class="grid">
            <div class="panel">
                <div class="panel-header">
                    <h2>Current Stock Levels</h2>
                </div>
                <div class="scrollable-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><strong style="color: var(--coffee-dark);"><?php echo $product['name']; ?></strong></td>
                                <td><?php echo $product['category']; ?></td>
                                <td class="<?php echo $product['stock_quantity'] < 10 ? 'low-stock' : ''; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                    <?php if ($product['stock_quantity'] < 10): ?>⚠️<?php endif; ?>
                                </td>
                                <td>
                                    <button class="adjust-btn" onclick="openAdjustModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        Adjust
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Activity</h2>
                </div>
                <div class="scrollable-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Change</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($log = $recent_logs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $log['product_name']; ?></td>
                                <td><span class="log-type <?php echo $log['transaction_type']; ?>"><?php echo ucfirst($log['transaction_type']); ?></span></td>
                                <td style="font-weight: 600; color: <?php echo $log['quantity_change'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>">
                                    <?php echo ($log['quantity_change'] > 0 ? '+' : '') . $log['quantity_change']; ?>
                                </td>
                                <td><?php echo date('M d, h:i A', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="adjustModal" class="modal">
        <div class="modal-content">
            <h2>Adjust Stock</h2>
            <form method="POST">
                <input type="hidden" name="product_id" id="product_id">
                
                <div class="form-group">
                    <label>Product:</label>
                    <input type="text" id="product_name" readonly>
                </div>

                <div class="form-group">
                    <label>Current Stock:</label>
                    <input type="number" id="current_stock" readonly>
                </div>

                <div class="form-group">
                    <label>Transaction Type:</label>
                    <select name="type" required>
                        <option value="restock">Restock (Add Stock)</option>
                        <option value="adjustment">Adjustment (Add/Remove)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Adjustment Amount:</label>
                    <input type="number" name="adjustment" placeholder="Positive to add, negative to remove" required>
                </div>

                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes" rows="3" placeholder="Reason for adjustment..."></textarea>
                </div>

                <div class="button-group">
                    <button type="button" class="cancel-btn" onclick="closeAdjustModal()">Cancel</button>
                    <button type="submit" name="adjust_stock" class="save-btn">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAdjustModal(product) {
            document.getElementById('product_id').value = product.id;
            document.getElementById('product_name').value = product.name;
            document.getElementById('current_stock').value = product.stock_quantity;
            document.getElementById('adjustModal').style.display = 'block';
        }

        function closeAdjustModal() {
            document.getElementById('adjustModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('adjustModal');
            if (event.target == modal) {
                closeAdjustModal();
            }
        }
    </script>
</body>
</html>
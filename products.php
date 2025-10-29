<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE name LIKE '%$search%' OR category LIKE '%$search%'" : "";
$products = $conn->query("SELECT * FROM products $where ORDER BY category, name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - Coffee Shop TPS</title>
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

        /* Controls */
        .controls {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(62, 39, 35, 0.08);
            border: 1px solid #EFEBE9;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .controls form {
            display: flex;
            gap: 1rem;
            flex: 1;
        }

        .controls input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #D7CCC8;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .controls input:focus {
            outline: none;
            border-color: var(--coffee-primary);
            box-shadow: 0 0 0 4px rgba(107, 68, 35, 0.1);
        }

        .search-btn {
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
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 68, 35, 0.3);
        }

        .add-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
        }

        /* Products Table */
        .products-table {
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

        .status-badge {
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.available { 
            background: #E8F5E9; 
            color: #2E7D32; 
        }

        .status-badge.unavailable { 
            background: #FFEBEE; 
            color: #C62828; 
        }

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

        .edit-btn { 
            background: #E3F2FD;
            color: #1565C0;
        }

        .edit-btn:hover {
            background: #1E88E5;
            color: white;
            transform: translateY(-1px);
        }

        .delete-btn { 
            background: #FFEBEE;
            color: #C62828;
        }

        .delete-btn:hover {
            background: #EF5350;
            color: white;
            transform: translateY(-1px);
        }

        .low-stock { 
            color: #EF5350; 
            font-weight: 700;
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

            .controls {
                flex-direction: column;
            }

            .controls form {
                width: 100%;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }

            .header h1 {
                font-size: 1.15rem;
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
        <h1>☕ Product Management</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Products Catalog</h2>
            <p>Manage your coffee shop menu items and inventory</p>
        </div>

        <div class="controls">
            <form method="GET">
                <input type="text" name="search" placeholder="Search products by name or category..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">🔍 Search</button>
            </form>
            <a href="add_product.php" class="add-btn">+ Add Product</a>
        </div>

        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $product['id']; ?></strong></td>
                            <td><strong><?php echo $product['name']; ?></strong></td>
                            <td><?php echo $product['category']; ?></td>
                            <td><strong>₱<?php echo number_format($product['price'], 2); ?></strong></td>
                            <td class="<?php echo $product['stock_quantity'] < 10 ? 'low-stock' : ''; ?>">
                                <?php echo $product['stock_quantity']; ?> units
                                <?php if ($product['stock_quantity'] < 10): ?>
                                    ⚠️
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge <?php echo $product['status']; ?>"><?php echo ucfirst($product['status']); ?></span></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this product? This action cannot be undone.')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="no-data">
                                    <div class="icon">☕</div>
                                    <p>No products found</p>
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
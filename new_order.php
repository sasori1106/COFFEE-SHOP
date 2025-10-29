<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order'])) {
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $order_items = json_decode($_POST['order_items'], true);
    
    if (!empty($order_items)) {
        $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $total_amount = 0;
        foreach ($order_items as $item) {
            $total_amount += $item['subtotal'];
        }
        
        $sql = "INSERT INTO orders (order_number, customer_name, total_amount, payment_method, order_status, cashier_username) 
                VALUES ('$order_number', '$customer_name', $total_amount, '$payment_method', 'completed', '{$_SESSION['username']}')";
        
        if ($conn->query($sql)) {
            $order_id = $conn->insert_id;
            
            foreach ($order_items as $item) {
                $product_id = $item['product_id'];
                $product_name = $conn->real_escape_string($item['product_name']);
                $quantity = $item['quantity'];
                $unit_price = $item['unit_price'];
                $subtotal = $item['subtotal'];
                
                $conn->query("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) 
                             VALUES ($order_id, $product_id, '$product_name', $quantity, $unit_price, $subtotal)");
                
                $conn->query("UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE id = $product_id");
                
                $conn->query("INSERT INTO inventory_logs (product_id, transaction_type, quantity_change, notes, created_by) 
                             VALUES ($product_id, 'sale', -$quantity, 'Order: $order_number', '{$_SESSION['username']}')");
            }
            
            header("Location: order_success.php?order=$order_number");
            exit();
        }
    }
}

$products = $conn->query("SELECT * FROM products WHERE status='available' AND stock_quantity > 0 ORDER BY category, name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Order - Coffee Shop TPS</title>
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

        .order-grid { 
            display: grid; 
            grid-template-columns: 1.8fr 1fr; 
            gap: 1.5rem; 
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

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .product-grid::-webkit-scrollbar {
            width: 6px;
        }

        .product-grid::-webkit-scrollbar-track {
            background: #EFEBE9;
            border-radius: 10px;
        }

        .product-grid::-webkit-scrollbar-thumb {
            background: var(--coffee-accent);
            border-radius: 10px;
        }

        .product-card {
            border: 2px solid #EFEBE9;
            border-radius: 12px;
            padding: 0;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--coffee-primary), var(--coffee-secondary));
            transform: scaleX(0);
            transition: transform 0.3s;
            z-index: 1;
        }

        .product-card:hover {
            border-color: var(--coffee-primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(107, 68, 35, 0.15);
        }

        .product-card:hover::before {
            transform: scaleX(1);
        }

        .product-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            background: linear-gradient(135deg, #EFEBE9 0%, #E0E0E0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 1rem;
        }

        .product-card h3 { 
            font-size: 0.95rem; 
            margin-bottom: 0.5rem;
            color: var(--coffee-dark);
            font-weight: 600;
        }

        .product-card .price { 
            color: var(--success); 
            font-weight: 700; 
            font-size: 1.1rem; 
            margin-bottom: 0.3rem;
        }

        .product-card .stock { 
            color: var(--coffee-secondary); 
            font-size: 0.75rem;
            background: #EFEBE9;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            display: inline-block;
        }

        .cart-items { 
            max-height: 400px; 
            overflow-y: auto; 
            margin-bottom: 1.5rem;
            padding-right: 0.5rem;
        }

        .cart-items::-webkit-scrollbar {
            width: 6px;
        }

        .cart-items::-webkit-scrollbar-track {
            background: #EFEBE9;
            border-radius: 10px;
        }

        .cart-items::-webkit-scrollbar-thumb {
            background: var(--coffee-accent);
            border-radius: 10px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #EFEBE9;
            background: #FAFAFA;
            margin-bottom: 0.5rem;
            border-radius: 8px;
        }

        .cart-item .name { 
            font-weight: 600; 
            flex: 1;
            color: var(--coffee-dark);
        }

        .cart-item .qty-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .cart-item button {
            background: linear-gradient(135deg, var(--info) 0%, #1E88E5 100%);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .cart-item button:hover {
            transform: scale(1.1);
        }

        .cart-item button.remove { 
            background: linear-gradient(135deg, var(--danger) 0%, #E53935 100%);
        }

        .cart-total {
            border-top: 3px solid var(--coffee-dark);
            padding-top: 1.2rem;
            margin-top: 1.2rem;
        }

        .cart-total h3 { 
            font-size: 1.75rem; 
            text-align: right;
            color: var(--coffee-dark);
            font-weight: 700;
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--coffee-primary);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--success) 0%, #4CAF50 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .submit-btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 187, 106, 0.4);
        }

        .submit-btn:disabled {
            background: #BCAAA4;
            cursor: not-allowed;
            transform: none;
        }

        .empty-cart { 
            text-align: center; 
            color: var(--coffee-secondary);
            padding: 3rem 1rem;
        }

        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .no-image {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, #EFEBE9 0%, #E0E0E0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        @media (max-width: 1024px) {
            .order-grid {
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

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛒 New Order</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Create New Order</h2>
            <p>Select products and complete the transaction</p>
        </div>

        <div class="order-grid">
            <div class="panel">
                <div class="panel-header">
                    <h2>Select Products</h2>
                </div>
                <div class="product-grid">
                    <?php while($product = $products->fetch_assoc()): ?>
                    <div class="product-card" onclick="addToCart(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                        <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                            <div class="product-image">
                                <img src="<?php echo $product['image_path']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="no-image">☕</div>
                        <?php endif; ?>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
                            <div class="stock">Stock: <?php echo $product['stock_quantity']; ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Order Cart</h2>
                </div>
                <div id="cart-items" class="cart-items"></div>
                
                <div class="cart-total">
                    <h3>Total: ₱<span id="total-amount">0.00</span></h3>
                </div>

                <form method="POST" id="order-form" onsubmit="return submitOrder()">
                    <div class="form-group">
                        <label>Customer Name (Optional):</label>
                        <input type="text" name="customer_name" placeholder="Walk-in Customer">
                    </div>
                    <div class="form-group">
                        <label>Payment Method:</label>
                        <select name="payment_method" required>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="GCash">GCash</option>
                            <option value="PayMaya">PayMaya</option>
                        </select>
                    </div>
                    <input type="hidden" name="order_items" id="order-items-data">
                    <button type="submit" name="submit_order" class="submit-btn" id="submit-btn">Complete Order</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(product) {
            const existing = cart.find(item => item.product_id === product.id);
            if (existing) {
                if (existing.quantity < product.stock_quantity) {
                    existing.quantity++;
                    existing.subtotal = existing.quantity * existing.unit_price;
                } else {
                    alert('Not enough stock available!');
                    return;
                }
            } else {
                cart.push({
                    product_id: product.id,
                    product_name: product.name,
                    quantity: 1,
                    unit_price: parseFloat(product.price),
                    subtotal: parseFloat(product.price),
                    max_stock: product.stock_quantity
                });
            }
            renderCart();
        }

        function updateQuantity(index, change) {
            const item = cart[index];
            const newQty = item.quantity + change;
            
            if (newQty > 0 && newQty <= item.max_stock) {
                item.quantity = newQty;
                item.subtotal = item.quantity * item.unit_price;
                renderCart();
            } else if (newQty > item.max_stock) {
                alert('Not enough stock available!');
            }
        }

        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function renderCart() {
            const cartDiv = document.getElementById('cart-items');
            const totalSpan = document.getElementById('total-amount');
            const submitBtn = document.getElementById('submit-btn');
            
            if (cart.length === 0) {
                cartDiv.innerHTML = '<div class="empty-cart"><div class="empty-cart-icon">🛒</div><div>Cart is empty<br><small>Click on products to add</small></div></div>';
                totalSpan.textContent = '0.00';
                submitBtn.disabled = true;
                return;
            }

            submitBtn.disabled = false;
            let html = '';
            let total = 0;

            cart.forEach((item, index) => {
                html += `
                    <div class="cart-item">
                        <div class="name">${item.product_name}</div>
                        <div class="qty-controls">
                            <button type="button" onclick="updateQuantity(${index}, -1)">-</button>
                            <span style="font-weight: 600; min-width: 30px; text-align: center;">${item.quantity}</span>
                            <button type="button" onclick="updateQuantity(${index}, 1)">+</button>
                            <span style="margin-left: 10px; font-weight: 700; color: var(--success);">₱${item.subtotal.toFixed(2)}</span>
                            <button type="button" class="remove" onclick="removeItem(${index})">×</button>
                        </div>
                    </div>
                `;
                total += item.subtotal;
            });

            cartDiv.innerHTML = html;
            totalSpan.textContent = total.toFixed(2);
        }

        function submitOrder() {
            if (cart.length === 0) {
                alert('Please add items to cart first!');
                return false;
            }
            document.getElementById('order-items-data').value = JSON.stringify(cart);
            return true;
        }

        renderCart();
    </script>
</body>
</html>
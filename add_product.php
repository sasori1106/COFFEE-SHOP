<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock_quantity']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = uniqid() . '_' . time() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            }
        }
    }

    $sql = "INSERT INTO products (name, category, price, stock_quantity, status, image_path) 
            VALUES ('$name', '$category', $price, $stock, '$status', " . 
            ($image_path ? "'$image_path'" : "NULL") . ")";
    
    if ($conn->query($sql)) {
        $product_id = $conn->insert_id;
        $conn->query("INSERT INTO inventory_logs (product_id, transaction_type, quantity_change, notes, created_by) 
                     VALUES ($product_id, 'restock', $stock, 'Initial stock', '{$_SESSION['username']}')");
        header("Location: products.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - Coffee Shop TPS</title>
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
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #EFEBE9 0%, #F5F5F5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(62, 39, 35, 0.15);
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header .icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
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
            padding: 0.875rem 1rem;
            border: 2px solid #D7CCC8;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
            background: #FAFAFA;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--coffee-primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(107, 68, 35, 0.1);
        }

        /* Image Upload Styling */
        .image-upload-container {
            position: relative;
        }

        .image-upload-area {
            border: 3px dashed #D7CCC8;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #FAFAFA;
        }

        .image-upload-area:hover {
            border-color: var(--coffee-primary);
            background: white;
        }

        .image-upload-area.drag-over {
            border-color: var(--coffee-primary);
            background: rgba(107, 68, 35, 0.05);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--coffee-secondary);
            margin-bottom: 1rem;
        }

        .upload-text {
            color: var(--coffee-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .upload-subtext {
            color: var(--coffee-secondary);
            font-size: 0.85rem;
        }

        #product_image {
            display: none;
        }

        .image-preview {
            margin-top: 1rem;
            display: none;
            position: relative;
        }

        .image-preview.active {
            display: block;
        }

        .preview-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #D7CCC8;
        }

        .remove-image {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #EF5350;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(239, 83, 80, 0.4);
        }

        .remove-image:hover {
            background: #E53935;
            transform: scale(1.1);
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
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        button[type="submit"] {
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 187, 106, 0.3);
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 187, 106, 0.4);
        }

        .cancel-btn {
            background: #EFEBE9;
            color: var(--coffee-dark);
        }

        .cancel-btn:hover {
            background: #D7CCC8;
            transform: translateY(-2px);
        }

        @media (max-width: 640px) {
            .container {
                padding: 2rem 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .button-group {
                flex-direction: column-reverse;
            }

            .preview-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="icon">☕</div>
            <h2>Add New Product</h2>
            <p class="subtitle">Add a new item to your menu</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" placeholder="e.g., Caramel Macchiato" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">Select a category</option>
                    <option value="Coffee">Coffee</option>
                    <option value="Tea">Tea</option>
                    <option value="Pastry">Pastry</option>
                    <option value="Sandwich">Sandwich</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Price (₱)</label>
                <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label>Initial Stock Quantity</label>
                <input type="number" name="stock_quantity" min="0" placeholder="0" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>

            <div class="form-group">
                <label>Product Image (Optional)</label>
                <div class="image-upload-container">
                    <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('product_image').click()">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Click to upload or drag and drop</div>
                        <div class="upload-subtext">PNG, JPG, JPEG, GIF or WEBP (Max 5MB)</div>
                    </div>
                    <input type="file" id="product_image" name="product_image" accept="image/*">
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" class="preview-image" src="" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeImage()">×</button>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="products.php" class="cancel-btn">Cancel</a>
                <button type="submit">💾 Save Product</button>
            </div>
        </form>
    </div>

    <script>
        const fileInput = document.getElementById('product_image');
        const uploadArea = document.getElementById('uploadArea');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        // File input change event
        fileInput.addEventListener('change', function(e) {
            handleFile(this.files[0]);
        });

        // Drag and drop events
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });

        function handleFile(file) {
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPG, PNG, GIF, or WEBP)');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                uploadArea.style.display = 'none';
                imagePreview.classList.add('active');
            };
            reader.readAsDataURL(file);
        }

        function removeImage() {
            fileInput.value = '';
            previewImg.src = '';
            uploadArea.style.display = 'block';
            imagePreview.classList.remove('active');
        }
    </script>
</body>
</html>
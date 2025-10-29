<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $image_path = $product['image_path']; // Keep existing image by default
    
    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if (!empty($product['image_path']) && file_exists($product['image_path'])) {
            unlink($product['image_path']); // Delete old image file
        }
        $image_path = null;
    }
    // Handle new image upload
    elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old image if exists
            if (!empty($product['image_path']) && file_exists($product['image_path'])) {
                unlink($product['image_path']);
            }
            
            // Generate unique filename
            $new_filename = uniqid() . '_' . time() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            }
        }
    }

    $sql = "UPDATE products SET name='$name', category='$category', price=$price, status='$status', 
            image_path=" . ($image_path ? "'$image_path'" : "NULL") . " WHERE id=$id";
    
    if ($conn->query($sql)) {
        header("Location: products.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Coffee Shop TPS</title>
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

        .info-box {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            border: 2px solid #90CAF9;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            color: #0D47A1;
            font-size: 0.875rem;
        }

        .info-box strong {
            display: block;
            margin-bottom: 0.25rem;
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

        input[readonly] {
            background: #EFEBE9;
            cursor: not-allowed;
            color: var(--coffee-secondary);
        }

        /* Image Upload Styling */
        .image-upload-container {
            position: relative;
        }

        .current-image {
            margin-bottom: 1rem;
            position: relative;
        }

        .current-image img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #D7CCC8;
        }

        .current-image-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .image-action-btn {
            flex: 1;
            padding: 0.5rem 1rem;
            border: 2px solid #D7CCC8;
            border-radius: 8px;
            background: white;
            color: var(--coffee-dark);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .image-action-btn:hover {
            background: #FAFAFA;
            border-color: var(--coffee-primary);
        }

        .image-action-btn.danger {
            border-color: #EF5350;
            color: #EF5350;
        }

        .image-action-btn.danger:hover {
            background: #FFEBEE;
        }

        .no-current-image {
            text-align: center;
            padding: 2rem;
            background: #FAFAFA;
            border: 2px dashed #D7CCC8;
            border-radius: 12px;
            color: var(--coffee-secondary);
            margin-bottom: 1rem;
        }

        .no-current-image .icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .image-upload-area {
            border: 3px dashed #D7CCC8;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #FAFAFA;
            display: none;
        }

        .image-upload-area.active {
            display: block;
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

        .remove-preview {
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

        .remove-preview:hover {
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
            background: linear-gradient(135deg, #42A5F5 0%, #1E88E5 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(66, 165, 245, 0.3);
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 165, 245, 0.4);
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

            .current-image img,
            .preview-image {
                height: 200px;
            }

            .current-image-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="icon">✏️</div>
            <h2>Edit Product</h2>
            <p class="subtitle">Update product information</p>
        </div>

        <div class="info-box">
            <strong>📦 Note:</strong> Stock quantity cannot be edited here. Use Inventory Management to adjust stock levels.
        </div>

        <form method="POST" enctype="multipart/form-data" id="editForm">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="Coffee" <?php echo $product['category']=='Coffee'?'selected':''; ?>>Coffee</option>
                    <option value="Tea" <?php echo $product['category']=='Tea'?'selected':''; ?>>Tea</option>
                    <option value="Pastry" <?php echo $product['category']=='Pastry'?'selected':''; ?>>Pastry</option>
                    <option value="Sandwich" <?php echo $product['category']=='Sandwich'?'selected':''; ?>>Sandwich</option>
                    <option value="Other" <?php echo $product['category']=='Other'?'selected':''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Price (₱)</label>
                <input type="number" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="form-group">
                <label>Current Stock Quantity</label>
                <input type="number" value="<?php echo $product['stock_quantity']; ?>" readonly>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="available" <?php echo $product['status']=='available'?'selected':''; ?>>Available</option>
                    <option value="unavailable" <?php echo $product['status']=='unavailable'?'selected':''; ?>>Unavailable</option>
                </select>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <div class="image-upload-container">
                    <!-- Current Image Display -->
                    <div id="currentImageSection" style="<?php echo empty($product['image_path']) ? 'display:none;' : ''; ?>">
                        <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                            <div class="current-image">
                                <img src="<?php echo $product['image_path']; ?>" alt="Current product image" id="currentImg">
                            </div>
                            <div class="current-image-actions">
                                <button type="button" class="image-action-btn" onclick="showUploadArea()">🔄 Replace Image</button>
                                <button type="button" class="image-action-btn danger" onclick="removeCurrentImage()">🗑️ Remove Image</button>
                            </div>
                        <?php else: ?>
                            <div class="no-current-image">
                                <div class="icon">📷</div>
                                <div>No image uploaded</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- No Current Image -->
                    <div id="noImageSection" style="<?php echo !empty($product['image_path']) && file_exists($product['image_path']) ? 'display:none;' : ''; ?>">
                        <div class="no-current-image">
                            <div class="icon">📷</div>
                            <div>No image uploaded</div>
                        </div>
                        <button type="button" class="image-action-btn" onclick="showUploadArea()">📤 Upload Image</button>
                    </div>

                    <!-- Upload Area -->
                    <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('product_image').click()">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Click to upload or drag and drop</div>
                        <div class="upload-subtext">PNG, JPG, JPEG, GIF or WEBP (Max 5MB)</div>
                    </div>
                    
                    <input type="file" id="product_image" name="product_image" accept="image/*">
                    <input type="hidden" id="remove_image" name="remove_image" value="0">
                    
                    <!-- New Image Preview -->
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" class="preview-image" src="" alt="Preview">
                        <button type="button" class="remove-preview" onclick="cancelNewImage()">×</button>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="products.php" class="cancel-btn">Cancel</a>
                <button type="submit">💾 Update Product</button>
            </div>
        </form>
    </div>

    <script>
        const fileInput = document.getElementById('product_image');
        const uploadArea = document.getElementById('uploadArea');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const currentImageSection = document.getElementById('currentImageSection');
        const noImageSection = document.getElementById('noImageSection');
        const removeImageInput = document.getElementById('remove_image');

        function showUploadArea() {
            uploadArea.classList.add('active');
            uploadArea.style.display = 'block';
            currentImageSection.style.display = 'none';
            noImageSection.style.display = 'none';
        }

        function removeCurrentImage() {
            if (confirm('Are you sure you want to remove this image?')) {
                removeImageInput.value = '1';
                currentImageSection.style.display = 'none';
                noImageSection.style.display = 'block';
            }
        }

        function cancelNewImage() {
            fileInput.value = '';
            previewImg.src = '';
            uploadArea.style.display = 'none';
            imagePreview.classList.remove('active');
            
            // Show appropriate section based on whether there's a current image
            if (removeImageInput.value === '1') {
                noImageSection.style.display = 'block';
            } else {
                const hasCurrentImage = document.getElementById('currentImg');
                if (hasCurrentImage) {
                    currentImageSection.style.display = 'block';
                } else {
                    noImageSection.style.display = 'block';
                }
            }
        }

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

            // Reset remove flag since we're uploading new image
            removeImageInput.value = '0';

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                uploadArea.style.display = 'none';
                imagePreview.classList.add('active');
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>
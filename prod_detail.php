<?php
include 'config.php';
session_start();

// Validate user session
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = []; // Initialize message array

// Handle add to cart functionality with prepared statement
if (isset($_POST['add_to_cart'])) {
    try {
        // Validate and sanitize input
        $product_name = trim($_POST['product_name']);
        $product_price = floatval($_POST['product_price']);
        $product_image = trim($_POST['product_image']);
        $product_quantity = intval($_POST['product_quantity']);
        $product_size = trim($_POST['size']);

        // Check if product already in cart
        $check_stmt = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
        $check_stmt->bind_param("si", $product_name, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message[] = 'Already added to cart.';
        } else {
            // Insert into cart with prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO `cart`(user_id, name, price, quantity, image, size) VALUES(?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("issdss", $user_id, $product_name, $product_price, $product_quantity, $product_image, $product_size);
            
            if ($insert_stmt->execute()) {
                $message[] = 'Product added to cart.';
            } else {
                $message[] = 'Failed to add product to cart.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } catch (Exception $e) {
        $message[] = 'Error processing request: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/prod.css">
</head>
<body>
    <?php 
    // Display messages
    if (!empty($message)) { 
        foreach ($message as $msg) {
            echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
        }
    } 
    ?>

    <header></header>

    <div class="breadcrumb">
        <h1>Product Details</h1>
    </div>

    <main class="product-container">
        <?php
        // Validate and sanitize product ID
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']); // Convert to integer to prevent SQL injection

            // Use prepared statement to fetch product
            $select_stmt = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_stmt->bind_param("i", $id);
            $select_stmt->execute();
            $select_products = $select_stmt->get_result();

            if ($select_products->num_rows > 0) {
                $fetch_products = $select_products->fetch_assoc();
        ?>
        <div class="product-image">
            <img src="uploaded_img/<?php echo htmlspecialchars($fetch_products['image']); ?>" 
                 alt="<?php echo htmlspecialchars($fetch_products['name']); ?>">
        </div>

        <div class="product-details">
            <h1 class="product-title"><?php echo htmlspecialchars($fetch_products['name']); ?></h1>
            <div class="product-price">₱<?php echo number_format($fetch_products['price'], 2); ?></div>

            <form action="" method="post">
                <div class="form-group">
                    <label for="size">Size</label>
                    <select name="size" id="size" required>
                        <option value="small">Small</option>
                        <option value="medium">Medium</option>
                        <option value="large">Large</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" min="1" name="product_quantity" value="1" id="quantity" required>
                </div>

                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_products['name']); ?>">
                <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_products['price']); ?>">
                <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_products['image']); ?>">

                <div class="button-group">
                    <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
                    <a href="cart.php" class="btn btn-cart">Go to Cart</a>
                    <input type="button" value="Back to Shop" onclick="window.location.href='shop.php'" class="btn btn-secondary">
                </div>
            </form>
        </div>
        <?php
            } else {
                echo '<p class="empty">No product found!</p>';
            }
            $select_stmt->close(); // Close the statement
        } else {
            echo '<p class="empty">No product ID provided!</p>';
        }
        ?>
    </main>

    <script>
        // Remove messages after 3 seconds
        setTimeout(() => {
            const messages = document.getElementsByClassName('message');
            while(messages[0]) {
                messages[0].parentNode.removeChild(messages[0]);
            }
        }, 3000);
    </script>
</body>
</html>
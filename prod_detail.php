<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['add_to_cart'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];
    $product_size = $_POST['size'];

    $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

    if(mysqli_num_rows($check_cart_numbers) > 0){
        $message[] = 'Already added to cart.';
    }else{
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image, size) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image', '$product_size')") or die('query failed');
        $message[] = 'Product added to cart.';
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
    <?php if(isset($message)){ 
        foreach($message as $msg){
            echo '<div class="message">'.$msg.'</div>';
        }
    } ?>

    <header>
        <div class="logo">Off→Grid</div>
    </header>

    <div class="breadcrumb">
        Product Details
    </div>

    <main class="product-container">
        <?php
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$id'") or die('query failed');
            if(mysqli_num_rows($select_products) > 0){
                $fetch_products = mysqli_fetch_assoc($select_products);
        ?>
        <div class="product-image">
            <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="<?php echo htmlspecialchars($fetch_products['name']); ?>">
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
                <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">

                <div class="button-group">
                    <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
                    <a href="cart.php" class="btn btn-cart">Go to Cart</a>
                    <input type="button" value="Back to Shop" onclick="window.location.href='shop.php'" class="btn btn-secondary">
                </div>
            </form>
        </div>
        <?php
            }
        }else{
            echo '<p class="empty">No product found!</p>';
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
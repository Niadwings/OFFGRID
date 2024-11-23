<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['add_to_cart'])){

   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'product added to cart!';
   }

}

$category_query = mysqli_query($conn, "SELECT DISTINCT category FROM `products`") or die('query failed');
$categories = mysqli_fetch_all($category_query, MYSQLI_ASSOC);

// Handle category selection
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$category_filter = !empty($selected_category) ? "WHERE category = '$selected_category'" : '';

// Fetch products based on the selected category
$select_products = mysqli_query($conn, "SELECT * FROM `products` $category_filter") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/sarili.css">

</head>
<body>
   <div class="preloader">
   </div>  
   
<?php include 'header.php'; ?>

   <div class="heading">
      <h3>shop</h3>
   </div>

<section class="products">

   <!-- <h1 class="title">44' Matrix Collection</h1> -->
   <div class="slct">
      <div class="box-container-2">
         <div class="categor">
            <?php if (!empty($categories)) : ?>
               <a href="shop.php" <?php echo (empty($selected_category)) ? 'class="active underline"' : 'class="inactive"'; ?>>
                  ALL
               </a>
               <?php foreach ($categories as $category) : ?>
                  <a href="shop.php?category=<?php echo $category['category']; ?>" <?php echo ($selected_category == $category['category']) ? 'class="active underline"' : 'class="inactive"'; ?>>
                     <?php echo strtoupper($category['category']); ?>
                  </a>
               <?php endforeach; ?>
            <?php else : ?>
               <p>No categories available.</p>
            <?php endif; ?>
         </div>
      </div>
   </div>


   <!-- <div class="box-container">
      <select id="category" class="shesh" name="category" onchange="location = this.value;">
         <option value="shop.php">All Categories</option>
         <?php foreach ($categories as $category) : ?>
            <option value="shop.php?category=<?php echo $category['category']; ?>" <?php echo ($selected_category == $category['category']) ? 'selected' : ''; ?>>
               <?php echo $category['category']; ?>
            </option>
         <?php endforeach; ?>
         <h2><?php echo $category; ?></h2>
      </select>
   </div> -->
   
   <div class="box-container">
      <?php  
         $product_query = mysqli_query($conn, "SELECT * FROM `products` $category_filter") or die('query failed');
         if(mysqli_num_rows($product_query) > 0){
            while($fetch_products = mysqli_fetch_assoc($product_query)){
      ?>
      <a href="prod_detail.php?id=<?php echo $fetch_products['id']; ?>" class="box-link">
      <form action="" method="post" class="box">
         <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <!-- <div class="cats"><?php echo $fetch_products['category']; ?></div> -->
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="semprice">₱ <?php echo $fetch_products['price']; ?></div>
         <!-- <input type="number" min="1" name="product_quantity" value="1" class="qty"> -->
         <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
         <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
         <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
         <!-- <input type="submit" value="add to cart" name="add_to_cart" class="btn"> -->
      </form>
      </a>
      <?php
            }
         } else {
            echo '<p class="empty">No products in this category!</p>';
         }
      ?>
   </div>

</section>








<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
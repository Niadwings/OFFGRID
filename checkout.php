<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

if (isset($_POST['order_btn'])) {
   if (empty($_POST['name']) || empty($_POST['number']) || empty($_POST['email']) || empty($_POST['method']) || empty($_POST['flat']) || empty($_POST['street']) || empty($_POST['city']) || empty($_POST['state']) || empty($_POST['country']) || empty($_POST['pin_code'])) {
       $message = 'Please input all the required fields.';
   } else {
       $name = mysqli_real_escape_string($conn, $_POST['name']);
       $number = $_POST['number'];
       $email = mysqli_real_escape_string($conn, $_POST['email']);
       $method = mysqli_real_escape_string($conn, $_POST['method']);
       $address = mysqli_real_escape_string($conn, 'flat no. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code']);
       $placed_on = date('d-M-Y');

       $cart_total = 0;
       $cart_products = [];

       $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
       if (mysqli_num_rows($cart_query) > 0) {
           while ($cart_item = mysqli_fetch_assoc($cart_query)) {
               $cart_products[] = $cart_item;
               $sub_total = ($cart_item['price'] * $cart_item['quantity']);
               $cart_total += $sub_total;

               $product_id = $cart_item['id'];
               $quantity = $cart_item['quantity'];
               mysqli_query($conn, "UPDATE `products` SET stock = stock - $quantity WHERE id = '$product_id'") or die('query failed');
           }
       }

       if ($cart_total == 0) {
           $message[] = 'Your cart is empty.';
       } else {
           mysqli_autocommit($conn, false);

           $total_products = implode(', ', array_map(function ($item) {
               return $item['name'] . ' (' . $item['quantity'] . ')';
           }, $cart_products));

           $order_query = mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die('query failed');

           if ($order_query) {
               $order_id = mysqli_insert_id($conn);

               mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');

               mysqli_commit($conn);

               $message[] = 'Order placed successfully!';
               header('location:orders.php');
               exit();
           } else {
               mysqli_rollback($conn);
               $message[] = 'Order could not be placed!';
           }
       }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/sarili.css">

   <style>
   .inputBox input[type="tel"] {
      -moz-appearance: textfield;
      appearance: textfield;
   }
   </style>

</head>
<body>
   <div class="preloader">
   </div>  

<?php include 'header.php'; ?>

<div class="heading">
   <h3>checkout</h3>
   <p> <a href="home.php">home</a> / checkout </p>
</div>

<section class="display-order">

   <?php  
      $grand_total = 0;
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      if(mysqli_num_rows($select_cart) > 0){
         while($fetch_cart = mysqli_fetch_assoc($select_cart)){
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
   ?>
   <p> <?php echo $fetch_cart['name']; ?> <span>(<?php echo '₱ '.$fetch_cart['price'].'/-'.'x'. $fetch_cart['quantity']; ?>)</span> </p>
   <?php
      }
   }else{
      echo '<p class="empty">your cart is empty</p>';
   }
   ?>
   <div class="grand-total"> Grand Total : <span>₱ <?php echo $grand_total; ?></span> </div>

</section>

<section class="checkout">
   <?php
      $user_query = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id'");
      $user_data = mysqli_fetch_assoc($user_query);
   ?>

   <form action="" method="post">   
      <h3>Place your order</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Your Name :</span>
            <input type="text" name="name" required placeholder="enter your name" value ="<?php echo $user_data['name']; ?>">
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="tel" name="number" required placeholder="Enter your number" oninput="limitNumberLength(this, 11);">
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" required placeholder="enter your email" value="<?php echo $user_data['email']; ?>">
         </div>
         <div class="inputBox">
            <span>Payment Method :</span>
            <select name="method">
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="credit card">Credit Card</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address line 01 :</span>
            <input type="number" min="0" name="flat" required placeholder="e.g. flat no.">
         </div>
         <div class="inputBox">
            <span>Address line 02 :</span>
            <input type="text" name="street" required placeholder="e.g. street name">
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" required placeholder="e.g. Angeles">
         </div>
         <div class="inputBox">
            <span>Province :</span>
            <input type="text" name="state" required placeholder="e.g. Pampanga">
         </div>
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" required placeholder="e.g. Philippines">
         </div>
         <div class="inputBox">
            <span>Pin code :</span>
            <input type="number" min="0" maxlength="4" name="pin_code" required placeholder="e.g. 2009">
         </div>
      </div>
      <input type="submit" value="order now" class="btn" name="order_btn">
   </form>

</section>

<script>
   function limitNumberLength(input, maxLength) {
      if (input.value.length > maxLength) {
         input.value = input.value.slice(0, maxLength);
      }
   }
</script>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
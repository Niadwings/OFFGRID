<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/sarili.css">

</head>
<body>
   <div class="preloader">
   </div>   

<?php include 'header.php'; ?>

<!-- <div class="heading">
   <h3>about us</h3>
   <p> <a href="home.php">Home</a> / About </p>
</div> -->

<section class="about">

   <div class="flex">

      <div class="image">
         <img src="images/abts.png" alt="">
      </div>

      <div class="content">
         <h3>about off-grid</h3>
         <p>Welcome to "OFF-GRID" – your go-to destination for cutting-edge streetwear. Embrace individuality with our curated collection, blending urban grit with authentic style. From graphic tees to edgy accessories, each piece tells a story.</p>
         <p>Dive into a world where quality meets rebellion, and street style is redefined. Join the movement, explore OFF-GRID – where fashion meets the streets. Unleash your style with us.</p>
         <a href="contact.php" class="btn">Message Us</a>
      </div>

   </div>

</section>


<section class="authors">

   <h1 class="title">DEVELOPERS</h1>

   <div class="box-container">

      <div class="box">
         <img src="./images/developers/pp-1.png" alt="">
         <h3>Trisha Ann Broncano</h3>
      </div>

      <div class="box">
         <img src="./images/developers/pp-2.png" alt="">
         <h3>OFF-GRID</h3>
      </div>

      <div class="box">
         <img src="./images/developers/pp-3.png" alt="">
         <h3>Diaz Anthony</h3>
      </div>

      <div class="box">
         <img src="./images/developers/pp-4.png" alt="">
         <h3>Leonel Vergara</h3>
      </div>

      <div class="box">
         <img src="./images/developers/pp-5.png" alt="">
         <h3>Nicole Angela Mariano</h3>
      </div>

      <div class="box">
         <img src="./images/developers/pp-6.png" alt="">
         <h3>John David Calayo</h3>
      </div>

   </div>

</section>


<?php include 'footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>
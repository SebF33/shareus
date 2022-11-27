<!-- SWIPER -->

<!DOCTYPE html>
<html lang="fr">

<head>
  <title>Swiper</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="Swiper" content="Swiper">

  <link rel="stylesheet" href="assets/lib/bootswatch/sketchy/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/lib/swiper-bundle.min.css" />
  
  <link rel="stylesheet" href="assets/myswiper.css" type="text/css" media="screen">
  <link rel="stylesheet" href="assets/style.css" type="text/css" media="screen">

  <script src="assets/lib/bootswatch/sketchy/bootstrap.min.js"></script>
</head>

<body>

  <?php include "nav.html"; ?>

  <!-- Swiper container -->
  <div class="swiper-container">
    <!-- Swiper wrapper -->
    <div class="swiper-wrapper">
      <!-- Swiper slides -->
      <?php
      $galleryDir = 'uploads/';
      foreach (glob("$galleryDir{*.jpg,*.gif,*.png,*.tif,*.jpeg}", GLOB_BRACE) as $imagePath) {
        $imageName = substr($imagePath, 8);
        echo "<div class=\"swiper-slide\" style=\"background-image:url($imagePath)\">";
        echo "<div class=\"overlay\">";
        echo "<h1 class=\"d-flex justify-content-center\">$imageName</h1>";
        echo "<a class=\"btn btn-secondary\" href=\"/download/$imageName\" role=\"button\">Télécharger</a>";
        echo "</div>";
        echo "</div>";
      }
      ?>
    </div>
    <!-- Swiper pagination -->
    <div class="swiper-pagination"></div>
  </div>

  <!-- Swiper JS -->
  <script src="assets/lib/swiper-bundle.min.js"></script>
  <script src="assets/myswiper.js"></script>
</body>

</html>
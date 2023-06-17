<!-- SLIDESHOW -->

<!DOCTYPE html>
<html lang="fr">

<head>
  <title>Slides</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="Slideshow" content="slideshow">
  <meta name="description" content="Slideshow">

  <link data-vue-tag="ssr" rel="icon" type="image/png" sizes="512x512" href="assets/favicon-512.png" />

  <link rel="stylesheet" href="assets/lib/bootswatch/sketchy/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/lib/swiper-bundle.min.css" />

  <link rel="stylesheet" href="assets/myswiper.css" type="text/css" media="screen">
  <link rel="stylesheet" href="assets/style.css" type="text/css" media="screen">

  <script src="assets/lib/bootswatch/sketchy/bootstrap.min.js"></script>
</head>

<body>

  <?php include "../components/header.html"; ?>

  <!-- Swiper container -->
  <div class="swiper-container">
    <!-- Swiper wrapper -->
    <div class="swiper-wrapper">
      <!-- Swiper slides -->
      <?php

      $glob = glob("$galleryDir{*.jpg,*.jpeg,*.png,*.webp}", GLOB_BRACE);
      $imagecount = count($glob);
      if ($imagecount > 0) {
        foreach ($glob as $imagePath) {
          $fileName = substr($imagePath, strlen($galleryDir));
          $parts = explode('_', $fileName);
          $imageName = $parts[2];
          $author = $parts[0];
          $date = date("d-m-Y", strtotime($parts[1]));
          echo "<div class=\"swiper-slide\" style=\"background-image:url($imagePath)\">";
          echo "<div class=\"overlay\">";
          echo "<h1 class=\"d-flex justify-content-center\">Image : $imageName</h1>";
          echo "<h2 class=\"d-flex justify-content-center\">Utilisateur : $author</h2>";
          echo "<h3 class=\"d-flex justify-content-center\">Date : $date</h3>";
          echo "<a class=\"btn btn-secondary\" href=\"/download/$fileName\" role=\"button\">Télécharger</a>";
          echo "</div>";
          echo "</div>";
        }
      } else {
        echo "<div class=\"swiper-slide\" style=\"background-image:url($placeholdersDir/placeholder_1.jpg)\">";
        echo "<div class=\"overlay\">";
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
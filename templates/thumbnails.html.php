<!-- THUMBNAILS -->

<!DOCTYPE html>
<html lang="fr">

<head>
  <title>Vignettes</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="Vignettes" content="vignettes">
  <meta name="description" content="Vignettes">

  <link data-vue-tag="ssr" rel="icon" type="image/png" sizes="512x512" href="assets/favicon-512.png" />

  <link rel="stylesheet" href="assets/lib/uikit.min.css" />
  <link rel="stylesheet" href="assets/lib/bootswatch/sketchy/bootstrap.min.css" />

  <link rel="stylesheet" href="assets/style.css" type="text/css" media="screen">

  <script src="assets/lib/bootswatch/sketchy/bootstrap.min.js"></script>
  <script src="assets/lib/uikit.min.js"></script>
  <script src="assets/lib/uikit-icons.min.js"></script>
</head>

<body uk-height-viewport>

  <?php include "../components/header.html"; ?>

  <div class="uk-container uk-margin-large-top">
    <article>
      <section>

        <header>
          <h1 class="uk-margin-bottom d-flex justify-content-center text-center">Vos partages...</h1>
        </header>

        <div class="uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-3@l uk-grid-medium" uk-grid="masonry: true" uk-lightbox="animation: slide">

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
              echo "<a href=\"$imagePath\">\n";
              echo "<img style=\"padding:7px\" class=\"uk-card uk-card-default uk-card-hover uk-card-body\" src=\"$imagePath\" alt=\"$imageName\">";
              echo "</a>";
            }
          } else {
            echo "<a href=\"$placeholdersDir/placeholder_1.jpg\">\n";
            echo "<img style=\"padding:7px\" class=\"uk-card uk-card-default uk-card-hover uk-card-body\" src=\"$placeholdersDir/placeholder_1.jpg\" alt=\"placeholder_1\">";
            echo "</a>";
          }

          ?>

        </div>

      </section>
      <hr class="uk-divider-icon uk-margin-large-top uk-margin-large-bottom">
    </article>
  </div>

</body>

</html>
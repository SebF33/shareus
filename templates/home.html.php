<!-- ACCUEIL -->

<!DOCTYPE html>
<html lang="fr">

<head>
  <title>Shareus</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="Accueil" content="Accueil">

  <link data-vue-tag="ssr" rel="icon" type="image/png" sizes="512x512" href="assets/favicon-512.png" />

  <link rel="stylesheet" href="assets/lib/bootswatch/sketchy/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/lib/notiflix-3.2.5.mod.css" />
  <link rel="stylesheet" href="assets/lib/dropzone.min.css" type="text/css" media="screen">

  <link rel="stylesheet" href="assets/mydropzone.css" type="text/css" media="screen">
  <link rel="stylesheet" href="assets/style.css" type="text/css" media="screen">

  <script src="assets/lib/bootswatch/sketchy/bootstrap.min.js"></script>
  <script src="assets/lib/dropzone.min.js"></script>
  <script src="assets/lib/jquery-3.6.0.min.js"></script>
  <script src="assets/lib/notiflix-3.2.5.mod.js"></script>
</head>

<body>

  <?php include "../components/header.html"; ?>

  <div class="container mt-5">
    <h1 class="d-flex justify-content-center text-center">Partagez-nous !</h1>
    <h2 class="d-flex justify-content-center text-center">Montrez vos plus belles images : illustrations, dessins, croquis, ...</h2>

    <form id="uploadForm" class="mt-5" name="frmAdd" method="post" enctype="multipart/form-data">
      <div class="form-group m-auto">
        <label class="fw-bold">Votre nom<span> *</span> :</label>
        <input type="text" name="username" class="form-control mt-1" placeholder="Saisissez 10 caractères max..." required="required">
      </div>
      <div class="demo-form-row text-center mt-4">
        <input type="hidden" name="MAX_FILE_SIZE" value="1048576"> <!-- Poids maxi : 1Mo => 1024*1024 -->
      </div>

      <!-- Zone d'aperçu des fichiers glissés -->
      <div id="previewsContainer" class="dropzone mt-5">
        <div class="dz-default dz-message">
          <button class="dz-button" type="button">
            <p class="fw-bold">Cliquez ou déposez vos fichiers ici.</p>
            <br>
            <span>(Fichiers acceptés : .jpg, .png, .webp)</span>
            <br>
            <span>(Poids max par fichier : 1 Mo)</span>
            <br>
            <span>(Caractères max du nom de fichier : 30)</span>
          </button>
        </div>
      </div>

      <div class="demo-form-row text-center my-4">
        <button type="submit" name="uploadBtn" id="button" class="btn btn-primary" value="Upload">Envoyer</button>
      </div>
    </form>

    <script src="assets/mydropzone.js"></script>
  </div>

</body>

</html>
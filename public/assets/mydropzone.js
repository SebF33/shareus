Dropzone.autoDiscover = false;

new Dropzone("#uploadForm", {
  clickable: ".dropzone",
  url: "http://localhost:8888/",
  previewsContainer: "#previewsContainer",
  paramName: "file",
  accept: function (file, done) {
    if (file.name.length > 30) {
      done("Il y a plus de 30 caractères dans le nom de fichier.");
    } else {
      done();
    }
  },

  acceptedFiles: ".jpeg,.jpg,.png,.webp",
  addRemoveLinks: true,
  autoProcessQueue: false,
  maxFiles: 10,
  maxFilesize: 1, // MB
  parallelUploads: 10,
  uploadMultiple: true,

  // Langage
  dictCancelUpload: "Annuler l'upload",
  dictCancelUploadConfirmation: "Voulez-vous vraiment annuler cet upload ?",
  dictDefaultMessage: "Déposer vos fichiers ici.",
  dictFallbackMessage: "Votre navigateur ne prend pas en charge l'upload de fichiers par glisser-déposer.",
  dictFallbackText: "Veuillez utiliser le formulaire de secours ci-dessous pour upload vos fichiers comme au bon vieux temps.",
  dictFileTooBig: "Le fichier est trop volumineux ({{filesize}}MiB). Taille de fichier maximale : {{maxFilesize}}MiB.",
  dictInvalidFileType: "Vous ne pouvez pas upload de fichiers de ce type.",
  dictMaxFilesExceeded: "Seuls {{maxFiles}} fichiers max sont autorisés.",
  dictRemoveFile: "Retirer",
  dictResponseError: "Le serveur a répondu avec le code {{statusCode}}.",

  init() {
    var myDropzone = this;

    this.element.querySelector("button[name=uploadBtn]").addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      myDropzone.processQueue();
    });

    Notiflix.Notify.init({
      position: 'center-top',
      cssAnimationStyle: 'from-top',
    });

    myDropzone.on("error", function (file, responseText) {
      $(file.previewElement).find('.dz-error-message').text(responseText);
    });

    myDropzone.on("errormultiple", function (file) {
      var errorMsg = 'Problème rencontré avec le fichier ' + file[0].name + ' 😒';
      Notiflix.Notify.failure(
        errorMsg, {
          className: 'alert alert-dismissible alert-danger',
          timeout: 5000,
          closeButton: true,
        },
      );
    });

    myDropzone.on("success", function (file) {
      var msg = 'Ce fichier a été envoyé avec succès.';
      $(file.previewElement).find('.dz-error-message').text(msg);
    });

    myDropzone.on("successmultiple", function () {
      var successTitle = 'Fichier(s) transmis avec succès ! 😃';
      Notiflix.Notify.success(
        successTitle, {
          className: 'alert alert-dismissible alert-success',
          timeout: 6000,
        },
      );
    });
  }
});
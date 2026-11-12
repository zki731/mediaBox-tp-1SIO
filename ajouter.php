<?php
// 1. Initialisation
require_once "includes/fonctions.php";

$erreurs = [];          // messages d'erreur à afficher
$type = "document";     // valeurs par défaut du formulaire
$titre = "";
$motsCles = "";
$description = "";
$url = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Récupérer les saisies du formulaire
    //    (pour pouvoir les réafficher en cas d'erreur)
    $type        = $_POST["type"] ?? "";
    $titre       = trim($_POST["titre"] ?? "");
    $motsCles    = trim($_POST["mots-cles"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $url         = trim($_POST["url"] ?? "");

    // 3. Vérifier les saisies
    //    - champs obligatoires selon le type de ressource
    //    - extension du fichier (si document)
    //    Chaque problème ajoute un message au tableau des erreurs.
    if ($type != "document" && $type != "favori") {
        $erreurs[] = "Le type de ressource est invalide.";
    }

    // 4. S'il n'y a aucune erreur :
    //    - téléverser le fichier (si document)
    //    - enregistrer la ressource
    //    - rediriger vers la liste
    //    Sinon : on continue, la page se réaffiche avec les erreurs.
    if (empty($erreurs)) {

        header("Location: index.php");
        exit;
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajoutez un document</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <!-- Afficher les erreurs s'il y en a (une liste, par exemple) -->




<form METHOD="POST"> <!-- Form de base-->
    <label for="resType">Choissisez un type de ressource</label>
    <select id="resType" name="formType">
        <option value="choice" disabled selected>VEUILLEZ CHOISIR UN TYPE DE DOCUMENT</option>
        <option value="Document">Document</option>
        <option value="Favori">Favori</option>
    </select>
    <!-- Pour un document -->
    <div id="champDocument" style="display: none; margin-top: 10px;">
        <label for="fichier">Sélectionnez votre fichier :</label>
        <input type="file" id="fichier" name="fichier" required>
        <label for="Titre">Veuillez entrer un titre pour votre document</label>
        <input type="text" id="docName" name="Titre" required></input>
    </div>

    <!-- Pour un URL -->
    <div id="champFavori" style="display: none; margin-top: 10px;">
        <label for="url">Entrez l'URL du favori :</label>
        <input type="url" id="url" name="url" placeholder="https://exemple.com" required>
    </div>

    <br>
    <button type="submit">Envoyer</button>
</form>

         <!-- attention à l'attribut enctype pour l'envoi de fichier ?????-->





    <!-- Script de bascule : afficher le champ fichier ou le champ URL selon le type
         (penser à l'état du formulaire au chargement de la page) -->
<script type="text/javascript">
    console.log("Hello World");
    let docType = document.getElementById('resType');
    //Récupération des deux blocs HTML
    let divDocument = document.getElementById('champDocument')
    let divFavori = document.getElementById('champFavori')
    docType.addEventListener('change', function() {
        let docTypeValue = docType.value;
        console.log(docTypeValue);
        if(docTypeValue==="Document"){
            divDocument.style.display="block";
            divFavori.style.display="none";
        }
        else{
            divFavori.style.display="block";
            divDocument.style.display="none";
        }
    }); //fonction qui attend le changement de ressrouce
</script>



</body>
</html>
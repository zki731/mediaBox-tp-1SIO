<?php
//Initialisation
//NB CACHER MOTS CLES ET DESCRIPTION EN JS
//NB RECOMMENTER CODE POUR MORIN
//NB AJOUTER AFFICHAGE DES ERREURS

require_once "includes/fonctions.php";

$erreurs = [];          // messages d'erreur à afficher
$type = "document";     // valeurs par défaut du formulaire
$titre = "";
$motsCles = "";
$description = "";
$url = "";

$extensionsAutorisees = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'];            // types de fichiers autorisés

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Récupérer les saisies du formulaire
    //    (pour pouvoir les réafficher en cas d'erreur)
    $type        = $_POST["type"] ?? "";
    $titre = ($type === "favori") ? trim($_POST["titre_favori"] ?? "") : trim($_POST["titre"] ?? "");
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
    if ($type === "document") { 
        if (empty($titre)) {
            $erreurs[] = "Le titre du document est obligatoire.";
        }
    
        if (!isset($_FILES["fichier"]) || $_FILES["fichier"]["error"] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Veuillez sélectionner un fichier valide.";
        } else {
            // Extraction et vérification de l'extension
            $nomFichier = $_FILES["fichier"]["name"];
            $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));

            if (!in_array($extension, $extensionsAutorisees)) {
                $erreurs[] = "Extension non autorisée. Formats acceptés : " . implode(', ', $extensionsAutorisees);
            }
        }
    } elseif ($type === "favori") {
        if (empty($titre)) {
            $erreurs[] = "Le titre du favori est obligatoire.";
        }
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $erreurs[] = "Veuillez entrer une URL valide.";
        }
    }
    // vérifie présence et l'état du fichier dans $_FILES, vérifie l'extension et l'extrait, vérifie la validité de l'url et l'extrait également

    //traitement des fichiers 

    // 4. S'il n'y a aucune erreur :
    //    - téléverser le fichier (si document)
    //    - enregistrer la ressource
    //    - rediriger vers la liste
    //    Sinon : on continue, la page se réaffiche avec les erreurs.

    if (empty($erreurs)) { //verifie la variable erreur et set le dossier source
        if ($type === "document") {
            $dossierDestination = "uploads/";
            
            //sert à créer le dossier si il n'existe pas encore
            if (!is_dir($dossierDestination)) {
                mkdir($dossierDestination, 0755, true);
            }

            //evite l'écrasement de fichiers portant le même nom
            $nomUnique = uniqid() . "_" . basename($_FILES["fichier"]["name"]);
            $cheminFinal = $dossierDestination . $nomUnique;

            // Déplace le fichier de la mémoire temporaire vers le dossier final
            if (move_uploaded_file($_FILES["fichier"]["tmp_name"], $cheminFinal)) {
                ajouter([
                    'type'        => 'document',
                    'titre'       => $titre,
                    'mots-cles'   => $motsCles,
                    'description' => $description,
                    'fichier'     => $nomUnique,
                    'date'        => date('Y-m-d')
                ]);

                header("Location: index.php");
                exit;

            } else {
                $erreurs[] = "Erreur lors de l'enregistrement du fichier sur le serveur.";
            }
        } else {
            ajouter([
                'type'        => 'favori',
                'titre'       => $titre,
                'mots-cles'   => $motsCles,
                'description' => $description,
                'url'         => $url,
                'date'        => date('Y-m-d')
            ]);

            header("Location: index.php");
            exit;
        }
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




<form METHOD="POST" enctype="multipart/form-data"> <!-- permet d'avertir le navigateur que le formulaire va envoyer et du texte et des fichiers binaires -->
  <!-- attention à l'attribut enctype pour l'envoi de fichier ?????-->   
   <label for="resType">Choissisez un type de ressource</label>
    <select id="resType" name="type">
        <option value="choice" disabled selected>VEUILLEZ CHOISIR UN TYPE DE DOCUMENT</option>
        <option value="document">Document</option>
        <option value="favori">Favori</option>
    </select>
    <!-- Pour un document -->
    <div id="champDocument" style="display: none; margin-top: 10px;">
        <label for="fichier">Sélectionnez votre fichier :</label>
        <input type="file" id="fichier" name="fichier">
        <label for="Titre">Veuillez entrer un titre pour votre document</label>
        <input type="text" id="docName" name="titre">
    </div>

    <!-- Pour un URL -->
    <div id="champFavori" style="display: none; margin-top: 10px;">
        <label for="url">Entrez l'URL du favori :</label>
        <input type="url" id="url" name="url" placeholder="https://exemple.com">
        <label for="favName">Veuillez entrer un titre pour votre favori</label> 
        <input type="text" id="favName" name="titre_favori">
    </div>

    <!-- Permet a l'user d'ajouter mots-clés et description manuellement (commun a document et favori) -->
     <div style="margin-top: 10px;">
        <label for="mots-cles">Mots-clés :</label>
        <input type="text" id="mots-cles" name="mots-cles">
        
        <label for="description">Description :</label>
        <textarea id="description" name="description"></textarea>
    </div>

    <br>
    <button type="submit">Envoyer</button>
</form>

         <!-- attention à l'attribut enctype pour l'envoi de fichier ?????-->






<script type="text/javascript">
    console.log("Hello World");
    let docType = document.getElementById('resType');
    //Récupération des deux blocs HTML
    let divDocument = document.getElementById('champDocument');
    let divFavori = document.getElementById('champFavori');
    //gestion du required
    let inputFichier = document.getElementById('fichier');
    let inputTitre = document.getElementById('docName');
    let inputUrl = document.getElementById('url');
    let inputTitreFavori = document.getElementById('favName'); // [AJOUT]
    
    function gererAffichage(){
        let docTypeValue = docType.value;
        console.log(docTypeValue);
        //si document/url alors :
        if(docTypeValue==="document"){
            divDocument.style.display="block";
            divFavori.style.display="none"; //gestion de l'affichage css du formulaire

            //activation de required sur les champs affichés :
            
            inputFichier.required = true;
            inputTitre.required = true;
            inputUrl.required = false;
            inputTitreFavori.required = false; // [AJOUT]
        }
        else if(docTypeValue == "favori"){
            divFavori.style.display="block";
            divDocument.style.display="none";
        
            //gestion du required comme au dessus mais propre au urls

            inputFichier.required = false;
            inputTitre.required = false;
            inputUrl.required = true;
            inputTitreFavori.required = true; // [AJOUT]
        }

    }
    docType.addEventListener('change', gererAffichage); //écoute le changement de ressource

    //execution de la fonction au chargement de la page

    gererAffichage();
</script>




</body>
</html>
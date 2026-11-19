<?php

require_once "includes/fonctions.php"; //importation des fonctions

$erreurs = [];          // messages d'erreur à afficher
$type = "document";     // valeur par défaut du formulaire
$titre = "";
$motsCles = "";
$description = "";
$url = "";

$extensionsAutorisees = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'];// types de fichiers autorisés

if ($_SERVER["REQUEST_METHOD"] == "POST") { 

    $type = $_POST["type"] ?? ""; //Si il existe il vaut lui même sinon il vaut la chaine vide
    $titre = ($type === "favori") ? trim($_POST["titre_favori"] ?? "") : trim($_POST["titre"] ?? ""); //Renvoie les deux types de titres en enlevant cara/espaces avec .trim
    $motsCles = trim($_POST["mots-cles"] ?? ""); //Récupère depuis post les données des form html et .trim 
    $description = trim($_POST["description"] ?? ""); 
    $url = trim($_POST["url"] ?? ""); 

    if ($type != "document" && $type != "favori") { //Vérifie le type de document et renvoie une erreur si ce n'est ni un doc ni un fav
        $erreurs[] = "Le type de ressource est invalide.";
    }
    if ($type === "document") { 
        if (empty($titre)) {
            $erreurs[] = "Le titre du document est obligatoire."; //Rend obligatoire la saisie d'un titre, sinon renvoie erreur
        }
    
        if (!isset($_FILES["fichier"]) || $_FILES["fichier"]["error"] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Veuillez sélectionner un fichier valide."; //Vérifie que la variable files est affectée ou si il y a une erreur et renvoie cette erreur le cas écheant
        } else {
            //Si il n'y a pas d'erreur/le fichier est valide : 

            $nomFichier = $_FILES["fichier"]["name"]; //Récupére nom du fichier depuis l'ordinateur de l'utilisateur
            $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION)); // Extrait l'extension du fichier et la convertit en minuscules

            if (!in_array($extension, $extensionsAutorisees)) {
                $erreurs[] = "Extension non autorisée. Formats acceptés : " . implode(', ', $extensionsAutorisees); //Affiche les types de fichiers autorisés si un fichier non autorisés est upload
            }
        }
    } elseif ($type === "favori") { //Oblige a donner un titre au favori
        if (empty($titre)) {
            $erreurs[] = "Le titre du favori est obligatoire.";
        }
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) { //Oblige a donner une url valide
            $erreurs[] = "Veuillez entrer une URL valide.";
        }
    }

//Traitement des fichiers après vérification : 

    if (empty($erreurs)) { //Verifie la variable erreur et défini le dossier source
        if ($type === "document") {
            $dossierDestination = "uploads/";
            
            //Sert à créer le dossier si il n'existe pas encore
            if (!is_dir($dossierDestination)) {
                mkdir($dossierDestination, 0755, true);
            }

            $nomFichier = basename($_FILES["fichier"]["name"]); //Extrait le nom et l'extension sans le chemin de fichier.
            $cheminFinal = $dossierDestination . $nomFichier;

            // Déplace le fichier de la mémoire temporaire vers le dossier final (revoir doc fonction move_uploaded)
            if (move_uploaded_file($_FILES["fichier"]["tmp_name"], $cheminFinal)) {
                ajouter([
                    'type'        => 'document',
                    'titre'       => $titre,
                    'mots-cles'   => $motsCles,
                    'description' => $description,
                    'fichier'     => $nomFichier,
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

<!-- Affichage simple des erreurs, à styliser avec du CSS -->
<?php if (!empty($erreurs)): ?>
    <div class="alert-erreurs">
        <ul>
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


<form METHOD="POST" enctype="multipart/form-data"> <!-- permet d'avertir le navigateur que le formulaire va envoyer et du texte et des fichiers binaires -->
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
<!-- //Script JS : -->

<script type="text/javascript">
    console.log("Hello World");
    //Récupération des blocs HTML
    let docType = document.getElementById('resType');
    let divDocument = document.getElementById('champDocument');
    let divFavori = document.getElementById('champFavori');
    //recuperation des saisies pour un document
    let inputFichier = document.getElementById('fichier');
    let inputTitre = document.getElementById('docName');
    //recuperation des saisies pour un favori
    let inputUrl = document.getElementById('url');
    let inputTitreFavori = document.getElementById('favName'); 
    //fonction principale pour afficher le bon document
    function gererAffichage(){
        let docTypeValue = docType.value;
        console.log(docTypeValue);

        //si document/url alors :
        if(docTypeValue==="document"){
            divDocument.style.display="block";
            divFavori.style.display="none"; //gestion de l'affichage css du formulaire

            //activation de required sur les champs affichés 
            
            inputFichier.required = true;
            inputTitre.required = true;
            inputUrl.required = false;
            inputTitreFavori.required = false; 
        }
        else if(docTypeValue == "favori"){
            divFavori.style.display="block";
            divDocument.style.display="none";
        
            //gestion du required comme au dessus mais propre au urls

            inputFichier.required = false;
            inputTitre.required = false;
            inputUrl.required = true;
            inputTitreFavori.required = true; 
        }

    }
    docType.addEventListener('change', gererAffichage); //écoute le changement de ressource

    //execution de la fonction au chargement de la page

    gererAffichage();
</script>




</body>
</html>
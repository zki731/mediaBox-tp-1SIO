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
    <title>Titre de la page</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <!-- Afficher les erreurs s'il y en a (une liste, par exemple) -->


    <!-- Le formulaire :
         - un seul formulaire pour les deux types
         - valeurs saisies réaffichées
         - attention à l'attribut enctype pour l'envoi de fichier -->


    <!-- Script de bascule : afficher le champ fichier ou le champ URL selon le type
         (penser à l'état du formulaire au chargement de la page) -->
</body>
</html>
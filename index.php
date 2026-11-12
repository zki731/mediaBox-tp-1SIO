<?php
include("includes/fonctions.php");
?>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="style/style.css">
        <title>MEDIABOX</title>
    </head>
    <body>
        <?php
        $ressources=lire(); //lit les données de .txt et les renvoie en array
        foreach($ressources as $res){ //foreach qui parcours les données permet leur affichage dans la variable $res
            echo '<article>';
                $logo = $res['type']=="document" ? "data\img\logo-document.svg" : "data\img\logo-favori.svg"; //condition qui vérifie le type de document et affiche l'image en fonction
                echo '<h1><img src="'.$logo.'" alt="'.htmlspecialchars($res['type']).'" width="32" height="32"> '.$res['titre'].'</h1>'; //affichage de l'image (merci deepseek)
                echo '<p>Mots-Clés: '.$res['mots-cles'].'</p>'; //affichage des mots clés
                $chemin=$res['type']=="document" ? 'uploads/'.$res['fichier'] : $res['url']; //condition qui vérifie le type de fichier et le défini dans la variable $chemin
                echo '<p>Chemin du fichier : '.$chemin.'</p>'; //affichage du chemin de fichier depuis $chemin
                echo '<p>Date : '.$res['date'].'</p>'; //affichage de la date
                echo '<a href="'.$chemin.'"target="_blank" rel="noopener noreferrer">Voir</a>'; //Permet de voir le document en prenant ce qui est contenu dans $chemin
            echo '</article>';
        }
        ?>
        <a href="ajouter.php" target="_blank" id="ajouter">Ajouter une ressource</a>
    </body>

</html>


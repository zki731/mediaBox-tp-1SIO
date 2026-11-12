<?php
include("includes/fonctions.php");
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>MEDIABOX</title>
    </head>
    <body>
        <?php
        $ressources=lire();
        foreach($ressources as $res){
            echo '<article>';
                $logo = $res['type']=="document" ? "data\img\logo-document.svg" : "data\img\logo-favori.svg";
                echo '<h1><img src="'.$logo.'" alt="'.htmlspecialchars($res['type']).'" width="32" height="32"> '.$res['titre'].'</h1>';           
                echo '<p>Mots-Clés: '.$res['mots-cles'].'</p>';
                $chemin=$res['type']=="document" ? $res['fichier'] : $res['url']; //condition
                echo '<p>Chemin du fichier : '.$chemin.'</p>';
                echo '<p>Date : '.$res['date'].'</p>';
            echo '</article>';
        }

        ?>
    </body>
</html>


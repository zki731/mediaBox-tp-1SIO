<?php
//Suppresion d'un document.
require_once "includes/fonctions.php";

if(isset($_GET['id'])){ //Au click, si la variable id est set (si le doc existe) supprime la variable id
    supprimer($_GET['id']);
}
header("Location: index.php");
exit;

?>

<?php
/**
 * -----------------------------------------------------------
 * FONCTIONS GED - Stockage dans un fichier texte structuré
 * -----------------------------------------------------------
 * Format du fichier :
 *   clé:"valeur"
 *   (valeurs multilignes possibles)
 *   Une ligne vide sépare les ressources.
 * -----------------------------------------------------------
 */

const FICHIER_GED = 'data/ressources.txt';

/*******************************************************************
 * Lire tous les documents depuis le fichier texte.
 *******************************************************************/

function lire() {
    // Vérifie si le fichier existe, sinon affiche un message d'erreur
    if (!file_exists(FICHIER_GED)) {
        echo "ERREUR : le fichier de données ".FICHIER_GED." n'existe pas.<br>";
        exit;
    }

    // Lit toutes les lignes du fichier dans un tableau $lignes
    // FILE_IGNORE_NEW_LINES supprime le saut de ligne en fin de chaque élément
    $lignes = file(FICHIER_GED, FILE_IGNORE_NEW_LINES);

    $documents = []; // Tableau final qui contiendra tous les documents
    $doc = [];       // Tableau temporaire pour stocker un document en cours de lecture

    $nbLignes = count($lignes); // Nombre total de lignes dans le fichier
    $i = 0;                     // Indice pour parcourir les lignes avec une boucle while

    // Parcours de toutes les lignes
    while ($i < $nbLignes) {
        $ligne = trim($lignes[$i]); // Supprime les espaces et tabulations en début et fin de ligne

        // --- Ligne vide : fin d'un document ---
        if ($ligne === "") {
            if (!empty($doc)) {
                $documents[] = $doc; // Ajoute le document courant au tableau final
                $doc = [];           // Réinitialise le tableau pour le prochain document
            }
            $i++;       // Passe à la ligne suivante
            continue;   // Revient au début de la boucle pour traiter la ligne suivante
        }

        // Recherche la position du caractère ":" qui sépare clé et valeur
        $pos = strpos($ligne, ":");
		
		// Vérifie l'existence du séparateur ":" entre la clé et la valeur
		if(!preg_match('/^[^:" ]*:\s*"/', $ligne)){
			echo "ERREUR  -- syntaxe incorrecte à la ligne ".($i+1)." du fichier ".FICHIER_GED."<br>";
			exit;
		}

        // --- Extrait la clé et la valeur ---
        $cle = substr($ligne, 0, $pos);                 // Extrait la clé : tout avant ":"
        $valeur = trim(substr($ligne, $pos + 1));      // Extrait la valeur : tout après ":", et enlève les espaces

        // Supprime le guillemet d'ouverture si la valeur commence par "
        if (str_starts_with($valeur, '"')) {
            $valeur = substr($valeur, 1);
        }

    	while (
            ($i + 1) < $nbLignes &&                  // On n’a pas dépassé le nombre total de lignes
            trim($lignes[$i + 1]) !== "" &&          // La ligne suivante n’est pas vide
            str_ends_with($lignes[$i], '"') === false)   // La ligne courant ne se termine pas par "
		{
			$i++;                                      // Passe à la ligne suivante
			$valeur .= "\n" . trim($lignes[$i]);      // Concatène la ligne suivante à la valeur avec un saut de ligne
        }

        // Supprime le guillemet de fermeture à la fin de la valeur
        if (str_ends_with($valeur, '"')) {
            $valeur = substr($valeur, 0, -1);
        }

        // Ajoute la paire clé/valeur dans le tableau du document courant
        $doc[$cle] = $valeur;

        $i++; // Passe à la ligne suivante
    }

    // Ajoute le dernier document si le fichier ne se termine pas par une ligne vide
    if (!empty($doc)) {
        $documents[] = $doc;
    }

    // Retourne le tableau contenant tous les documents
    return $documents;
}


/*******************************************************************
 * Écrire tous les documents dans le fichier texte.
 *******************************************************************/
function ecrire(array $documents) {
    $sortie = ""; // Initialise une chaîne vide qui contiendra tout le contenu à écrire

    // Parcourt chaque document/favori dans le tableau
    foreach ($documents as $doc) {

        // Parcourt chaque clé/valeur du document
        foreach ($doc as $cle => $valeur) {
            // Normalise les retours chariot Windows (\r\n) en retours chariot Unix (\n)
            $valeur = str_replace("\r\n", "\n", $valeur);

            // Construit la ligne "clé:"valeur"" et l'ajoute à la chaîne de sortie
            $sortie .= $cle . ':"' . $valeur . '"' . "\n";
        }

        // Ajoute une ligne vide entre chaque document/favori
        $sortie .= "\n";
    }

    // Écrit tout le contenu dans le fichier défini par FICHIER_GED
    file_put_contents(FICHIER_GED, $sortie);
}


/*******************************************************************
 * Obtenir un document par son ID.
 *******************************************************************/
function getDocumentById($id) {
    // Lit tous les documents/favoris à partir du fichier texte
    $documents = lire();

    // Parcourt chaque document
    foreach ($documents as $doc) {
        // Vérifie si le document a une clé 'id' et si elle correspond à l'identifiant recherché
        if (isset($doc['id']) && $doc['id'] == $id) {
            return $doc; // Retourne le document correspondant dès qu'il est trouvé
        }
    }

    // Si aucun document avec cet ID n'a été trouvé, retourne null
    return null;
}


/*******************************************************************
 * Ajouter un document.
 *******************************************************************/
function ajouter(array $document) {
    // Lit tous les documents/favoris depuis le fichier
    $documents = lire();

    // Détermine le nouvel ID :
    $nouvelId = count($documents) ? max(array_column($documents, 'id')) + 1 : 1;

    // Assigne le nouvel ID au document
    $document['id'] = $nouvelId;

    // Réorganise le tableau pour que l'ID soit en première position
    if (isset($document['id'])) {
        $document = ['id' => $document['id']] + $document;
    }

    // Ajoute le document au tableau existant
    $documents[] = $document;

    // Réécrit tous les documents dans le fichier
    ecrire($documents);

    // Retourne l'ID du document ajouté
    return $nouvelId;
}



/*******************************************************************
 * Modifier un document existant.
 *******************************************************************/
function modifier($id, array $document) {
    // Lit tous les documents existants depuis le fichier
    $documents = lire();
    $modifie = false; // Indique si une modification a été effectuée

    // Parcourt tous les documents par référence pour pouvoir les modifier directement
    foreach ($documents as &$doc) {
        // Si le document courant a le même ID que celui à modifier
        if ($doc['id'] == $id) {
            // Parcourt toutes les clés/valeurs à mettre à jour
            foreach ($document as $cle => $valeur) {
                // Ne modifie jamais l'ID
                if ($cle !== 'id') $doc[$cle] = $valeur;
            }

            // Réorganise le tableau pour que l'ID reste en première position
            $doc = ['id' => $doc['id']] + $doc;

            $modifie = true; // Indique qu'une modification a été effectuée
            break;           // On a trouvé et modifié le document, on peut sortir
        }
    }

    // Si une modification a été faite, réécrit tous les documents dans le fichier
    if ($modifie) ecrire($documents);

    // Retourne true si un document a été modifié, false sinon
    return $modifie;
}



/*******************************************************************
 * Supprimer un document.
 *******************************************************************/
function supprimer($id) {
    // Lit tous les documents/favoris existants depuis le fichier
    $documents = lire();

    $nouvelleListe = []; // Tableau pour stocker les documents qui ne seront pas supprimés
    $supprime = false;   // Indique si un document a été supprimé

    // Parcourt tous les documents
    foreach ($documents as $doc) {
        // Si le document courant a l'ID à supprimer
        if ($doc['id'] == $id) {
            $supprime = true; // On marque qu'une suppression a eu lieu

            // Si le document contient un fichier associé
            if (!empty($doc['fichier'])) {
                $fichier = 'uploads/' . $doc['fichier']; // Chemin complet du fichier
                // Supprime physiquement le fichier s'il existe
                if (file_exists($fichier)) unlink($fichier);
            }

        } else {
            // Si le document n'a pas l'ID à supprimer, on le garde
            $nouvelleListe[] = $doc;
        }
    }

    // Si une suppression a été faite, on réécrit la liste mise à jour dans le fichier
    if ($supprime) ecrire($nouvelleListe);

    // Retourne true si un document a été supprimé, false sinon
    return $supprime;
}

/*******************************************************************
 * Normalise une chaîne pour permettre des comparaisons insensibles
 * à la casse, aux accents et aux espaces en début/fin.
 *******************************************************************/

function normaliser(string $texte): string
{
    $texte = trim($texte);
    $texte = mb_strtolower($texte, 'UTF-8');

    if (class_exists('Normalizer')) {
        // Décompose les caractères accentués (é -> e + accent), puis retire les accents
        $texte = Normalizer::normalize($texte, Normalizer::FORM_D);
        $texte = preg_replace('/\p{Mn}/u', '', $texte);
    } else {
        // Solution de repli si l'extension intl n'est pas disponible
        $accents = [
            'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a',
            'ç'=>'c',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'î'=>'i','ï'=>'i','í'=>'i',
            'ô'=>'o','ö'=>'o','ó'=>'o','õ'=>'o',
            'ù'=>'u','û'=>'u','ü'=>'u','ú'=>'u',
            'ÿ'=>'y','ñ'=>'n','œ'=>'oe','æ'=>'ae'
        ];
        $texte = strtr($texte, $accents);
    }

    return $texte;
}

?>

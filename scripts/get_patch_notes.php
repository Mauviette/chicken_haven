<?php
header('Content-Type: application/json');

// Définir le chemin du fichier
$file_path = __DIR__ . "/../data/patch_notes.data";

// Vérifier si le fichier existe
if (!file_exists($file_path)) {
    echo json_encode(["error" => $file_path . " not found"]);
    exit;
}

// Lire le contenu du fichier
$patch_notes = file_get_contents($file_path);

// Séparer les patch notes par "---"
$notes_array = array_filter(array_map('trim', explode("---", $patch_notes)));

// Retourner en JSON
echo json_encode($notes_array);
?>

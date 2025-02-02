<?php
header('Content-Type: application/json');

require_once '../../database/db_connect.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'];
        $slot_number = $_POST['slot_number'];
        $chicken_id = $_POST['chicken_id'];

        // Insérer ou mettre à jour le slot avec la poule
        $stmt = $pdo->prepare("INSERT INTO incubators (user_id, slot_number, chicken_id) VALUES (:user_id, :slot_number, :chicken_id) ON DUPLICATE KEY UPDATE chicken_id = VALUES(chicken_id)");

        $stmt->execute([
            'user_id' => $user_id,
            'slot_number' => $slot_number,
            'chicken_id' => $chicken_id
        ]);

        // Retirer la poule si elle est déjà dans un autre emplacement
        $stmt = $pdo->prepare(' UPDATE incubators SET chicken_id = NULL WHERE user_id = :user_id AND slot_number != :slot_number AND chicken_id = :chicken_id');
        $stmt->execute([
            'user_id' => $user_id,
            'slot_number' => $slot_number,
            'chicken_id' => $chicken_id
        ]);

        echo json_encode(["success" => true, "message" => "Poule ajoutée avec succès"]);
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

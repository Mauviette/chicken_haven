<?php 

require_once '../../database/db_connect.php';

session_start();

$user_id = $_SESSION['user_id'];

// On a la table player_egg_price(user_id, egg_id, price) et la table openable_eggs(id, price). Le but est de vérifier si une valeur de player_egg_price est manquante. Dans ce cas, insérer une ligne avec les arguments correspondants de openable_eggs

$query = "SELECT id, price FROM openable_eggs";
$result = $pdo->query($query);

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $egg_id = $row['id'];
    $price = $row['price'];

    $check_query = "SELECT * FROM player_egg_price WHERE user_id = ? AND egg_id = ?";
    $stmt = $pdo->prepare($check_query);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $egg_id, PDO::PARAM_INT);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows == 0) {
        $insert_query = "INSERT INTO player_egg_price (user_id, egg_id, price) VALUES (?, ?, ?)";
        $insert_stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $insert_stmt->bindValue(2, $egg_id, PDO::PARAM_INT);
        $insert_stmt->bindValue(3, $price, PDO::PARAM_STR);
        $insert_stmt->bind_param("iid", $user_id, $egg_id, $price);
        $insert_stmt->execute();
    }

    $stmt->close();
}

$pdo->close();

?>
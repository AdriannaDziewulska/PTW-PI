<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Sprawdzenie, czy użytkownik już istnieje
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");

    try {
        $stmt->execute([$username, $email, $hash]);
        header("Location: login.php?registered=1");
        exit();
    } catch (PDOException $e) {
        $error = "Rejestracja nieudana: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8" />
    <title>Rejestracja</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

    <form class="form-login" method="post" novalidate>
        <h2>Rejestracja</h2>
        <input type="text" name="username" placeholder="Nazwa użytkownika" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <button type="submit">Zarejestruj</button>
        <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        <a class="button" href="login.php">&larr; Powrót do logowania</a>
    </form>

</body>

</html>
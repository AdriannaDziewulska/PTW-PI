<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

   if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['profile_photo'] = $user['profile_photo'];

    header("Location: calendar.php");
    exit();
    } else {
        $error = "Nieprawidłowe dane logowania";
    }
}
?>


<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8" />
    <title>Logowanie</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <form class="form-login" method="post" action="">
        <h2>Logowanie</h2>
        <input type="text" name="username" placeholder="Nazwa użytkownika" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <button type="submit">Zaloguj</button>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <a href="register.php" class="button">Nie masz konta? Zarejestruj się</a>
    </form>

</body>

</html>
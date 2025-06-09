<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Obsługa zdjęcia profilowego
    $uploadDir = 'uploads/';
    $photoName = null; // domyślnie null

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['profile_photo']['tmp_name'];
        $originalName = basename($_FILES['profile_photo']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extension, $allowed)) {
            $photoName = uniqid('avatar_') . '.' . $extension;
            move_uploaded_file($tmpName, $uploadDir . $photoName);
        }
    }

    // Dodanie użytkownika z profile_photo (może być NULL)
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, profile_photo) VALUES (?, ?, ?, ?)");

    try {
        $stmt->execute([$username, $email, $hash, $photoName]);
        header("Location: login.php?registered=1");
        exit();
    } catch (PDOException $e) {
        $error = "Rejestracja nieudana: " . $e->getMessage();
    }
    var_dump($_FILES['profile_photo']);

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

    <form class="form-login" method="post" enctype="multipart/form-data">
        <h2>Rejestracja</h2>
        <input type="text" name="username" placeholder="Nazwa użytkownika" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <label>Zdjęcie profilowe:</label>
        <input type="file" name="profile_photo" accept="image/*" required>
        <button type="submit">Zarejestruj</button>
        <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        <a class="button" href="login.php">&larr; Powrót do logowania</a>
    </form>

</body>

</html>
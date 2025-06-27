<?php
session_start();

$users = [
    'mitarbeiter1' => password_hash('passwort123', PASSWORD_DEFAULT),
    'admin' => password_hash('adminpass', PASSWORD_DEFAULT)
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location: create.html');
        exit;
    } else {
        $error = '❌ Benutzername oder Passwort ist falsch.';
    }
}
?>


<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mitarbeiter Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #2980b9, #6dd5fa);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      padding: 20px;
    }
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      max-width: 400px;
      width: 100%;
    }
    h2 {
      text-align: center;
      color: #2980b9;
      margin-bottom: 30px;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-top: 8px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }
    .button {
      margin-top: 25px;
      width: 100%;
      padding: 12px;
      background-color: #3498db;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      font-size: 1rem;
      cursor: pointer;
    }
    .button:hover {
      background-color: #2c3e50;
    }
    .error-message {
      color: red;
      margin-top: 10px;
      font-size: 0.9rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Mitarbeiter Login</h2>

    <?php if (!empty($error)) : ?>
  <div class="error-message"><?= $error ?></div>
<?php endif; ?>


    <form action="login_nutzer.php" method="POST">
      <label for="username">Benutzername</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Passwort</label>
      <input type="password" id="password" name="password" required>

      <button type="submit" class="button">Einloggen</button>
    </form>
  </div>
</body>
</html>

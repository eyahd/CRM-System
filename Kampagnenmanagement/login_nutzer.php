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
        header('Location: plannung.php');
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #0f0f0f, #2c3e50);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .login-box {
      background: rgba(0, 0, 0, 0.7);
      padding: 3rem;
      border-radius: 20px;
      width: 100%;
      max-width: 400px;
      backdrop-filter: blur(4px);
      text-align: center;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    }

    .login-box h2 {
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #1e3a8a;
    }

    label {
      display: block;
      text-align: left;
      margin-top: 1rem;
      font-size: 0.95rem;
      font-weight: 500;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-top: 6px;
      border-radius: 8px;
      border: none;
      background: #fff;
      font-size: 1rem;
    }

    .btn {
      margin-top: 2rem;
      background-color: #1e3a8a;
      color:rgb(255, 255, 255);
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background-color: #1e3a8a;
    }

    .error-message {
      color: #ff7b7b;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>Mitarbeiter Login</h2>

    <?php if (!empty($error)) : ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="login_nutzer.php" method="POST">
      <label for="username">Benutzername</label>
      <input type="text" id="username" name="username" required />

      <label for="password">Passwort</label>
      <input type="password" id="password" name="password" required />

      <button type="submit" class="btn">Einloggen</button>
    </form>
  </div>

</body>
</html>

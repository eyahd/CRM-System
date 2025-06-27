 <?php
session_start();

// Beispielhafte Benutzer (in echter Anwendung: Datenbank verwenden)
$users = [
    'mitarbeiter1' => password_hash('passwort123', PASSWORD_DEFAULT),
    'admin' => password_hash('adminpass', PASSWORD_DEFAULT)
];

// Formulardaten prüfen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        
        if (file_exists('create.html')) {
            echo "✅ Datei existiert – Weiterleitung erfolgt.";
          } else {
            echo "❌ Datei 'create.html' nicht gefunden!";
            exit;
          }
          
        header('Location:create.html');
        exit;
    } else {
        echo '
        <div class="error-message">
          <span class="error-icon">&#9888;</span>
          Benutzername oder Passwort ist falsch.
        </div>
        ';
            }
}
?>
<a href="login_nutzer.html" style="display:block; text-align:center; margin-top:20px;">
  Zurück zum Login
</a>
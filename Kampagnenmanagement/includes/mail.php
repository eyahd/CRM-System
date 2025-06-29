<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



$mail = new PHPMailer(true);
$message = "";

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 
    $mail->Password   = 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('', 'Citycar24');
    $mail->addAddress('');
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->isHTML(true);
    $mail->Subject = 'Jetzt sparen: Exklusive Angebote von Citycar24!';
    $mail->Body    ='<b>Guten Tag!</b><br><br>
Als geschätzter Kunde von Citycar24 profitieren Sie regelmäßig von exklusiven Angeboten – abgestimmt auf Ihre individuellen Bedürfnisse und Ihr Fahrverhalten.<br><br>
Ob Cityflitzer, Familienvan oder Businessklasse – bei uns finden Sie das passende Fahrzeug zum besten Preis.<br><br>
Jetzt reinschauen und sparen: <a href="https://www.citycar24.de/angebote">Unsere aktuellen Deals</a><br><br>
Freundliche Grüße<br>
Ihr Citycar24-Team
';

    $mail->send();
    $message = "✅ Nachricht wurde gesendet";
} catch (Exception $e) {
    $message = "❌ Nachricht konnte nicht gesendet werden. Fehler: " . $mail->ErrorInfo;
}
?>

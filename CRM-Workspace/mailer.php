<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendeEmail(string $typ, string $empfaenger_email, string $empfaenger_name, array $daten = []): void
{
    $config = require __DIR__ . '/config/mail_config.php';

    switch ($typ) {
        case 'registrierung':
            $template = __DIR__ . '/mails/kunde_registriert.html';
            $subject = 'Registrierungsbestätigung';
            break;
        case 'daten_update':
            $template = __DIR__ . '/mails/daten_update.html';
            $subject = 'Datenänderung';
            break;
        case 'passwort_update':
            $template = __DIR__ . '/mails/passwort_update.html';
            $subject = 'Passwortänderung';
            break;
        case 'passwort_reset':
            $template = __DIR__ . '/mails/passwort_reset.html';
            $subject = 'Passwort zurücksetzen';
            break;
        case 'email_verification':
            $template = __DIR__ . '/mails/email_verification.html';
            $subject = 'Bestätigen Sie Ihre E-Mail-Adresse';
            break;
        case 'konto_geloescht':
            $template = __DIR__ . '/mails/konto_geloescht.html';
            $subject = 'Konto gelöscht';
            break;
        case 'admin_hinzufuegen':
            $template = __DIR__ . '/mails/admin_hinzufuegen.html';
            $subject = 'Admin erstellen';
            break;
        default:
            throw new Exception(message: "Unbekannter E-Mail-Typ: $typ");
    }
    // E-Mail-Template laden und Platzhalter ersetzen
    $body = file_get_contents(filename: $template);
    error_log("DEBUG Maildaten: " . print_r($daten, true));

    foreach ($daten as $key => $value) {
        $body = str_replace(search: "{{{$key}}}", replace: $value, subject: $body);
    }

    $mail = new PHPMailer(exceptions: true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = $config['port'];
        $mail->SMTPAuth = $config['smtp_auth'];
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->CharSet = $config['charset'];
        $mail->Encoding = $config['encoding'];
        $mail->setFrom(address: $config['from_email'], name: $config['from_name']);
        $mail->addAddress(address: $empfaenger_email, name: $empfaenger_name);
        $mail->isHTML(isHtml: true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        error_log(message: "E-Mail-Fehler: Papercut SMTP nicht gestartet! " . $mail->ErrorInfo);
        file_put_contents(filename: __DIR__ . '/logs/mail_errors.log', data: date(format: 'Y-m-d H:i:s') . " - Fehler: Papercut SMTP nicht gestartet! " . $e->getMessage() . PHP_EOL, flags: FILE_APPEND);
        throw new Exception(message: "E-Mail konnte nicht gesendet werden. Bitte versuchen Sie es später erneut.");
    }
}

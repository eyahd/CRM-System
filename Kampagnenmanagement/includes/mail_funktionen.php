<?php
if (!function_exists('sendeKampagnenMail')) {
    function sendeKampagnenMail($empfaenger, $betreff, $inhalt) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@deinedomain.de\r\n";

        return mail($empfaenger, $betreff, $inhalt, $headers);
    }
}

?>

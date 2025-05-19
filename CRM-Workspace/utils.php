<?php
function validierePasswoerter(string $pw1, string $pw2): ?string {
    if (strlen($pw1) < 8) {
        return "Das Passwort muss mindestens 8 Zeichen lang sein.";
    }
    if ($pw1 !== $pw2) {
        return "Die Passwörter stimmen nicht überein.";
    }
    return null; 
}

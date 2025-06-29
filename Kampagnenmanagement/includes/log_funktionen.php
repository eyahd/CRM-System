<?php
function schreibeLog($text, $datei = "sendelog.txt") {
    $timestamp = date("Y-m-d H:i:s");
    $eintrag = "[$timestamp] " . $text . "\n";
    file_put_contents($datei, $eintrag, FILE_APPEND);
}
?>

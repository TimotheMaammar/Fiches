<?php
if (isset($_GET["url"])) {
    $url = $_GET["url"];
    header("Location: " . $url);
    exit();
} else {
    echo "URL manquante.";
}
?>

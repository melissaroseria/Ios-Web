<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['newPassword'];
    file_put_contents('../../../src/pass.txt', $newPassword);
}
?>
<?php
// Logout endpoint público (o diretório /app não é público no servidor)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

session_unset();
session_destroy();

header('Location: login.php');
exit;

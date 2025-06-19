<?php
require 'config.php';
// Выход из системы
session_unset();
session_destroy();
header('Location: /');
exit;
?>
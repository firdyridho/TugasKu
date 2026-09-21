<?php
require_once __DIR__ . '/../config.php';
if (isLoggedIn()) redirect('/dashboard/');
header('Location: ' . BASE_URL . '/auth/login.php?mode=register');
exit;

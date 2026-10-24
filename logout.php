<?php
/**
 * GlobeTrek Adventures - User Logout
 */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

logout_user();
set_flash('info', 'You have been successfully logged out.');
header('Location: ' . BASE_URL . '/login.php');
exit;

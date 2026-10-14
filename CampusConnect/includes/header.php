<?php
/**
 * includes/header.php
 * Expects an optional $pageTitle variable to be set before include.
 */
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/logo.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= e($css) ?>.css">
    <?php endforeach; endif; ?>
</head>
<body class="<?= isLoggedIn() ? 'app-shell' : '' ?>">
<?php if (isLoggedIn()): ?>
<?php include ROOT_PATH . '/includes/navbar.php'; ?>
<div class="layout">
<?php include ROOT_PATH . '/includes/sidebar.php'; ?>
<main class="content">
<?php $flashes = getFlashes(); ?>
<?php foreach ($flashes as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<?php else: ?>
<?php $flashes = getFlashes(); ?>
<?php foreach ($flashes as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?? 'AgroPay' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/agropay/assets/css/styles.css">
    <?php if (isset($css_extra)): ?>
    <link rel="stylesheet" href="/agropay/assets/css/<?= $css_extra ?>">
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">
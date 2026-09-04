<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Monev TKA') ?></title>
   <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome/css/solid.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome/css/regular.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome/css/brands.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/css/dataTables.bootstrap5.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/notification.css') ?>">
    <?php if(!empty($pageAsset)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/'.$pageAsset.'.css') ?>?v=<?= time() ?>">
    <?php endif; ?>
    <?php if(!empty($pageCss)): ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/'.$pageCss.'.css') ?>">
    <?php endif; ?>
</head>
<body>
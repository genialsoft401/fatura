<?php
require_once '../app/helpers/translation.php';
require_once '../app/config/db.php';
require_once '../app/helpers/functions.php';
require_once '../app/helpers/authentication.php';
require_once '../app/views/head.php';
?>

<style>
    .app-wrapper {
        display: flex;
        /* min-height: 100vh; */
    }

    .main-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .content {
        padding: 20px;
        flex: 1;
        overflow-x: hidden;
    }
</style>

<div class="app-wrapper">

    <!-- SIDEBAR -->
    <?php require_once '../app/views/side.php'; ?>

    <div class="main-wrapper">

        <!-- NAVBAR -->
        <?php require_once '../app/views/nav.php'; ?>

        <!-- CONTEÚDO REAL DA PÁGINA -->
        <main class="content">
        </main>
    </div>
</div>
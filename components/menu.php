<?php
if (!defined('IMDB')) {
    http_response_code(403);
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$p = defined('ACTIVE_PAGE') ? ACTIVE_PAGE : "";
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" href="/">IMDb</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link<?= ($p == "index") ? " active":"" ?>" href="/">Főoldal</a>
                <a class="nav-link<?= ($p == "search") ? " active":"" ?>" href="/search.php">Keresés</a>
                <a class="nav-link<?= ($p == "media_edit") ? " active":"" ?>" href="/media_edit.php">Tartalom hozzáadása</a>
            </div>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a class="nav-link<?= ($p == "login") ? " active":"" ?>" href="profile.php"><?= $_SESSION['user_fullname'] ?></a>
                    <a class="nav-link" href="/backend/logout.php">Kijelentkezés</a>
                <?php } else { ?>
                    <a class="nav-link<?= ($p == "login") ? " active":"" ?>" href="/login.php">Bejelentkezés</a>
                    <a class="nav-link<?= ($p == "register") ? " active":"" ?>" href="/register.php">Regisztráció</a>
                <?php } ?>
            </div>
        </div>
    </div>
</nav>
<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}
define('IMDB', true);
define('TITLE', 'Regisztráció');
define('ACTIVE_PAGE', 'register');
?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>

<body>
    <?php require 'components/menu.php'; ?>
    <div class="container mt-4" style="max-width: 400px;">
        <h1>Regisztráció</h1>
        <form action="/backend/register.php" method="POST" class="mt-5">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="name@example.com" required>
                <label for="fullname">Teljes név</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" id="username" name="username" placeholder="name" required maxlength="100"
                    class="form-control<?= (isset($_GET["error"]) && $_GET["error"] == "username_taken") ? " is-invalid" : "" ?>">
                <label for="username">Felhasználónév</label>
                <div class="invalid-feedback">
                    A felhasználónév már foglalt!
                </div>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="8">
                <label for="password">Jelszó</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" id="password2" name="password2" placeholder="Password" required minlength="8"
                    class="form-control<?= ((isset($_GET["error"]) && $_GET["error"] === "no_match") ? " is-invalid" : "") ?>">
                <label for="password2">Jelszó újra</label>
                <div class="invalid-feedback">
                    A két jelszó nem egyezik meg!
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required>
                <label class="form-check-label" for="flexCheckDefault">
                    A lelkemet átadom örökös szolgálatra a kárhozatban.
                </label>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Regisztráció</button>
            </div>
        </form>
    </div>
    <?php require 'components/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fullname = document.getElementById('fullname');
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            const password2 = document.getElementById('password2');

            const inputs = [fullname, username, password, password2];
            inputs.forEach(input => {
                input.value = localStorage.getItem(input.id) || '';
                input.addEventListener('change', () => {
                    localStorage.setItem(input.id, input.value);
                });
            });
        });
    </script>
</body>

</html>
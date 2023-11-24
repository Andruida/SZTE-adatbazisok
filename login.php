<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}
define('IMDB', true);
define('TITLE', 'Bejelentkezés');
define('ACTIVE_PAGE', 'login');
?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>

<body>
    <?php require 'components/menu.php'; ?>
    <div class="container mt-4" style="max-width: 400px;">
        <h1>Bejelentkezés</h1>
        <form action="/backend/login.php" method="POST" class="mt-5">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="name@example.com" required>
                <label for="username">Felhasználónév</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Jelszó</label>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Bejelentkezés</button>
            </div>
            <?php if (isset($_GET["error"]) && $_GET["error"] == "invalid") { ?>
            <div class="alert alert-danger mt-4" role="alert">
                Hibás felhasználónév vagy jelszó!
            </div>
            <?php } ?>
        </form>

    </div>
    <?php require 'components/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const username = document.getElementById('username');
            const password = document.getElementById('password');

            const inputs = [username, password];
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
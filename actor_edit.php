<?php
define('IMDB', true);
define('name', 'Színész szerkesztése');
define('ACTIVE_PAGE', 'actor_edit');
require_once 'backend/conn.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['id'])) {
        header('Location: /actor.php?id='.$_GET['id']);
    } else {
        header('Location: /');
    }
    exit;
}

$new = !isset($_GET['id']);

if (!$new) {
    $stmt = $conn->prepare('SELECT `id`, `name`, `birthday`, `country` FROM actors WHERE id = :id');
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();

    $actor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$actor) {
        http_response_code(404);
        exit;
    }
}

$stmt = $conn->query('SELECT `id`, `name` FROM `countries` ORDER BY `name` ASC;');
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="hu">
<?php require "components/head.php"; ?>
<body>
    <?php require "components/menu.php"; ?>
    <div class="container mt-4" style="max-width: 600px;">
        <h1>Színészek</h1>
        <form action="/backend/actor_edit.php" method="post" class="mt-4">
            <input type="hidden" name="id" value="<?= $actor["id"] ?? '' ?>">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name" placeholder="name" required
                    value="<?= ($new) ? "" : $actor["name"] ?>">
                <label for="name">Név</label>
            </div>
            <div class="form-floating mb-3">
                <input type="date" class="form-control" id="birthday" name="birthday" placeholder="birthday" required
                    value="<?= ($new) ? "" : $actor["birthday"] ?>">
                <label for="birthday">Születési dátum</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="country" name="country" required>
                    <option value="" disabled <?= ($new) ? "selected" : "" ?>></option>
                    <?php foreach ($countries as $country) { ?>
                    <option value="<?= $country['id'] ?>" <?= ($country['id'] == $actor["country"]) ? "selected" : "" ?>>
                        <?= $country['name'] ?>
                    </option>
                    <?php } ?>
                </select>
                <label for="country">Ország</label>
            </div>
            <div class="d-grid">
                <button class="btn btn-primary" type="submit"><?= ($new) ? "Létrehozás" : "Mentés" ?></button>
            </div>

        </form>
    </div>
    <?php require "components/footer.php"; ?>
</body>
</html>
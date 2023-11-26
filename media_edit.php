<?php
define('IMDB', true);
define('TITLE', 'Média szerkesztése');
define('ACTIVE_PAGE', 'media_edit');
require_once 'backend/conn.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['id'])) {
        header('Location: /media.php?id='.$_GET['id']);
    } else {
        header('Location: /');
    }
    exit;
}

if (isset($_GET['id'])) {
    $stmt = $conn->prepare(
    "SELECT 
        r.id as id, r.title as title, g.name as genre, 
        r.movie as movie_id, r.series as series_id, 
        m.length as length, m.released as released, 
        s.seasons as seasons, s.episodes as episodes
    FROM `media` r
    LEFT JOIN `movies` m ON r.movie = m.id
    LEFT JOIN `series` s ON r.series = s.id
    LEFT JOIN `genres` g ON r.genre = g.id
    WHERE r.id = :id
    GROUP BY r.id
    ORDER BY r.title ASC;"
    );
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();

    $media = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$media) {
        http_response_code(404);
        exit;
    }

    $stmt = $conn->prepare(
    "SELECT a.id as id, a.name as `name`, am.role as `role` FROM `actor_media` am
    LEFT JOIN `actors` a ON a.id = am.actor
    WHERE am.media = :id
    ORDER BY a.name ASC;"
    );
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $media_actors = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

$stmt = $conn->query("SELECT `id`, `name` FROM `actors` ORDER BY `name` ASC;");
$actors = $stmt->fetchAll(PDO::FETCH_ASSOC);
array_unshift($actors, ['id' => -1, 'name' => '']);

$stmt = $conn->query("SELECT `name` FROM `genres` ORDER BY `name` ASC;");
$genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$new = !isset($_GET['id']);
$new_movie = isset($_GET['movie']);
$new_series = isset($_GET['series']);
?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>
<body>
    <?php require 'components/menu.php'; ?>
    <div class="container mt-4" style="max-width: 600px;">
        <?php if (!$new || $new_movie || $new_series) { ?>
        <?php if ($new) { ?>
        <h1><?= ($new_movie) ? "Új film hozzáadása" : "Új sorozat hozzáadása" ?></h1>
        <?php } else { ?>
        <h1><?= $media['title'] ?> szerkesztése</h1>
        <?php } ?>
        <form action="/backend/media_edit.php" method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= $media['id'] ?? '' ?>">
            <?php if ($new_movie) { ?>
            <input type="hidden" name="movie" value="1">
            <?php } ?>
            <?php if ($new_series) { ?>
            <input type="hidden" name="series" value="1">
            <?php } ?>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="title" name="title" placeholder="Title" required
                    value="<?= ($new) ? "" : $media["title"] ?>">
                <label for="title">Cím</label>
            </div>
            <div class="form-floating mb-3">
                <datalist id="genres">
                    <?php foreach ($genres as $genre) { ?>
                    <option value="<?= $genre['name'] ?>">
                    <?php } ?>
                </datalist>
                <input type="text" list="genres" class="form-control" id="genre" name="genre" placeholder="Genre" required
                    value="<?= ($new) ? "" : $media["genre"] ?>">
                <label for="genre">Műfaj</label>
            </div>
            <?php if (!empty($media["movie_id"]) || $new_movie) { ?>
            <div class="form-floating mb-3">
                <input type="number" class="form-control" id="length" name="length" placeholder="Length" required
                    value="<?= ($new) ? "" : $media["length"] ?>">
                <label for="length">Hossz (perc)</label>
            </div>
            <div class="form-floating mb-3">
                <input type="number" class="form-control" id="released" name="released" placeholder="Released" required
                    value="<?= ($new) ? "" : $media["released"] ?>">
                <label for="released">Megjelenési év</label>
            </div>
            <?php } ?>
            <?php if (!empty($media["series_id"]) || $new_series) { ?>
            <div class="form-floating mb-3">
                <input type="number" class="form-control" id="seasons" name="seasons" placeholder="Seasons" required
                    value="<?= ($new) ? "" : $media["seasons"] ?>">
                <label for="seasons">Évadok</label>
            </div>
            <div class="form-floating mb-3">
                <input type="number" class="form-control" id="episodes" name="episodes" placeholder="Episodes" required
                    value="<?= ($new) ? "" : $media["episodes"] ?>">
                <label for="episodes">Epizódok</label>
            </div>
            <?php } ?>
            <h2>Színészek</h2>
            <?php foreach ($media_actors as $k => $media_actor) { ?>
            <div class="row g-2 mb-3">
                <div class="col-auto">
                    <div class="form-floating">
                        <select class="form-select" id="actor-<?= $k ?>" name="actors[<?= $k ?>]" required>
                            <?php foreach ($actors as $actor) { ?>
                            <option value="<?= $actor['id'] ?>" <?= ($media_actor["id"] == $actor["id"]) ? "selected" : "" ?>>
                                <?= $actor['name'] ?>
                            </option>
                            <?php } ?>
                        </select>
                        <label for="actor-<?= $k ?>">Színész</label>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="role-<?= $k ?>" name="roles[<?= $k ?>]" placeholder="role" required
                            value="<?= ($new) ? "" : $media_actor["role"] ?>">
                        <label for="role-<?= $k ?>">Szerep</label>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="row g-2 mb-3">
                <div class="col-auto">
                    <div class="form-floating">
                        <select class="form-select" id="actor-new" name="actors[]">
                            <?php foreach ($actors as $actor) { ?>
                            <option value="<?= $actor['id'] ?>" <?= ($actor['id'] == -1) ? "selected" : "" ?>>
                                <?= $actor['name'] ?>
                            </option>
                            <?php } ?>
                        </select>
                        <label for="actor-new">Színész</label>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="role-new" name="roles[]" placeholder="role"
                            value="">
                        <label for="role-new">Szerep</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 mt-4 mb-2">
                <button type="submit" class="btn btn-primary"><?= ($new) ? "Létrehozás" : "Mentés" ?></button>
            </div>
            <?php if (!$new) { ?>
            <div class="d-grid gap-2 mb-5">
                <a href="/media.php?id=<?= $media["id"] ?>"><button type="button" class="btn btn-secondary" style="width: 100%;">Vissza</button></a>
            </div>
            <?php } ?>

        </form>
        <?php } else { ?>
        <div class="text-center">
            <a href="/media_edit.php?movie" class="btn btn-primary mb-3" style="max-width: 200px; width: 100%;">Film
                hozzáadása</a>
            <a href="/media_edit.php?series" class="btn btn-primary mb-3" style="max-width: 200px; width: 100%;">Sorozat
                hozzáadása</a>
            <a href="/actor_edit.php" class="btn btn-primary mb-3" style="max-width: 200px; width: 100%;">Színész
                hozzáadása</a>
        </div>
        <?php } ?>
    </div>
    <?php require 'components/footer.php'; ?>
</body>

</html>
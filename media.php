<?php
define('IMDB', true);
define('TITLE', 'Média megtekintése');
define('ACTIVE_PAGE', 'media');
if (!isset($_GET['id'])) {
    http_response_code(404);
}

require_once 'backend/conn.php';

if (!isset($_GET["id"])) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare(
"SELECT 
    r.id as id, r.title as title, g.name as genre, 
    r.movie as movie_id, r.series as series_id, 
    m.length as length, m.released as released, 
    s.seasons as seasons, s.episodes as episodes, 
    AVG(r2.rating) as avg_rating, COUNT(r2.rating) as ratings 
FROM `media` r
LEFT JOIN `movies` m ON r.movie = m.id
LEFT JOIN `series` s ON r.series = s.id
LEFT JOIN `genres` g ON r.genre = g.id
LEFT JOIN `ratings` r2 ON r2.media = r.id
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

$stmt = $conn->prepare(
    "SELECT r.rating as rating, u.name as `name` FROM `ratings` r
    LEFT JOIN `users` u ON u.id = r.user
    WHERE r.media = :id
    ORDER BY u.name ASC;"
);
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_rating = null;
session_start();
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare(
        "SELECT `rating` FROM `ratings` WHERE `media` = :media AND `user` = :user;"
    );
    $stmt->bindParam(':media', $_GET['id']);
    $stmt->bindParam(':user', $_SESSION['user_id']);
    $stmt->execute();
    $user_rating = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user_rating) {
        $user_rating = $user_rating['rating'];
    } else {
        $user_rating = null;
    }
}

?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>
<body>
    <?php require 'components/menu.php'; ?>
    
    <div class="container mt-4" style="max-width: 600px;">
        <h1><?= $media["title"] ?></h1>
        <?php if (isset($_SESSION['user_id'])) { ?>
        <div class="row g-2">
            <div class="col-auto">
                <a href="media_edit.php?id=<?= $media["id"] ?>">
                    <button class="btn btn-primary">Szerkesztés</button>
                </a>
            </div>
            <div class="col-auto">
                <form action="/backend/media_delete.php" method="post">
                    <input type="hidden" name="id" value="<?= $media["id"] ?>">
                    <button type="submit" class="btn btn-danger">Törlés</button>
                </form>
            </div>
        </div>
        <?php } ?>
        <div class="table-responsive my-4">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th>Cím</th>
                        <td><?= $media["title"] ?></td>
                    </tr>
                    <tr>
                        <th>Műfaj</th>
                        <td><?= $media["genre"] ?></td>
                    </tr>
                    <?php if ($media["movie_id"] !== null) { ?>
                    <tr>
                        <th>Típus</th>
                        <td>Film</td>
                    </tr>
                    <tr>
                        <th>Hossz</th>
                        <td><?= $media["length"] ?> perc</td>
                    </tr>
                    <tr>
                        <th>Megjelenés</th>
                        <td><?= $media["released"] ?></td>
                    </tr>
                    <?php } else if ($media["series_id"] !== null) { ?>
                    <tr>
                        <th>Típus</th>
                        <td>Sorozat</td>
                    </tr>
                    <tr>
                        <th>Évadok</th>
                        <td><?= $media["seasons"] ?></td>
                    </tr>
                    <tr>
                        <th>Epizódok</th>
                        <td><?= $media["episodes"] ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <th>Értékelés</th>
                        <td>
                            <?php if ($media['ratings'] > 0) { ?>
                            <?= number_format($media['avg_rating']/10, 1) ?> / 10 (<?= $media['ratings'] ?>)
                            <?php } else { ?>
                            - / 10 (0)
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <h2>Színészek</h2>
        <div class="table-responsive mt-3 mb-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Név</th>
                        <th>Szerep</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($media_actors as $media_actor) { ?>
                    <tr>
                        <td><a href="actor.php?id=<?= $media_actor["id"] ?>"><?= $media_actor["name"] ?></a></td>
                        <td><?= $media_actor["role"] ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <h2>Értékelések</h2>
        <div class="table-responsive mt-3 mb-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Felhasználó</th>
                        <th>Értékelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ratings as $rating) { ?>
                    <tr>
                        <td><?= $rating["name"] ?></td>
                        <td><?= number_format($rating["rating"]/10, 1) ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($_SESSION['user_id'])) { ?>
        <form action="/backend/rate.php" method="POST">
            <input type="hidden" name="id" value="<?= $media["id"] ?>">
            <label for="rating" class="form-label">Értékelés</label>
            <input type="range" name="rating" min="0" max="100" step="1" class="form-range" id="rating"
                value="<?= $user_rating ?? 50  ?>">
            <h3 class="text-center" id="ratingDisplay"><?= number_format(($user_rating ?? 50)/10, 1)?></h3>
            <div class="text-center">
                <button type="submit" class="btn btn-primary mt-3">Értékelés küldése</button>
            </div>
        </form>
        <?php } ?>
        
    </div>
    <div style="height: 300px;"></div>
    <?php require 'components/footer.php'; ?>
    <script>
        const rating = document.getElementById('rating');
        const ratingDisplay = document.getElementById('ratingDisplay');
        addEventListener('DOMContentLoaded', () => {
            ratingDisplay.innerText = (rating.value / 10).toFixed(1);
        });
        rating.addEventListener('input', () => {
            ratingDisplay.innerText = (rating.value / 10).toFixed(1);
        });

    </script>
</body>

</html>
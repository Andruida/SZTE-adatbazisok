<?php
define('IMDB', true);
define('TITLE', 'Média megtekintése');
define('ACTIVE_PAGE', 'media');
if (!isset($_GET['id'])) {
    http_response_code(404);
}

require_once 'backend/conn.php';

session_start();
if (!isset($_GET["id"])) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare(
    "SELECT a.id, a.name, a.country as cc, c.name as country FROM `actors` a
    LEFT JOIN countries c ON a.country = c.id
    WHERE a.id = :id;"
);
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$actor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$actor) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare(
    "SELECT 
        am.actor as id, m.id as media_id, am.role as role, m.movie as movie_id, 
        m.series as series_id, m.title as title, g.name as genre,
        AVG(r.rating) as avg_rating, COUNT(r.rating) as ratings
    FROM `actor_media` am
    LEFT JOIN `media` m ON am.media = m.id
    LEFT JOIN `genres` g ON g.id = m.genre
    LEFT JOIN `ratings` r ON r.media = m.id
    WHERE am.actor = :id
    GROUP BY am.actor, m.id
    ORDER BY avg_rating DESC;"
);
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>
<body>
    <?php require 'components/menu.php'; ?>
    
    <div class="container mt-4" style="max-width: 600px;">
        <h1><?= $actor["name"] ?></h1>
        <?php if (isset($_SESSION['user_id'])) { ?>
        <div class="row g-2">
            <div class="col-auto">
                <a href="actor_edit.php?id=<?= $media["id"] ?>">
                    <button class="btn btn-primary">Szerkesztés</button>
                </a>
            </div>
            <div class="col-auto">
                <form action="/backend/actor_delete.php" method="post">
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
                        <th scope="row">Név</th>
                        <td><?= $actor["name"] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Ország</th>
                        <td><?= $actor["country"] ?> (<?= $actor["cc"] ?>)</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <h2>Szerepek</h2>
        <div class="table-responsive my-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Cím</th>
                        <th scope="col">Típus</th>
                        <th scope="col">Műfaj</th>
                        <th scope="col">Szerep</th>
                        <th scope="col">Értékelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role) { ?>
                    <tr>
                        <td><a href="/media.php?id=<?= $role["media_id"] ?>"><?= $role["title"] ?></a></td>
                        <td>
                            <?php if ($role["movie_id"] !== null) { ?>
                            Film
                            <?php } else if ($role["series_id"] !== null) { ?>
                            Sorozat
                            <?php } ?>
                        </td>
                        <td><?= $role["genre"] ?></td>
                        <td><?= $role["role"] ?></td>
                        <td>
                            <?php if ($role["avg_rating"] !== null) { ?>
                            <?= number_format($role["avg_rating"]/10, 1) ?> (<?= $role["ratings"] ?>)
                            <?php } else { ?>
                            Nincs értékelés
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
            </table>
        </div>
    </div>
    <?php require 'components/footer.php'; ?>
</body>

</html>
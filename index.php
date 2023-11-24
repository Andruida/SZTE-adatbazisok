<?php
define('IMDB', true);
define('TITLE', 'Főoldal');
define('ACTIVE_PAGE', 'index');

require_once 'backend/conn.php';

$stmt = $conn->query("SELECT `title` FROM `media`");
$titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare(
"SELECT 
    m.id as id, m.title as title, g.name as genre, 
    m2.length as length, m2.released as released, 
    AVG(r.rating) as avg_rating, COUNT(r.rating) as ratings 
FROM `media` m
INNER JOIN `movies` m2 ON m.movie = m2.id
LEFT JOIN `ratings` r ON m.id = r.media
LEFT JOIN `genres` g ON m.genre = g.id
WHERE m2.released > 2019
GROUP BY m.id
ORDER BY avg_rating DESC
LIMIT 5;
");
$stmt->execute();
$bestMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare(
    "SELECT y.value as `year`, COUNT(m.id) as `count` FROM `media` m
    INNER JOIN `movies` m2 ON m.movie = m2.id
    RIGHT JOIN 
    (SELECT (@val := @val + 1) - 1+2000 AS value FROM media, 
    (SELECT @val := 0) AS tt) y ON y.value = m2.released
    WHERE y.value BETWEEN 2000 AND year(curdate())
    GROUP BY y.value
    ORDER BY y.value DESC;
    ");
$stmt->execute();
$years = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare(
    "SELECT a.name FROM `actors` a
    LEFT JOIN actor_media am ON a.id = am.actor
    LEFT JOIN `media` m ON am.media = m.id
    WHERE m.movie IS NOT NULL
    GROUP BY a.id
    ORDER BY COUNT(am.media) DESC
    LIMIT 1;
    ");
$stmt->execute();
$bestActorRow = $stmt->fetch(PDO::FETCH_ASSOC);
if ($bestActorRow) {
    $bestActor = $bestActorRow['name'];
} else {
    $bestActor = "-";
}

$stmt = $conn->prepare(
    "SELECT m.id as id, m.title as title, g.name as genre, 
        s.episodes as episodes, s.seasons as seasons, 
        am.role as role,
        AVG(r.rating) as avg_rating, COUNT(r.rating) as ratings 
    FROM actor_media am
    LEFT JOIN `media` m ON m.id = am.media
    INNER JOIN `series` s ON s.id = m.series
    LEFT JOIN `ratings` r ON m.id = r.media
    LEFT JOIN `genres` g ON m.genre = g.id
    WHERE am.actor = (
        SELECT a.id FROM `actors` a
        LEFT JOIN actor_media am ON a.id = am.actor
        LEFT JOIN `media` m ON am.media = m.id
        WHERE m.movie IS NOT NULL
        GROUP BY a.id
        ORDER BY COUNT(am.media) DESC
        LIMIT 1
    )
    GROUP BY am.id
    ORDER BY avg_rating DESC;
    ");

$stmt->execute();
$series_by_the_best = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
movies_of_actors VIEW:

SELECT 
    `m`.`id` AS `id`, `m`.`title` AS `title`,
    `g`.`name` AS `genre`, `m2`.`length` AS `length`,
    `m2`.`released` AS `released`, `am`.`role` AS `role`,
    `a`.`name` AS `actor`, AVG(`r`.`rating`) AS `avg_rating`,
    COUNT(`r`.`rating`) AS `ratings`,`a`.`id` AS `a_id` 
FROM `actor_media` `am`
LEFT JOIN `actors` `a` ON `a`.`id` = `am`.`actor`
LEFT JOIN `media` `m` ON `m`.`id` = `am`.`media`
INNER JOIN `movies` `m2` ON `m2`.`id` = `m`.`movie`
LEFT JOIN `ratings` `r` ON `m`.`id` = `r`.`media`
LEFT JOIN `genres` `g` ON `m`.`genre` = `g`.`id`
GROUP BY `a`.`id`, `m`.`id`
ORDER BY `a`.`name` ASC;;
*/

$stmt = $conn->prepare(
    "SELECT d1.* FROM 
    movies_of_actors as d1
    LEFT JOIN movies_of_actors d2 
    ON d1.a_id = d2.a_id AND d1.avg_rating < d2.avg_rating
    WHERE d2.avg_rating IS NULL
    ORDER BY `d1`.`actor` ASC;
    ");

$stmt->execute();
$best_movies_of_actors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>
<body>
    <?php require 'components/menu.php'; ?>
    <div class="container mt-4">
        <h1>IMDb</h1>
        <datalist id="titles">
            <?php foreach ($titles as $title) { ?>
            <option value="<?= $title['title'] ?>">
            <?php } ?>
        </datalist>
        <form action="/search.php" class="mt-5 mx-auto" style="max-width: 800px;">
            <label>Keresés a következőkben:</label>
            <div class="form-check">
                <input class="form-check-input" checked type="checkbox" value="" id="movies" name="movies">
                <label class="form-check-label" for="movies">
                    Filmek
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" checked type="checkbox" value="" id="series" name="series">
                <label class="form-check-label" for="series">
                    Sorozatok
                </label>
            </div>
            <div class="form-floating mt-3 mb-3">
                <input type="search" list="titles" value="<?= $_GET["q"] ?? "" ?>" class="form-control" id="search" name="q" placeholder="name@example.com">
                <label for="search">Keresés</label>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary mb-3" style="max-width: 200px; width: 100%;">Keresés</button>
            </div>
        </form>

        <h2 class="mt-5">Legmagasabban értékelt filmek 2019 után</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cím</th>
                        <th>Műfaj</th>
                        <th>Hossz</th>
                        <th>Megjelenés</th>
                        <th>Értékelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bestMovies as $movie) { 
                        $genre = explode("|", $movie['genre']);
                        $genre = implode(", ", $genre);
                    ?>
                    <tr>
                        <td><a href="/media.php?id=<?= $movie['id'] ?>"><?= $movie['title'] ?></a></td>
                        <td><?= $genre ?></td>
                        <td><?= $movie['length'] ?> perc</td>
                        <td><?= $movie['released'] ?></td>
                        <td>
                            <?php if ($movie['ratings'] > 0) { ?>
                            <?= number_format($movie['avg_rating']/10, 1) ?> / 10 (<?= $movie['ratings'] ?>)
                            <?php } else { ?>
                            - / 10 (0)
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <h2 class="mt-5">Kiadott filmek száma évekre lebontva</h2>
        <div class="table-responsive" style="max-width: 200px;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Év</th>
                        <th style="text-align: right;">Filmek száma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($years as $year) { ?>
                    <tr>
                        <td><?= $year['year'] ?></td>
                        <td style="text-align: right;"><?= $year['count'] ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <h2 class="mt-5">Legtöbb filmben szereplő színész (<?= $bestActor ?>) sorozatai</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cím</th>
                        <th>Műfaj</th>
                        <th>Évadok</th>
                        <th>Részek</th>
                        <th>Szerep</th>
                        <th>Értékelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($series_by_the_best as $series) { 
                        $genre = explode("|", $series['genre']);
                        $genre = implode(", ", $genre);
                    ?>
                    <tr>
                        <td><a href="/media.php?id=<?= $series['id'] ?>"><?= $series['title'] ?></a></td>
                        <td><?= $genre ?></td>
                        <td><?= $series['seasons'] ?> évad</td>
                        <td><?= $series['episodes'] ?> rész</td>
                        <td><?= $series['role'] ?></td>
                        <td>
                            <?php if ($series['ratings'] > 0) { ?>
                            <?= number_format($series['avg_rating']/10, 1) ?> / 10 (<?= $series['ratings'] ?>)
                            <?php } else { ?>
                            - / 10 (0)
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <h2 class="mt-5">Színészek legjobb filmjei</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Színész</th>
                        <th>Cím</th>
                        <th>Műfaj</th>
                        <th>Hossz</th>
                        <th>Megjelenés</th>
                        <th>Értékelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($best_movies_of_actors as $movie) { 
                        $genre = explode("|", $movie['genre']);
                        $genre = implode(", ", $genre);
                    ?>
                    <tr>
                        <td><?= $movie['actor'] ?></td>
                        <td><a href="/media.php?id=<?= $movie['id'] ?>"><?= $movie['title'] ?></a></td>
                        <td><?= $genre ?></td>
                        <td><?= $movie['length'] ?> perc</td>
                        <td><?= $movie['released'] ?></td>
                        <td>
                            <?php if ($movie['ratings'] > 0) { ?>
                            <?= number_format($movie['avg_rating']/10, 1) ?> / 10 (<?= $movie['ratings'] ?>)
                            <?php } else { ?>
                            - / 10 (0)
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        
    </div>
    <?php require 'components/footer.php'; ?>
    <script>
        localStorage.removeItem("fullname");
        localStorage.removeItem("username");
        localStorage.removeItem("password");
        localStorage.removeItem("password2");
    </script>
</body>

</html>
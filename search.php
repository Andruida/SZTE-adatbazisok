<?php
define('IMDB', true);
define('TITLE', 'Keresés');
define('ACTIVE_PAGE', 'search');

require_once 'backend/conn.php';

$conditions = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $q = '%'.$_GET['q'].'%';
    $conditions[] = "r.title LIKE :q";
} else {
    $q = null;
}
if (isset($_GET['movies']) && isset($_GET['series'])) {
    // do nothing
} else if (isset($_GET['movies'])) {
    $conditions[] = "r.movie IS NOT NULL";
} else if (isset($_GET['series'])) {
    $conditions[] = "r.series IS NOT NULL";
}

$where = count($conditions) > 0 ? 'WHERE '.implode(' AND ', $conditions) : '';


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
$where
GROUP BY r.id
ORDER BY r.title ASC;
");
if ($q !== null) {
    $stmt->bindParam(':q', $q);
}
$stmt->execute();
$media = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT `title` FROM `media`");
$titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$default = (!isset($_GET['movies']) && !isset($_GET['series'])) || (isset($_GET['movies']) && isset($_GET['series']));
?>
<!doctype html>
<html lang="hu">
<?php require 'components/head.php'; ?>
<body>
    <?php require 'components/menu.php'; ?>
    <div class="container mt-4">
        <h1>Keresés</h1>
        <datalist id="titles">
            <?php foreach ($titles as $title) { ?>
            <option value="<?= $title['title'] ?>">
            <?php } ?>
        </datalist>
        <form action="/search.php" class="mt-5 mx-auto" style="max-width: 800px;">
            <label>Keresés a következőkben:</label>
            <div class="form-check">
                <input class="form-check-input" <?= (isset($_GET["movies"]) || $default) ? "checked" : "" ?> type="checkbox" value="" id="movies" name="movies">
                <label class="form-check-label" for="movies">
                    Filmek
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" <?= (isset($_GET["series"]) || $default) ? "checked" : "" ?> type="checkbox" value="" id="series" name="series">
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

        <div class="table-responsive">
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th scope="col">Cím</th>
                        <?php if ($default) { ?>
                        <th scope="col">Típus</th>
                        <?php } ?>
                        <th scope="col">Műfaj</th>
                        <?php if (isset($_GET['movies']) || $default) { ?>
                        <th scope="col">Hossz</th>
                        <th scope="col">Megjelenés</th>
                        <?php } ?>
                        <?php if (isset($_GET['series']) || $default) { ?>
                        <th scope="col">Évadok</th>
                        <?php } ?>
                        <th scope="col">Értékelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($media as $medium) {
                        $genre = explode("|", $medium['genre']);
                        $genre = implode(", ", $genre);
                    ?>
                    <tr>
                        <td><a href="/media.php?id=<?= $medium['id'] ?>"><?= $medium['title'] ?></a></td>
                        <?php if ($default) { ?>
                        <td><?= ($medium['movie_id'] == null) ? "Sorozat" : "Film" ?></td>
                        <?php } ?>
                        <td><?= $genre ?></td>
                        <?php if (isset($_GET['movies']) || $default) { ?>
                        <td><?= ($medium['length'] == null) ? "" : $medium['length']." perc" ?></td>
                        <td><?= ($medium['released'] == null) ? "" : $medium['released'] ?></td>
                        <?php } ?>
                        <?php if (isset($_GET['series']) || $default) { ?>
                        <td><?= ($medium['seasons'] == null) ? "" : $medium['seasons']." évad (".$medium['episodes']." rész)" ?></td>
                        <?php } ?>
                        <td>
                            <?php if ($medium['ratings'] > 0) { ?>
                            <?= number_format($medium['avg_rating']/10, 1) ?> / 10 (<?= $medium['ratings'] ?>)
                            <?php } else { ?>
                            - / 10 (0)
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require 'components/footer.php'; ?>
</body>

</html>
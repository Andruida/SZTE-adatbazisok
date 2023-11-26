<?php
define("IMDB", true);
define("TITLE", "Színészek");
define("ACTIVE_PAGE", "actors");

require_once "backend/conn.php";
session_start();

$stmt = $conn->query(
    "SELECT a.id, a.name, a.birthday, a.country as cc, c.name as country FROM `actors` a
    LEFT JOIN countries c ON a.country = c.id
    ORDER BY a.name;"
);
$actors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="hu">
<?php require "components/head.php"; ?>
<body>
    <?php require "components/menu.php"; ?>
    <div class="container mt-4" style="max-width: 600px;">
        <h1>Színészek</h1>
        <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="actor_edit.php">
            <button class="btn btn-primary">Színész hozzáadása</button>
        </a>
        <?php } ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Név</th>
                        <th>Születési dátum</th>
                        <th>Ország</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actors as $actor) { ?>
                    <tr>
                        <td><a href="actor.php?id=<?= $actor["id"] ?>"><?= $actor["name"] ?></a></td>
                        <td><?= $actor["birthday"] ?></td>
                        <td><?= $actor["country"] ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require "components/footer.php"; ?>
</body>
</html>
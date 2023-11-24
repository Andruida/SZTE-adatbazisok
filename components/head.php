<?php
if (!defined('IMDB')) {
    http_response_code(403);
    exit;
}
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IMDb<?= (defined('TITLE')) ? " - ".TITLE : "" ?></title>
    <style>
        input:required + label:after {
            content: '*';
            color: red;
            padding-left: 5px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
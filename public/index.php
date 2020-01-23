<?php
require_once '../vendor/autoload.php';

define('CLASS_DIR', 'app/');

spl_autoload_register(function ($class_name) {
    require_once sprintf(
        '%s%s.php',
        CLASS_DIR,
        $class_name
    );
});

//Load Twig templating environment
$loader = new Twig_Loader_Filesystem('../templates/');
$twig = new Twig_Environment($loader, ['debug' => true]);

$episodes = new Episodes();

$seasonFilter = ($_POST['season']) ?? null;

$episodesList = $episodes->get($seasonFilter);
$seasonsList = $episodes->retrieveSeasons($episodesList);

//Render the template
echo $twig->render(
    'page.html',
    [
        "episodes" => $episodesList,
        "seasons" => $seasonsList,
        "seasonFilter" => $seasonFilter,
    ]
);
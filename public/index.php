<?php
require_once '../vendor/autoload.php';

const EPISODES_CACHE_KEY = 'simpsons-episodes';
const CACHE_EXPIRY = 10;

$episodesCacheKey = sprintf(EPISODES_CACHE_KEY, $_SERVER['SERVER_ADDR']);

//Load Twig templating environment
$loader = new Twig_Loader_Filesystem('../templates/');
$twig = new Twig_Environment($loader, ['debug' => true]);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

if ($redis->get($episodesCacheKey)) {

    $data = json_decode($redis->get($episodesCacheKey), true);

} else {

    //Get the episodes from the API
    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', 'http://3ev.org/dev-test-api/');
    $data = json_decode($res->getBody(), true);

    $redis->set(
        $episodesCacheKey,
        json_encode($data),
        CACHE_EXPIRY
    );

}

//Sort the episodes
array_multisort(array_keys($data), SORT_ASC, SORT_STRING, $data);

//Render the template
echo $twig->render('page.html', ["episodes" => $data]);

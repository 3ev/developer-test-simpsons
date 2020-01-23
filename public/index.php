<?php
require_once '../vendor/autoload.php';

//Load Twig templating environment
$loader = new Twig_Loader_Filesystem('../templates/');
$twig = new Twig_Environment($loader, ['debug' => true]);

$data = [];
$status = 'Error';

try {

    //Get the episodes from the API
    $client = new GuzzleHttp\Client();

    $res = $client->request('GET', 'http://3ev.org/dev-test-api/');
    $resBody = $res->getBody();

    $data = json_decode($resBody, true);

    //Sort the episodes
    array_multisort(array_keys($data), SORT_ASC, SORT_STRING, $data);

    $status = 'OK';

} catch (\GuzzleHttp\Exception\ServerException $exception) {
    $error = sprintf('Error: Code [%s], Message [%s]', $exception->getCode(), $exception->getMessage());
} catch (Exception $exception) {
    $error = sprintf('Error: Code [%s], Message [%s]', $exception->getCode(), $exception->getMessage());
}

//Render the template
echo $twig->render('page.html', ["episodes" => $data]);

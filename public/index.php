<?php
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';
// Creamos la aplicación.
$app = new \Slim\App();

//Ruta servicios
require '../src/rutas/servicios.php';

$app->run();

?>
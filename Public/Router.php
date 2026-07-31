<?php

require_once __DIR__ . '/../Config.php';

$routes = [
	'/' => $viewPath . '/Home.php',
	'/about' => $viewPath . '/About.php',
	'/contact' => $viewPath . '/Contact.php',
	'/posts' => $viewPath . '/Posts.php',
	'/compile' => $viewPath . '/Compile.php',
	'/show' => $viewPath . '/Show.php',
	'/example' => $viewPath . '/Example.php',
	'/404' => $viewPath . '/NotFound.php',
];

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

if (isset($routes[$path])) {
	require $routes[$path];
	exit;
}

http_response_code(404);
require $routes['/404'];

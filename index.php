<?php

use App\ApiOrderSource;
use App\Authorization;
use App\AuthorizationException;
use App\Database;
use App\MySQLOrderSource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\OrderRepository;
use App\Session;

require __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$session = new Session();
$sessionMiddleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($session) {
	$session->start();
	$response = $handler->handle($request);
	$session->save();
	
	return $response;
};

$app->add($sessionMiddleware);

$config = include_once 'config/config.php';
$database = new Database($config['mysql']['dsn'], $config['mysql']['username'], $config['mysql']['password']);
$authorization = new Authorization($database, $session);

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
//	$apiOrder = new ApiOrderSource();
	$mysqlOrder = new MySQLOrderSource();
	$orderRepository = new OrderRepository();
	$orderRepository->setSource(new ApiOrderSource());
	
	
	$body = $twig->render('index.twig', [
		'user' => $session->getData('user'),
	]);
	$response->getBody()->write($body);
	return $response;
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session){
	$body = $twig->render('login.twig', [
		'message' => $session->flush('message'),
		'form' => $session->flush('form')
	]);
	$response->getBody()->write($body);
	return $response;
});

$app->post('/login-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization, $session) {
	$params = (array) $request->getParsedBody();
	try {
		$authorization->login($params['email'], $params['password']);
	} catch (AuthorizationException $e) {
		$session->setData('message', $e->getMessage());
		$session->setData('form', $params);
		return $response->withHeader('Location', '/login')->withStatus(302);
	}
	return $response->withHeader('Location', '/')->withStatus(200);
});

$app->get('/register', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
	$body = $twig->render('register.twig', [
		'message' => $session->flush('message'),
		'form' => $session->flush('form')
	]);
	$response->getBody()->write($body);
	return $response;
});

$app->post('/register-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization, $session) {
	$params = (array) $request->getParsedBody();
	try {
		$authorization->register($params);
	} catch (AuthorizationException $e) {
		$session->setData('message', $e->getMessage());
		$session->setData('form', $params);
		return $response->withHeader('Location', '/register')->withStatus(302);
	}
	return $response->withHeader('Location', '/')->withStatus(200);
});

$app->get('/logout', function (ServerRequestInterface $request, ResponseInterface $response) use ($session) {
	$session->setData('user', null);
	return $response->withHeader('Location', '/')->withStatus(302);
});


$app->run();

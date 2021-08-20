<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

//Rota INDEX
$app->get('/', function() {
    
    /*
	echo "OK";

	$sql = new Hcode\DB\Sql();

	$resultados = $sql->select("SELECT * FROM tb_users");

	echo("<br><pre>");
	print_r(json_encode($resultados));
	echo("</pre><br>");
	*/

	$page = new Page();

	$page->setTpl("index");

});

//Rota ADMIN
$app->get('/admin', function() {

	//
	User::verificaLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

//Rota ADMIN - LOGIN
$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false, 
		"footer"=>false
	]);

	$page->setTpl("login");

});

//Rota ADMIN - LOGIN - POST
$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["senha"]);

	header("Location: ../admin");

	exit;

});

//Rota ADMIN - LOGOUT
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: ../admin/login");

	exit;

});

//Rota ADMIN - USERS
$app->get('/admin/users', function() {

	//
	User::verificaLogin();

	//
	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users" => $users
	));

});

//Rota ADMIN - USERS - CREATE
$app->get('/admin/users/create', function() {

	//
	User::verificaLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

//Rota ADMIN - USERS - DELETE - GET
$app->get('/admin/users/:iduser/delete', function($iduser) {

	//
	User::verificaLogin();

	//
	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");

	exit;

});

//Rota ADMIN - USERS - UPDATE
$app->get('/admin/users/:iduser', function($iduser) {

	//
	User::verificaLogin();

	//
	$user = new User();

	$user->get((int)$iduser);

	//
	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user" => $user->getValues()
	));

});

//Rota ADMIN - USERS - CREATE - POST
$app->post('/admin/users/create', function() {

	//
	User::verificaLogin();

	//
	//var_dump($_POST);

	$user = new User();

	//
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

	//var_dump($user);

	$user->save();

	header("Location: /admin/users");

	exit;

});

//Rota ADMIN - USERS - UPDATE - POST
$app->post('/admin/users/:iduser', function($iduser) {

	//
	User::verificaLogin();

	//
	$user = new User();

	//
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

	exit;

	//
	$page = new PageAdmin();

	$page->setTpl("users-update");
});

//
$app->run();

 ?>
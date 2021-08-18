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

//
$app->run();

 ?>
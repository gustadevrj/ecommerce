<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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

});

//Rota FORGOT PASSWORD - GET
$app->get('/admin/forgot', function() {

	//
	$page = new PageAdmin([
		"header"=>false, 
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

//Rota FORGOT PASSWORD - POST
$app->post('/admin/forgot', function() {

	//
	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");

	exit;

});

//Rota FORGOT SENT
$app->get('/admin/forgot/sent', function() {

	//
	$page = new PageAdmin([
		"header"=>false, 
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

//Rota FORGOT RESET
$app->get('/admin/forgot/reset', function() {

	//
	$user = User::validForgotDecrypt($_GET["code"]);

	//
	$page = new PageAdmin([
		"header"=>false, 
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"], 
		"code"=>$_GET["code"]
	));

});

//Rota FORGOT RESET - POST
$app->post('/admin/forgot/reset', function() {

	//
	$forgot = User::validForgotDecrypt($_POST["code"]);

	//
	User::setForgotUsed($forgot["idrecovery"]);

	//
	$user = new User();

	$user->get((int)$forgot["iduser"]);

	//
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	//
	$page = new PageAdmin([
		"header"=>false, 
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

//Rota - ADMIN - CATEGORY
$app->get("/admin/categories", function(){

	//
	User::verificaLogin();

	$categories = Category::listAll();

	//
	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$categories
	]);

});

//Rota - ADMIN - CATEGORY - CREATE
$app->get("/admin/categories/create", function(){

	//
	User::verificaLogin();

	//
	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

//Rota ADMIN - CATEGORY - CREATE - POST
$app->post('/admin/categories/create', function() {

	//
	User::verificaLogin();

	//
	//var_dump($_POST);

	$category = new Category();

	$category->setData($_POST);

	//var_dump($category);

	$category->save();

	header("Location: /admin/categories");

	exit;

});

//Rota ADMIN - CATEGORY - DELETE - GET
$app->get('/admin/categories/:idcategory/delete', function($idcategory) {

	//
	User::verificaLogin();

	//
	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");

	exit;

});

//Rota ADMIN - CATEGORY - UPDATE
$app->get('/admin/categories/:idcategory', function($idcategory) {

	//
	User::verificaLogin();

	//
	$category = new Category();

	$category->get((int)$idcategory);

	//
	$page = new PageAdmin();

	$page->setTpl("categories-update", array(
		"category" => $category->getValues()
	));

});

//Rota ADMIN - CATEGORY - UPDATE - POST
$app->post('/admin/categories/:idcategory', function($idcategory) {

	//
	User::verificaLogin();

	//
	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");

	exit;

});

//Rota CATEGORY - EXIBE CATEGORIA - GET
$app->get('/categories/:idcategory', function($idcategory) {

	//
	$category = new Category();

	$category->get((int)$idcategory);

	//
	$page = new Page();

	$page->setTpl("category/", array(
		"category" => $category->getValues(),
		"products" => []
	));

	exit;

});

//
$app->run();

 ?>
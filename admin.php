<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

?>
<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

?>
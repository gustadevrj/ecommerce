<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//Rota ADMIN - USERS - IDUSER - PASSWORD
$app->get('/admin/users/:iduser/password', function($iduser) {

	//
	User::verificaLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", array(
		"user" => $user->getValues(), 
		"msgError" => User::getError(), 
		"msgSuccess" => User::getSuccess() 
	));

	exit;

});

//Rota ADMIN - USERS - IDUSER - PASSWORD - POST
$app->post('/admin/users/:iduser/password', function($iduser) {

	//
	User::verificaLogin();

	if (!isset($_POST["despassword"]) || $_POST["despassword"] === ""){

		User::setError("Preencha a Nova Senha!");

		header("Location: /admin/users/$iduser/password");

		exit;

	}

	if (!isset($_POST["despassword-confirm"]) || $_POST["despassword-confirm"] === ""){

		User::setError("Preencha a Confirmacao da Nova Senha!");

		header("Location: /admin/users/$iduser/password");

		exit;

	}

	if ($_POST["despassword"] !== $_POST["despassword-confirm"]){

		User::setError("Os Campos Senha e Confirmacao da Senha Nao Estao Iguais!");

		header("Location: /admin/users/$iduser/password");

		exit;

	}

	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword($_POST["despassword"]);

	User::setSuccess("Senha Alterada com Sucesso!");

	header("Location: /admin/users/$iduser/password");

	exit;

});

//Rota ADMIN - USERS
$app->get('/admin/users', function() {

	//
	User::verificaLogin();

	//
	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	//
	if($search != ""){

		//
		$paginacao = User::getPageSearch($search, $page);

	}
	else{

		//
		$paginacao = User::getPage($page);

	}

	//
	$pages = array();

	for($x = 0; $x < $paginacao["pages"]; $x++){

		array_push($pages, [
			"href"=>"/admin/users?" . http_build_query([
				"page"=>$x+1, 
				"search"=>$search 
			]), 
			"text"=>$x+1 
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users" => $paginacao["dados"], 
		"search"=>$search, 
		"pages"=>$pages 
	));

	exit;

});

//Rota ADMIN - USERS - CREATE
$app->get('/admin/users/create', function() {

	//
	User::verificaLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

	exit;

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

	exit;

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
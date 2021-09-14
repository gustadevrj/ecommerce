<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

//Rota - ADMIN - CATEGORY
$app->get("/admin/categories", function(){

	//
	User::verificaLogin();

	//
	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	//
	if($search != ""){

		//
		$paginacao = Category::getPageSearch($search, $page);

	}
	else{

		//
		$paginacao = Category::getPage($page);

	}

	//
	$pages = array();

	for($x = 0; $x < $paginacao["pages"]; $x++){

		array_push($pages, [
			"href"=>"/admin/categories?" . http_build_query([
				"page"=>$x+1, 
				"search"=>$search 
			]), 
			"text"=>$x+1 
		]);

	}

	//
	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$paginacao["dados"], 
		"search"=>$search, 
		"pages"=>$pages 
	]);

	exit;

});

//Rota - ADMIN - CATEGORY - CREATE
$app->get("/admin/categories/create", function(){

	//
	User::verificaLogin();

	//
	$page = new PageAdmin();

	$page->setTpl("categories-create");

	exit;

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

	exit;

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

//Rota ADMIN - CATEGORY - PRODUCTS - EXIBE CATEGORIA - PRODUTOS - GET
$app->get('/admin/categories/:idcategory/products', function($idcategory) {

	//
	User::verificaLogin();

	//
	$category = new Category();

	$category->get((int)$idcategory);

	//
	$page = new PageAdmin();

	$page->setTpl("categories-products", array(
		"category" => $category->getValues(), 
		"productsRelated" => $category->getProducts(), 
		"productsNotRelated" => $category->getProducts(false)
	));

	exit;

});

//Rota ADMIN - CATEGORY - PRODUCTS - ADD - PRODUTOS - GET
$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct) {

	//
	User::verificaLogin();

	//
	$category = new Category();

	$category->get((int)$idcategory);

	//
	$produto = new Product();

	$produto->get((int)$idproduct);

	$category->addProduct($produto);

	header("Location: /admin/categories/" . $idcategory . "/products");

	exit;

});

//Rota ADMIN - CATEGORY - PRODUCTS - REMOVE - PRODUTOS - GET
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct) {

	//
	User::verificaLogin();

	//
	$category = new Category();

	$category->get((int)$idcategory);

	//
	$produto = new Product();

	$produto->get((int)$idproduct);

	$category->removeProduct($produto);

	header("Location: /admin/categories/" . $idcategory . "/products");

	exit;

});



?>
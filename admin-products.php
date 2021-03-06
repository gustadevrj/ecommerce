<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

//Rota - ADMIN - PRODUCT
$app->get("/admin/products", function(){

	//
	User::verificaLogin();

	//
	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	//
	if($search != ""){

		//
		$paginacao = Product::getPageSearch($search, $page);

	}
	else{

		//
		$paginacao = Product::getPage($page);

	}

	//
	$pages = array();

	for($x = 0; $x < $paginacao["pages"]; $x++){

		array_push($pages, [
			"href"=>"/admin/products?" . http_build_query([
				"page"=>$x+1, 
				"search"=>$search 
			]), 
			"text"=>$x+1 
		]);

	}

	//
	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$paginacao["dados"], 
		"search"=>$search, 
		"pages"=>$pages 
	]);

	exit;

});

//Rota - ADMIN - PRODUCT - CREATE
$app->get("/admin/products/create", function(){

	//
	User::verificaLogin();

	//
	$page = new PageAdmin();

	$page->setTpl("products-create");

	exit;

});

//Rota ADMIN - PRODUCT - CREATE - POST
$app->post('/admin/products/create', function() {

	//
	User::verificaLogin();

	//
	//var_dump($_POST);

	$product = new Product();

	$product->setData($_POST);

	//var_dump($product);

	$product->save();

	header("Location: /admin/products");

	exit;

});

//Rota ADMIN - PRODUCT - DELETE - GET
$app->get('/admin/products/:idproduct/delete', function($idproduct) {

	//
	User::verificaLogin();

	//
	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /admin/products");

	exit;

});

//Rota ADMIN - PRODUCT - UPDATE
$app->get('/admin/products/:idproduct', function($idproduct) {

	//
	User::verificaLogin();

	//
	$product = new Product();

	$product->get((int)$idproduct);

	//
	$page = new PageAdmin();

	$page->setTpl("products-update", array(
		"product" => $product->getValues()
	));

	exit;

});

//Rota ADMIN - PRODUCT - UPDATE - POST
$app->post('/admin/products/:idproduct', function($idproduct) {

	//
	User::verificaLogin();

	//
	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	//
	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");

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

?>
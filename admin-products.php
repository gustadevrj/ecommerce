<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

//Rota - ADMIN - PRODUCT
$app->get("/admin/products", function(){

	//
	User::verificaLogin();

	$products = Product::listAll();

	//
	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
	]);

});

//Rota - ADMIN - PRODUCT - CREATE
$app->get("/admin/products/create", function(){

	//
	User::verificaLogin();

	//
	$page = new PageAdmin();

	$page->setTpl("products-create");

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
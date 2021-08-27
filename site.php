<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

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

	//
	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

//Rota SITE - CATEGORY - EXIBE CATEGORIA - GET
$app->get('/categories/:idcategory', function($idcategory) {

	//PAGINACAO
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	//
	$category = new Category();

	$category->get((int)$idcategory);

	//PAGINACAO
	$paginacao = $category->getProductsPage($page);

	//PAGINACAO
	$pages = [];

	for ($i = 1; $i <= $paginacao["pages"]; $i++){
		array_push($pages, [
			"link"=>"/categories/" . $category->getidcategory() . "?page=" . $i, 
			"page"=>$i
		]);
	}

	//
	$page = new Page();

	$page->setTpl("category", array(
		"category" => $category->getValues(),
		"products" => $paginacao["dados"], 
		"pages"=>$pages
	));

	exit;

});

//Rota SITE - PRODUCT - DETALHE - GET
$app->get('/products/:desurl', function($desurl) {

	//
	$produto = new Product();

	$produto->getFromURL($desurl);

	//
	$page = new Page();

	$page->setTpl("product-detail", array(
		"product" => $produto->getValues(), 
		"categories"=> $produto->getCategories()
	));

	exit;

});

?>
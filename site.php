<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


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

//Rota SITE - CART - CARRINHO - GET
$app->get('/cart', function() {

	//
	$cart = Cart::getFromSession();

	//
	$page = new Page();

	//
	//var_dump($cart->getValues());

	$page->setTpl("cart", array(
		"cart"=>$cart->getValues(), 
		"products"=>$cart->getProducts(), 
		"error"=>Cart::getMsgErro() 
	));

	exit;

});

//Rota SITE - CART - CARRINHO - ADD PRODUCT - GET
$app->get('/cart/:idproduct/add', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	//
	$cart = Cart::getFromSession();

	//
	$qtd = (isset($_GET["qtd"])) ? (int)$_GET["qtd"] : 1;

	for($i=0; $i < $qtd; $i++){
		//
		$cart->addProduct($product);
	}

	header("Location: /cart");

	exit;

});

//Rota SITE - CART - CARRINHO - PRODUCT - REMOVE 1 POR VEZ - GET
$app->get('/cart/:idproduct/minus', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	//
	$cart = Cart::getFromSession();

	//
	$cart->removeProduct($product);

	header("Location: /cart");

	exit;

});

//Rota SITE - CART - CARRINHO - PRODUCT - REMOVE TODOS - GET
$app->get('/cart/:idproduct/remove', function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	//
	$cart = Cart::getFromSession();

	//
	$cart->removeProduct($product, true);

	header("Location: /cart");

	exit;

});

//Rota SITE - CART - CARRINHO - CALCULA FRETE - POST
$app->post('/cart/freight', function() {

	//
	$cart = Cart::getFromSession();

	//
	$cart->setFreight($_POST["zipcode"]);

	header("Location: /cart");

	exit;

});

//Rota SITE - CHECKOUT - GET
$app->get('/checkout', function() {

	//
	User::verificaLogin(false);

	//
	$cart = Cart::getFromSession();

	//
	$address = new Address();

	//
	$page = new Page();

	$page->setTpl("checkout", array(
		"cart" => $cart->getValues(), 
		"address"=> $address->getValues()
	));

	exit;

});

//Rota SITE - LOGIN - GET
$app->get('/login', function() {

	//
	$page = new Page();

	$page->setTpl("login", array(
		"error"=>User::getError()
	));

	exit;

});

//Rota SITE - LOGIN - POST
$app->post('/login', function() {

	try{

		//
		User::login($_POST["login"], $_POST["password"]);

	}
	catch(Exception $e){

		User::setError($e->getMessage());

	}

	header("Location: /checkout");

	exit;

});

//Rota SITE - LOGOUT - GET
$app->get('/logout', function() {

	//
	User::logout();

	header("Location: /login");

	exit;

});

?>
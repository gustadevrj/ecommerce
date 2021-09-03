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

	exit;

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
		"error"=>User::getError(), 
		"errorRegister"=>User::getErrorRegister(), 
		"registerValues"=>(isset($_SESSION["registerValues"])) ? $_SESSION["registerValues"] : [
			"name"=>"", "login"=>"", "email"=>"", "phone"=>""
		]
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

//Rota SITE - CADASTRO DE USUARIO - POST
$app->post('/register', function() {

	//
	$_SESSION["registerValues"] = $_POST;

	//NAME
	if(!(isset($_POST["name"])) || $_POST["name"] == ""){
		User::setErrorRegister("Preencha o seu Nome!");

		header("Location: /login");

		exit;
	}

	//LOGIN
	if(!(isset($_POST["login"])) || $_POST["login"] == ""){
		User::setErrorRegister("Preencha o seu Login!");

		header("Location: /login");

		exit;
	}
	else{

		//VERIFICA SE LOGIN JA EXISTE
		if(User::checkLoginExists($_POST["login"]) === true){
			User::setErrorRegister("Esse Login Ja Existe!");

			header("Location: /login");

			exit;
		}

	}

	//EMAIL
	if(!(isset($_POST["email"])) || $_POST["email"] == ""){
		User::setErrorRegister("Preencha o seu Email!");

		header("Location: /login");

		exit;
	}

	//PASSOWORD
	if(!(isset($_POST["password"])) || $_POST["password"] == ""){
		User::setErrorRegister("Preencha sua Senha!");

		header("Location: /login");

		exit;
	}

	$user = new User();

	$user->setData([
		"inadmin"=>0, 
		"deslogin"=>$_POST["login"],
		"desperson"=>$_POST["name"], 
		"desemail"=>$_POST["email"], 
		"despassword"=>$_POST["password"], 
		"nrphone"=>$_POST["phone"] 
	]);

	$user->save();

	//
	User::login($_POST["login"], $_POST["password"]);

	//
	$_SESSION["registerValues"] = NULL;

	header("Location: /checkout");

	exit;

});

//Rota FORGOT PASSWORD - GET
$app->get('/forgot', function() {

	//
	$page = new Page();

	$page->setTpl("forgot");

	exit;

});

//Rota FORGOT PASSWORD - POST
$app->post('/forgot', function() {

	//
	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");

	exit;

});

//Rota FORGOT SENT
$app->get('/forgot/sent', function() {

	//
	$page = new Page();

	$page->setTpl("forgot-sent");

	exit;

});

//Rota FORGOT RESET
$app->get('/forgot/reset', function() {

	//
	$user = User::validForgotDecrypt($_GET["code"]);

	//
	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"], 
		"code"=>$_GET["code"]
	));

	exit;

});

//Rota FORGOT RESET - POST
$app->post('/forgot/reset', function() {

	//
	$forgot = User::validForgotDecrypt($_POST["code"]);

	//
	User::setForgotUsed($forgot["idrecovery"]);

	//
	$user = new User();

	$user->get((int)$forgot["iduser"]);

	//
	/*
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);
	*/

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	//
	$page = new Page();

	$page->setTpl("forgot-reset-success");

	exit;

});

//Rota PROFILE - GET
$app->get('/profile', function() {

	//
	User::verificaLogin(false);

	//
	$user = User::getFromSession();

	//
	$page = new Page();

	$page->setTpl("profile", array(
		"user"=>$user->getValues(), 
		"profileMsg"=>User::getSuccess(), 
		"profileError"=>User::getError()
	));

	exit;

});

//Rota PROFILE - POST
$app->post('/profile', function() {

	//
	User::verificaLogin(false);

	//NOME
	if((!isset($_POST["desperson"])) || ( $_POST["desperson"] === "")){
		User::setError("Preencha seu Nome!");

		header("Location: /profile");

		exit;
	}

	//LOGIN
	if((!isset($_POST["deslogin"])) || ( $_POST["deslogin"] === "")){
		User::setError("Preencha seu Login!");

		header("Location: /profile");

		exit;
	}

	//EMAIL
	if((!isset($_POST["desemail"])) || ( $_POST["desemail"] === "")){
		User::setError("Preencha seu Email!");

		header("Location: /profile");

		exit;
	}

	//
	$user = User::getFromSession();

	//VERIFICA SE O LOGIN FOI TROCADO
	if($_POST["deslogin"] !== $user->getdeslogin()){

		//VERIFICA SE O LOGIN JA EXISTE
		if(User::checkLoginExists($_POST["deslogin"]) === true){

			User::setError("Esse Login Ja Existe!");

			header("Location: /profile");

			exit;

		}

	}

	//
	$_POST["inadmin"] = $user->getinadmin();

	//
	$_POST["despassword"] = $user->getdespassword();

	//
	$_POST["deslogin"] = $_POST["deslogin"];

	//
	$user->setData($_POST);

	//
	$user->update();

	//
	User::getSuccess("Dados Alterados com Sucesso!");

	//PARA ALTERAR OS DADOS NO FORMULARIO E NO HEADER
	$_SESSION[User::SESSION]["desperson"] = $_POST["desperson"];
	$_SESSION[User::SESSION]["deslogin"] = $_POST["deslogin"];
	$_SESSION[User::SESSION]["desemail"] = $_POST["desemail"];
	$_SESSION[User::SESSION]["nrphone"] = $_POST["nrphone"];

	//
	header("Location: /profile");

	exit;

});

?>
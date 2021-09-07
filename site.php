<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


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
		"error"=>Cart::getMsgError() 
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
	$address = new Address();

	//
	$cart = Cart::getFromSession();

	//
	if(isset($_GET["zipcode"])){

		$_GET["zipcode"] = $cart->getdeszipcode();

	}

	if(isset($_GET["zipcode"])){

		$address->loadFromCEP($_GET["zipcode"]);

		$cart->setdeszipcode($_GET["zipcode"]);

		$cart->save();

		$cart->getCalculateTotal();

	}

	//
	if(!$address->getdesaddress()) $address->setdesaddress("");
	if(!$address->getdescomplement()) $address->setdescomplement("");
	if(!$address->getdesdistrict()) $address->setdesdistrict("");
	if(!$address->getdescity()) $address->setdescity("");
	if(!$address->getdesstate()) $address->setdesstate("");
	if(!$address->getdescountry()) $address->setdescountry("");
	if(!$address->getdeszipcode()) $address->setdeszipcode("");

	//
	$page = new Page();

	$page->setTpl("checkout", array(
		"cart" => $cart->getValues(), 
		"address"=> $address->getValues(), 
		"products"=>$cart->getProducts(), 
		"error"=>Address::getMsgError()
	));

	exit;

});

//Rota SITE - CHECKOUT - POST
$app->post('/checkout', function() {

	//
	User::verificaLogin(false);

	//CEP
	if((!isset($_POST["zipcode"])) || ($_POST["zipcode"] === "")){

		Address::setMsgError("Informe o CEP!");

		header("Location: /checkout");

		exit;
	}

	//ENDERECO
	if((!isset($_POST["desaddress"])) || ($_POST["desaddress"] === "")){

		Address::setMsgError("Informe o Endereco!");

		header("Location: /checkout");

		exit;
	}

	//BAIRRO
	if((!isset($_POST["desdistrict"])) || ($_POST["desdistrict"] === "")){

		Address::setMsgError("Informe o Bairro!");

		header("Location: /checkout");

		exit;
	}

	//CIDADE
	if((!isset($_POST["descity"])) || ($_POST["descity"] === "")){

		Address::setMsgError("Informe a Cidade!");

		header("Location: /checkout");

		exit;
	}

	//ESTADO
	if((!isset($_POST["desstate"])) || ($_POST["desstate"] === "")){

		Address::setMsgError("Informe o Estado!");

		header("Location: /checkout");

		exit;
	}

	//PAIS
	if((!isset($_POST["descountry"])) || ($_POST["descountry"] === "")){

		Address::setMsgError("Informe o Pais!");

		header("Location: /checkout");

		exit;
	}

	//
	$user = User::getFromSession();

	//
	$address = new Address();

	$_POST["deszipcode"] = $_POST["zipcode"];

	$_POST["idperson"] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	//
	$cart = Cart::getFromSession();

	//
	$cart->getCalculateTotal();

	//
	$totals = $cart->getvltotal();

	//
	$order = new Order();

	$order->setData([
		"idcart"=>$cart->getidcart(), //
		"idaddress"=>$address->getidaddress(), 
		"iduser"=>$user->getiduser(), 
		"idstatus"=>OrderStatus::EM_ABERTO, 
		//"vltotal"=>$totals["vlprice"] + $cart->getvlfreight() 
		"vltotal"=>$totals 
	]);

	$order->save();

	header("Location: /order/" . $order->getidorder());

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
//***ERRO - ESSA ROTA NAO ESTA CARREGANDO O ENDERECO NA TELA DE CHECKOUT
	try{

		//
		User::login($_POST["login"], $_POST["password"]);

		//
		//GRAVA O idsuser NO CARRINHO (cart).INICIO
		$cart = Cart::getFromSession();

		$cart->setData(
			["iduser"=>$_SESSION["User"]["iduser"]
		]);

		$cart->save();

		$cart->setToSession();
		//GRAVA O idsuser NO CARRINHO (cart).FIM

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

//Rota ORDER - GET
$app->get('/order/:idorder', function($idorder) {

	//
	User::verificaLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	//
	$page = new Page();

	$page->setTpl("payment", array(
		"order"=>$order->getValues() 
	));

	exit;

});

//Rota ORDER - GET
$app->get('/boleto/:idorder', function($idorder) {

	//
	User::verificaLogin(false);

	//
	$order = new Order();

	$order->get((int)$idorder);

	//##############################
	//##############################
	//******************************
	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
	//$valor_boleto = formatPrice($valor_cobrado + $taxa_boleto);

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - CEP: " . $order->getdeszipcode() . " - " . $order->getdescountry();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "0000"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "00000";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "0"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "XXX XXX";
	$dadosboleto["cpf_cnpj"] = "00.000.000/0000-00";
	$dadosboleto["endereco"] = "Rua XXX XXX XXX, 000 - XXX, 00000-000";
	$dadosboleto["cidade_uf"] = "XXX - XX";
	$dadosboleto["cedente"] = "XXX XXX LTDA - ME";

	// NÃO ALTERAR!
	//include("include/funcoes_itau.php"); 
	//include("include/layout_itau.php");

	//
	$path = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	//
	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");

	//******************************
	//##############################
	//##############################

	exit;

});

?>
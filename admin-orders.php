<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

//Rota ADMIN - ORDERS - STATUS - GET
$app->get('/admin/orders/:idorder/status', function($idorder) {

	//
	User::verificaLogin();

	//
	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status", array(
		"order"=>$order->getValues(), 
		"status"=>OrderStatus::listAll(), 
		"msgSuccess"=>Order::getSuccess(), 
		"msgError"=>Order::getError() 
	));

	exit;

});

//Rota ADMIN - ORDERS - STATUS - POST
$app->post('/admin/orders/:idorder/status', function($idorder) {

	//
	User::verificaLogin();

	if(!isset($_POST["idstatus"]) || !(int)$_POST["idstatus"] > 0){

		Order::setError("Informe o Status Atual");

		header("Location: /admin/orders/" . $idorder . "/status");

		exit;

	}

	//
	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST["idstatus"]);

	$order->save();

	Order::setSuccess("Status Alterado com Sucesso!");

	//header("Location: /admin/orders");

	header("Location: /admin/orders/" . $idorder . "/status");

	exit;

});

//Rota ADMIN - ORDERS - DELETE - GET
$app->get('/admin/orders/:idorder/delete', function($idorder) {

	//
	User::verificaLogin();

	//
	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /admin/orders");

	exit;

});

//Rota ADMIN - ORDERS - IDORDER - DETAIL - GET
$app->get('/admin/orders/:idorder', function($idorder) {

	//
	User::verificaLogin();

	//
	$order = new Order();

	$order->get((int)$idorder);

	//
	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", array(
		"order"=>$order->getValues(), 
		"cart"=>$cart->getValues(), 
		"products"=>$cart->getProducts() 
	));

	exit;

});

//Rota ADMIN - ORDERS - GET
$app->get('/admin/orders', function() {

	//
	User::verificaLogin();

	$page = new PageAdmin();

	$page->setTpl("orders", array(
		"orders"=>Order::listAll() 
	));

	exit;

});


?>
<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model{

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

	public static function getFromSession(){

		$cart = new Cart();

		//*** - ??? - ESSA LINHA DENTRO DO ELSE ESTAVA DANDO PROBLEMA
		//$cart->getFromSessionID();

		//*** - ??? - ***
		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]["idcart"] > 0){
		//if ((int)$cart->getidcart() > 0){
		//if (isset($_SESSION[Cart::SESSION]["idcart"]) && (int)$_SESSION[Cart::SESSION]["idcart"] > 0){

			$cart->get((int)$_SESSION[Cart::SESSION]["idcart"]);

		}
		else{

			//*** - ??? - ESSA LINHA DENTRO DO ELSE ESTAVA DANDO PROBLEMA
			$cart->getFromSessionID();

			if(!(int)$cart->getidcart() > 0){

				$data = [
					"dessessionid"=>session_id()
				];

				if(User::checkLogin(false)){

					$user = User::getFromSession();

					$data["iduser"] = $user->getiduser();
				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

			}

		}

		return $cart;
	}

	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();

	}

	public function getFromSessionID(){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid;", array(
				":dessessionid" => session_id()
		));

		if(count($result) > 0){
			$this->setData($result[0]);
		}

	}

	public function get(int $idcart){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart;", array(
				":idcart" => $idcart
		));

		if(count($result) > 0){
			$this->setData($result[0]);
		}

	}

	public function save(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", array(
			":idcart"=> $this->getidcart(), 
			":dessessionid"=> $this->getdessessionid(), 
			":iduser"=> $this->getiduser(), 
			":deszipcode"=> $this->getdeszipcode(), 
			":vlfreight"=> $this->getvlfreight(), 
			":nrdays"=> $this->getnrdays()
		));

		//$this->setData($result[0]);

		if(count($result) > 0){
			$this->setData($result[0]);
		}

	}

	public function addProduct(Product $produto){

		$sql = new Sql();

		$sql->query("INSERT INTO 
			tb_cartsproducts 
			(idcart, idproduct) 
			VALUES 
			(:idcart, :idproduct);
			", 
			array(
				":idcart"=> $this->getidcart(), 
				":idproduct"=> $produto->getidproduct()
			)
		);

		$this->getCalculateTotal();

	}

	public function removeProduct(Product $produto, $all = false){

		$sql = new Sql();

		if($all){

			$sql->query("
				UPDATE tb_cartsproducts 
				SET 
				dtremoved = NOW() 
				WHERE 
				idcart = :idcart
				AND idproduct = :idproduct
				AND dtremoved IS NULL;
				", 
				array(
					":idcart"=> $this->getidcart(), 
					":idproduct"=> $produto->getidproduct()
				)
			);

		}
		else{

			$sql->query("
				UPDATE tb_cartsproducts 
				SET 
				dtremoved = NOW() 
				WHERE 
				idcart = :idcart
				AND idproduct = :idproduct
				AND dtremoved IS NULL
				LIMIT 1;
				", 
				array(
					":idcart"=> $this->getidcart(), 
					":idproduct"=> $produto->getidproduct()
				)
			);

		}

		$this->getCalculateTotal();

	}

	public function getProducts(){

		$sql = new Sql();

		$rows = $sql->select("
			SELECT 
			b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal  
			FROM
			tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vlweight, b.desurl
			ORDER BY b.desproduct
			",
			array(
				":idcart"=>$this->getidcart()
			)
			);

		return Product::checkList($rows);

	}

	public function getProductsTotals(){

		$sql = new Sql();

		$results = $sql->select("
				SELECT 
				SUM(vlprice) AS vlprice, 
				SUM(vlwidth) AS vlwidth, 
				SUM(vlheight) AS vlheight, 
				SUM(vllength) AS vllength, 
				SUM(vlweight) AS vlweight, 
				COUNT(*) AS nrqtd 
				FROM 
				tb_products a 
				INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct 
				WHERE 
				b.idcart = :idcart 
				AND b.dtremoved IS NULL;
			",
				array(
					":idcart"=> $this->getidcart()
			));

		if(count($results) > 0){
			return $results[0];
		}
		else{
			return [];
		}
	}

	public function setFreight($zipcode){

		$zipcode = str_replace("-", "", $zipcode);

		$totals = $this->getProductsTotals();

		if ($totals["nrqtd"] > 0){

			//CORREIOS - REGRAS DE NEGOCIO.INICIO
			if($totals["vlheight"] < 2) $totals["vlheight"] = 2;

			if($totals["vllength"] < 16) $totals["vllength"] = 16;
			//CORREIOS - REGRAS DE NEGOCIO.FIM


			$qs = http_build_query([
				"nCdEmpresa"=>"", 
				"sDsSenha"=>"", 
				"nCdServico"=>"40010", //40010: SEDEX Varejo - 40045: SEDEX a Cobrar Varejo - 40215: SEDEX 10 Varejo - 40290: SEDEX Hoje Varejo - 41106: PAC Varejo
				"sCepOrigem"=>"22640102", //CEP de Origem sem hífen
				"sCepDestino"=>$zipcode, //CEP de Destino sem hífen
				"nVlPeso"=>$totals["vlweight"], 
				"nCdFormato"=>"1", //Formato da encomenda (incluindo embalagem). Valores possíveis: 1 – Formato caixa/pacote - 2 – Formato rolo/prisma - 3 - Envelope
				"nVlComprimento"=>$totals["vllength"], 
				"nVlAltura"=>$totals["vlheight"], 
				"nVlLargura"=>$totals["vlwidth"], 
				"nVlDiametro"=>"0", 
				"sCdMaoPropria"=>"S", //Indica se a encomenda será entregue com o serviço adicional mão própria.
				"nVlValorDeclarado"=>$totals["vlprice"], 
				"sCdAvisoRecebimento"=>"S" //Indica se a encomenda será entregue com o serviço adicional aviso de recebimento
			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs);

			/*
			//
			//$xml = (array)$xml;

			//
			var_dump($xml);

			//
			echo("<br><pre>");
			print_r($xml);
			echo("</pre><br>");

			//
			echo(json_encode($xml));
			*/

			//
			$result = $xml->Servicos->cServico;

			//RETORNOU ERRO???
			if($result->MsgErro != ""){

				Cart::setMsgError($result->MsgErro);

			}
			else{

				Cart::clearMsgError();

			}

			//
			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($zipcode);

			//
			$this->save();

			return $result;

		}
		else{

			//??????????

		}

	}

	//
	public static function formatValueToDecimal($value):float{

		$value = str_replace(".", "", $value);
		$value = str_replace(",", ".", $value);

		return $value;
	}

	//
	public static function setMsgError($msg){

		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}

	//
	public static function getMsgError(){

		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;

	}

	//
	public static function clearMsgError(){

		$_SESSION[Cart::SESSION_ERROR] = NULL;

	}

	//
	public function updateFreight(){

		if ($this->getdeszipcode() != ""){

			$this->setFreight($this->getdeszipcode());

		}

	}

	//
	public function getValues(){

		$this->getCalculateTotal();

		return parent::getValues();

	}

	//
	public function getCalculateTotal(){

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals["vlprice"]);
		$this->setvltotal($totals["vlprice"] + $this->getvlfreight());

	}

}

?>
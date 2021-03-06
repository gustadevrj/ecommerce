<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model{

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct;");

	}

	public static function checkList($list){

		foreach ($list as &$row) {
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}

		return $list;

	}

	public function save(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=> $this->getidproduct(), 
			":desproduct"=>$this->getdesproduct(), 
			":vlprice"=> $this->getvlprice(), 
			":vlwidth"=> $this->getvlwidth(), 
			":vlheight"=> $this->getvlheight(), 
			":vllength"=> $this->getvllength(), 
			":vlweight"=> $this->getvlweight(), 
			":desurl"=> $this->getdesurl()
		));

		$this->setData($result[0]);

	}

	public function get($idproduct){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct;", array(
				":idproduct" => $idproduct
		));

		$data = $result[0];

		$this->setData($data);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct;", array(
			":idproduct" => $this->getidproduct()
		));

		//##############################
		//CRIAR METODO!
		//APAGA ARQUIVO.INICIO
		if(file_exists(
				$_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
				"res" . DIRECTORY_SEPARATOR . 
				"site" . DIRECTORY_SEPARATOR . 
				"img" . DIRECTORY_SEPARATOR . 
				"produtos" . DIRECTORY_SEPARATOR . 
				$this->getidproduct() . ".jpg"
			)){
				//echo("<br>ARQUIVO EXISTE!!!<br>");

				$arquivo = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
				"res" . DIRECTORY_SEPARATOR . 
				"site" . DIRECTORY_SEPARATOR . 
				"img" . DIRECTORY_SEPARATOR . 
				"produtos" . DIRECTORY_SEPARATOR . 
				$this->getidproduct() . ".jpg";

				//echo("<br>" . $arquivo . "<br>");

				//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
				unlink($arquivo);
				//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
		}
		//APAGA ARQUIVO.FIM
		//##############################

	}

	public function checkPhoto(){

		if(file_exists(
				$_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
				"res" . DIRECTORY_SEPARATOR . 
				"site" . DIRECTORY_SEPARATOR . 
				"img" . DIRECTORY_SEPARATOR . 
				"produtos" . DIRECTORY_SEPARATOR . 
				$this->getidproduct() . ".jpg"
			)){
				$url = "/res/site/img/produtos/" . $this->getidproduct() . ".jpg";
		}
		else{
			$url = "/res/site/img/product.jpg";
		}

		return $this->setdesphoto($url);

	}

	public function getValues(){

		//
		$this->checkPhoto();

		//
		$values = parent::getValues();

		return $values;

	}

	public function setPhoto(){

		//##############################
		//xxx.INICIO
		if ((string)$_FILES["file"]["name"] === ""){
			return;
		}
		//xxx.FIM
		//##############################

		//
		$extension = explode(".", (string)$_FILES["file"]["name"]);
		$extension = end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':

				$image = imagecreatefromjpeg($_FILES["file"]["tmp_name"]);
				break;

			case 'gif':

				$image = imagecreatefromgif($_FILES["file"]["tmp_name"]);
				break;

			case 'png':

				$image = imagecreatefrompng($_FILES["file"]["tmp_name"]);
				break;
		}

		$destino = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
				"res" . DIRECTORY_SEPARATOR . 
				"site" . DIRECTORY_SEPARATOR . 
				"img" . DIRECTORY_SEPARATOR . 
				"produtos" . DIRECTORY_SEPARATOR . 
				$this->getidproduct() . ".jpg";

		//
		//$image = (!isset($image)) ? $destino : $image;

		imagejpeg($image, $destino);

		imagedestroy($image);

		$this->checkPhoto();

	}

	//
	public function getFromURL($desurl){

		$sql = new Sql();

		$rows = $sql->select("
			SELECT * FROM 
			tb_products 
			WHERE 
			desurl = :desurl
			LIMIT 1;", 
			array(
				":desurl"=>$desurl
			)
		);

		$this->setData($rows[0]);

	}

	//
	public function getCategories(){

		$sql = new Sql();

		return $sql->select("
				SELECT * FROM 
				tb_categories a INNER JOIN tb_productscategories b 
				ON a.idcategory = b.idcategory 
				WHERE
				b.idproduct = :idproduct;", 
			array(
				":idproduct" => $this->getidproduct()
			)
		);

	}

	//PAGINACAO
	public static function getPage($page = 1, $itemsPerPage = 5){

		//
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * FROM 
			tb_products 
			ORDER BY desproduct 
			LIMIT $start, $itemsPerPage;
			");

		//QUANTOS ITENS TEM?
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS total;");

		return array(
			"dados"=>$results, 
			"total"=>(int)$resultTotal[0]["total"], 
			"pages"=>ceil($resultTotal[0]["total"] / $itemsPerPage)
		);
	}

	//
	public static function getPageSearch($search, $page = 1, $itemsPerPage = 5){

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			WHERE 
			desproduct LIKE :search 
			ORDER BY desproduct 
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'dados'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

}

?>
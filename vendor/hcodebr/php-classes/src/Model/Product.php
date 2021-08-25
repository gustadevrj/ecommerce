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

		//
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

		//
		if ((string)$_FILES["file"]["name"] === ""){
			return;
		}

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

}

?>
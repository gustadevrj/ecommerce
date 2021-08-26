<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory;");

	}

	public function save(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=> $this->getidcategory(), 
			":descategory"=>$this->getdescategory()
		));

		$this->setData($result[0]);

		//
		Category::updateFile();

	}

	public function get($idcategory){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory;", array(
				":idcategory" => $idcategory
		));

		$data = $result[0];

		$this->setData($data);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory;", array(
			":idcategory" => $this->getidcategory()
		));

		//
		Category::updateFile();
	}

	//ATUALIZA ARQUIVO MENU DE CATEGORIAS - categories-menu.html
	public static function updateFile(){

		$categories = Category::listAll();

		$html = array();

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/' . $row["idcategory"] . '">' . $row["descategory"] . '</a></li>');
		}

		//
		file_put_contents($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode("", $html));
	}

	//
	public function getProducts($produto_relacionado_categoria = true){

		$sql = new Sql();

		if ($produto_relacionado_categoria === true){

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct 
					FROM tb_products a
					INNER JOIN tb_productscategories b
					ON 
					a.idproduct = b.idproduct
					WHERE
					b.idcategory = :idcategory);", 
					[
						":idcategory"=>$this->getidcategory()
					]);

		}
		else{

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct 
					FROM tb_products a
					INNER JOIN tb_productscategories b
					ON 
					a.idproduct = b.idproduct
					WHERE
					b.idcategory = :idcategory);", 
					[
						":idcategory"=>$this->getidcategory()
					]);

		}

	}

	//ADICIONA PRODUTO A CATEGORIA
	public function addProduct(Product $produto){

		$sql = new Sql();

		$sql->query("INSERT INTO 
				tb_productscategories 
				(idcategory, idproduct) VALUES  
				(:idcategory, :idproduct);", 
				array(
					":idcategory"=>$this->getidcategory(), 
					":idproduct"=>$produto->getidproduct()
		));

	}

	//REMOVE PRODUTO DE CATEGORIA
	public function removeProduct(Product $produto){

		$sql = new Sql();

		$sql->query("DELETE FROM 
			tb_productscategories 
			WHERE 
			idcategory = :idcategory 
			AND idproduct = :idproduct;", 
			array(
				":idcategory"=>$this->getidcategory(), 
				":idproduct"=>$produto->getidproduct()
		));

	}


}

?>
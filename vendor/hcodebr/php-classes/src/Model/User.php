<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

	const SESSION = "User";

	public static function login($login, $senha){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :login;", array(':login' => $login));

		//$results->bindValues(":login", $login);

		if(count($results) === 0){
			throw new \Exception("Usuario Inexistente ou Senha Invalida!");
		}

		$dados = $results[0];

		if (password_verify($senha, $dados["despassword"]) === true){
			$user = new User();

			//
			$user->setData($dados);

			/*
			var_dump($dados);
			exit;
			*/

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		}
		else{
			throw new \Exception("Usuario Inexistente ou Senha Invalida!");
		}

	}

	public static function verificaLogin($inadmin = true){

		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		){

			header("Location: admin/login");
			exit;

		}
	}

	public static function logout(){

		$_SESSION[User::SESSION] = NULL;

	}

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson;");

	}

	public function save(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson" => $this->getdesperson(), 
			":deslogin" => $this->getdeslogin(), 
			":despassword" => $this->getdespassword(), 
			":desemail" => $this->getdesemail(), 
			":nrphone" => $this->getnrphone(), 
			":inadmin" => $this->getinadmin()
		));

		$this->setData($result[0]);

	}

	public function get($iduser){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
				":iduser" => $iduser
		));

		$data = $result[0];

		$this->setData($data);

	}

	public function update(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser" => $this->getiduser(), 
			":desperson" => $this->getdesperson(), 
			":deslogin" => $this->getdeslogin(), 
			":despassword" => $this->getdespassword(), 
			":desemail" => $this->getdesemail(), 
			":nrphone" => $this->getnrphone(), 
			":inadmin" => $this->getinadmin()
		));

		$this->setData($result[0]);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser" => $this->getiduser()
		));
	}

}

?>
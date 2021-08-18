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

}

?>
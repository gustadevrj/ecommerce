<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";

	const SECRET = "0123456789ABCDEF";

	const SECRET_IV = "FEDCBA9876543210";

	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

	public static function getFromSession(){

		$user = new User();

		if(isset($_SESSION[User::SESSION]) && ((int)$_SESSION[User::SESSION]["iduser"]) > 0){

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true){

		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		){
			//NAO ESTA LOGADO!
			return false;
		}
		else{

			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){
				return true;
			}
			else if ($inadmin === false){
				return true;
			}
			else{
				return false;
			}

		}

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results) > 0);

	}

	public static function login($login, $senha){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :login;", array(':login' => $login));

		//$results->bindValues(":login", $login);

		if(count($results) === 0){
			throw new \Exception("Usuario Inexistente ou Senha Invalida!");
		}

		$dados = $results[0];

		/*
		echo("<br>Dados: <pre>");
		print_r($dados);
		echo("</pre><br>");
		*/

		/*
		$senha1 = '$2y$12$zyppPTF9RKdVLh9AnRmf8ez0Nqj9EBP/OlQpSOoGPx1cENnRlGXpO';//HASH Senha: admin
		$senha2 = '$2y$12$jvSVBPK/HwzN4b6FXphPvOsNsw8CLXLWEznbmnxT4GrttUE/SgI3G';//HASH Senha: suporte
		$senha3 = '$2y$12$0o/n/W6g0ZWwOPDSBoUmNOQd2L2Gh2RKVZ6FD03dBjeld1GhAkBAe';//HASH Senha: teste
		*/

		if (password_verify($senha, $dados["despassword"]) === true){

			$user = new User();

			//
			$dados["desperson"] = utf8_encode($dados["desperson"]);

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

		/*
		!isset($_SESSION[User::SESSION])
		||
		!$_SESSION[User::SESSION]
		||
		!(int)$_SESSION[User::SESSION]["iduser"] > 0
		||
		(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		*/
		if(!User::checkLogin($inadmin)){

			if($inadmin){
				header("Location: /admin/login");
			}
			else{
				header("Location: /login");
			}

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
			":desperson" => utf8_decode($this->getdesperson()), 
			":deslogin" => $this->getdeslogin(), 
			":despassword" => User::getPasswordHash($this->getdespassword()), 
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

		//
		$data["desperson"] = utf8_encode($data["desperson"]);

		$this->setData($data);

	}

	public function update(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser" => $this->getiduser(), 
			":desperson" => utf8_decode($this->getdesperson()), 
			":deslogin" => $this->getdeslogin(), 
			//":despassword" => User::getPasswordHash($this->getdespassword()), //ESSA LINHA ESTAVA DANDO ERRO PQ GERAVA UM HASH DE OUTRO HASH TODA VEZ QUE O USUARIO ERA EDITADO
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

	public static function getForgot($email, $inadmin = true){

		$sql = new Sql();

		$result = $sql->select("
				SELECT * FROM tb_persons a 
				INNER JOIN tb_users b 
				USING (idperson) 
				WHERE a.desemail = :email;
			", array(
					":email"=>$email
				));

		if (count($result) === 0){
			throw new \Exception("Não Foi Possivel Recuperar a Senha!");
		}
		else{

			$dado = $result[0];

			$result2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
					":iduser"=>$dado["iduser"], 
					":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if(count($result2) === 0){
				throw new \Exception("Não Foi Possivel Recuperar a Senha!");
			}
			else{
				$dadoRecovery = $result2[0];

				//
				//Funcao mcrypt_encrypt() Foi Depreciada no PHP 7.1
				//Se Ocorrer Algum Erro, Usar a Função openssl_encrypt()
				//mcrypt_encrypt(cipher, key, data, mode)
				//openssl_encrypt(data, method, password)
				//openssl_encrypt(data, method, password, 0, ???)

				//NAO FUNCIONA
				//$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dadoRecovery["idrecovery"], MCRYPT_MODE_ECB));

				//FUNCIONA!
				//$code = base64_encode(openssl_encrypt($dadoRecovery["idrecovery"], "AES-128-CBC", User::SECRET, 0, User::SECRET_IV));

				//FUNCIONA!
				$code = openssl_encrypt($dadoRecovery["idrecovery"], "AES-128-CBC", pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				if ($inadmin === true){
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=" . $code;
				}
				else{
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=" . $code;
				}

				/*
				echo("<br>***<pre>");
				print_r($link);
				echo("</pre>***<br>");
				//exit;
				*/

				//
				$mailer = new Mailer($dado["desemail"], $dado["desperson"], "Redefinir Senha!", "forgot", array(
						"name"=>$dado["desperson"], 
						"link"=>$link
				));

				/*
				echo("<br>***<pre>");
				print_r($mailer);
				echo("</pre>***<br>");
				*/

				/*
				echo("<br>***<pre>");
				print_r($dado);
				echo("</pre>***<br>");
				exit;
				*/

				//
				$mailer->send();

				return $dado;
				//return $link;

			}
		}

	}

	public static function validForgotDecrypt($code){

		$code = base64_decode($code);

		//
		//Funcao mcrypt_encrypt() Foi Depreciada no PHP 7.1
		//Se Ocorrer Algum Erro, Usar a Função openssl_decrypt()
		//mcrypt_decrypt(cipher, key, data, mode)
		//openssl_decrypt(data, method, password)
		//openssl_decrypt(data, method, password, 0, ???)

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", 
			array(
				":idrecovery"=>$idrecovery
			));

		if (count($results) === 0){
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else{

			return $results[0];

		}

	}

	public static function setForgotUsed($idrecovery){

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}

	public function setPassword($password){

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>User::getPasswordHash($password),
			":iduser"=>$this->getiduser()
		));

	}

	public static function setError($msg){

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError(){

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError(){

		$_SESSION[User::ERROR] = NULL;

	}

	public static function setSuccess($msg){

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess(){

		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess(){

		$_SESSION[User::SUCCESS] = NULL;

	}

	//
	public static function getPasswordHash($password){

		return password_hash($password, PASSWORD_DEFAULT, [
			"cost"=>12
		]);

	}

	public function getOrders(){

		$sql = new Sql();

		//A LINHA INNER JOIN tb_addresses e USING (idaddress) ESTA DANDO ERRO
		//O MESMO PROBLEMA OCORRE NA PROC sp_orders_save
		/*
		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			':iduser'=>$this->getiduser()
		]);
		*/

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e ON e.idaddress = c.idaddress 
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			':iduser'=>$this->getiduser()
		]);

		return $results;

	}

	//PAGINACAO
	public static function getPage($page = 1, $itemsPerPage = 5){

		//
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * FROM 
			tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson 
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
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson)
			WHERE 
			b.desperson LIKE :search 
			OR b.desemail = :search_exato  
			OR a.deslogin LIKE :search
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%', 
			":search_exato" => $search 
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'dados'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function setErrorRegister($msg){

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister(){

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister(){

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}

	public static function checkLoginExists($login){

		$sql = new Sql();

		$results = $sql->select("
				SELECT * FROM tb_users WHERE deslogin = :deslogin
			", array(
				":deslogin"=>$login
		));

		return (count($results) > 0);

	}

	//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

	

	//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

}

?>
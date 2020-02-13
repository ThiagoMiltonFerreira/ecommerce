<?php


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;






class User extends Model {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucess";


	public static function setError($msg)
	{
		
		$_SESSION[USER::ERROR] = $msg;

	}
	public static function getError()
	{
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();
	
		return $msg;
	}

	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
	}

	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
	}

	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
		User::clearErrorRegister();
		return $msg;
	}

	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}

	
	public static function setSuccess($msg)
	{
		
		$_SESSION[USER::SUCCESS] = $msg;

	}
	public static function getSuccess()
	{
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();
	
		return $msg;
	}

	public static function clearSuccess()
	{
		$_SESSION[User::SUCCESS] = NULL;
	}



	public static function getFromSession()
	{

		$user = new User();

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION] ['iduser'] > 0)
		{
	

			$user->setData($_SESSION[User::SESSION]);
			

		}


		return $user;

	}



	public static function checkLogin($inadmin = true)
	{
		if(!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		
		){
			// Nao Esta logado
			return false;

		}
		else
		{
			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true)
			{

				return true;
			}
			else if($inadmin === false)
			{

				//return true;
				return false;

			}else{

				//return false;
				return true;
			}

		}

	}
	public static function checkLoginExist($login)
	{


		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results)>0); // if o resultado da busca for maior que 0
	}

	public static function login($login, $password)
	{
       //Verifica se existe usuario
		$sql = new Sql();
		$results = $sql->select("SELECT*FROM tb_users WHERE deslogin = :LOGIN",array( //bind param do sql que vem do formulario EVITA INJECTION
			":LOGIN"=>$login // passa minha variavel login para o login do SQL

		));

		if(count($results)===0)
		{
			throw new \Exception("Usuario inexitente ou senha invalida"); // Essa \ antes do exeception e para pegar a execessao principal do PHP nao foi criada a execssao para este erro ainda.
			
		}
		$data = $results[0];

		if(password_verify($password,$data["despassword"] ) ===true)
		{
			$user = new User();
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();
			return $user;
			//$user->setData($data);
			//var_dump($user);
			


			//exit;

		}else{
			throw new \Exception("Usuario inexitente ou senha invalida."); // Essa \ antes do exeception e para pegar a execessao principal do PHP nao foi criada a execssao para este erro ainda.
		}


	}
	public static function verifyLogin($inadmin = true)
	{
		//Verifica a existencia da sessao

		if(User::checkLogin($inadmin)===true){


			header("Location: /admin/login");	
		

		}
		else
		{
			header("Location: /login");

		}
		exit;

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;


	}

	public static function listAll()
	{

		$sql = new Sql();
     return $sql->select("SELECT *FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY 	b.desperson");


	}
	public static function listUser($idUser)
	{

		$sql = new Sql();
     	$return = $sql->select("SELECT *FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE iduser = :IDUSER ORDER BY b.desperson",array( //bind param do sql que vem do formulario EVITA INJECTION
			":IDUSER"=>$idUser// passa minha variavel login para o login do SQL

		));

     	return $return;
     	
	}

	public function save()
	{

		$sql = new Sql();
		/*
		pdesperson VARCHAR(64), 
		pdeslogin VARCHAR(64), 
		pdespassword VARCHAR(256), 
		pdesemail VARCHAR(128),
		pnrphone BIGINT, 
		pinadmin TINYINT
		*/
		// Procedure do banco faz todo o processo de inserir op usuario pois o usuario nao insere so na tabela usuario insere tambem no enredereço entao tenho uma procedure que faz isso so passar os dados... a procedure e criada direto no banco.
		$results = $sql->select("CALL sp_users_save(:pdesperson, :pdeslogin, :pdespassword, :pdesemail, :pnrphone, :pinadmin)", array(
			":pdesperson"=>$this->getdesperson(),
			":pdeslogin"=>$this->getdeslogin(),
			//":pdespassword"=>$this->getdespassword(),
			":pdespassword"=>password_hash($this->getdespassword(), PASSWORD_DEFAULT),
			":pdesemail"=>$this->getdesemail(),
			":pnrphone"=>$this->getnrphone(),
			":pinadmin"=>$this->getinadmin()

		));
		//var_dump($results);
		$this->setData($results[0]);

	}
	public function update()
	{

		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :pdesperson, :pdeslogin, :pdespassword, :pdesemail, :pnrphone, :pinadmin)", array(
			":iduser"=>$this->getiduser(),
			":pdesperson"=>$this->getdesperson(),
			":pdeslogin"=>$this->getdeslogin(),
			":pdespassword"=>User::getdespasswordHash($this->getdespassword()),
			":pdesemail"=>$this->getdesemail(),
			":pnrphone"=>$this->getnrphone(),
			":pinadmin"=>$this->getinadmin()

		));
		//var_dump($results);
		$this->setData($results[0]);

	}

	public function updateAccountUser()
	{

		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_account(:iduser, :pdesperson, :pdesemail, :pnrphone)", array(
			":iduser"=>$this->getiduser(),
			":pdesperson"=>$this->getdesperson(),
			":pdesemail"=>$this->getdesemail(),
			":pnrphone"=>$this->getnrphone()
			

		));
	
		$this->setData($results);

	}


	public function delete()
	{
		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:iduser)",array(
			":iduser"=>$this->getiduser()
		));


	}

	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();
		$results = $sql->select("
			SELECT * 
			FROM tb_persons as a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail =:email",array(
				":email"=>$email
			)); //Passa o valor por array para evitar o injection

		if(count($results) === 0)
		{
			throw new \Exception("Nao foi possivel recuperar a senha", 1);
			
		}
		else
		{
			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]

			));

			if(count($results2) === 0 )
			{
				throw new \Exception("Não Foi possivel recuperar a senha", 1);
				
			}
			else
			{
				$dataRecovery = $results2[0];
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET , $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				

				if($inadmin === true)
				{
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				}
				else
				{
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}

				
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store.","forgot",
					array(
						"name"=>$data["desperson"],
						"link"=>$link
				));
			
				$mailer->send();
		
				return $data;
			}


		}

	}

	public static function validForgotDecrypt($code)
	{
			
			$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,User::SECRET,base64_decode($code),MCRYPT_MODE_ECB);
			//  **********teste aqui ************
			//var_dump($idrecovery);
			//exit;
			// VOLTA O CODIGO

			$sql = new Sql();
			$results = $sql->select("

					SELECT * FROM db_ecommerce.tb_userspasswordsrecoveries a
					INNER JOIN tb_users b USING(iduser)
					INNER JOIN  tb_persons c USING(idperson)
					WHERE 
					a.idrecovery = :idrecovery
    				AND
    				a.dtrecovery IS NULL
    				AND
    				DATE_ADD(a.dtregister,INTERVAL 2 HOUR) >= now();

				",array(
					":idrecovery" => $idrecovery

				));

				if(count($results) ===0)
				{

					throw new \Exception("Não foi possivel recuperar a senha.");
					
				}
				else
				{
					return $results[0];
				}

	}

	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();
		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(":idrecovery"=>$idrecovery
		));


	}

	public function setPassword($password,$idUser)
	{

		$sql = new Sql();
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$idUser
		));


	}
	public static function getdespasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);


	}

}



?>
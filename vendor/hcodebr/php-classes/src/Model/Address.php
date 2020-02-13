<?php


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;



class Address extends Model {

	const SESSION_ERROR =  "AddressError";

	public static function getCEP($nrcep)
	{
		$nrcep = str_replace("-", "", $nrcep);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/"); // vai passar o cep para a url do via cep
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // estou esperando a resposta entao seta true
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // exige altenticacao SSL? false o via cep nao exige

		$data = json_decode(curl_exec($ch), true); // executa tudo ,  ja da um decode para transformar de jason para array passando parameeetro  true

		curl_close($ch);

		return $data;


	}

	public function loadFromCEP($nrcep)
	{

		$data = Address::getCEP($nrcep);


		if(isset($data['logradouro']) && $data['logradouro'])
		{
				
			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($nrcep);




		}


	}

	public function save()
	{

		$sql = new sql();

		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry
			, :deszipcode, :desdistrict)",[

				'idaddress'=>$this->getidadress(),
				'idperson'=>$this->getidperson(),
				'desaddress'=>utf8_decode($this->getdesaddress()),
				'descomplement'=>utf8_decode($this->getdescomplement()),
				'descity'=>utf8_decode($this->getdescity()),
				'desstate'=>utf8_decode($this->getdesstate()),
				'descountry'=>utf8_decode($this->getdescountry()),
				'deszipcode'=>utf8_decode($this->getdeszipcode()),
				'desdistrict'=>utf8_decode($this->getdesdistrict())
				

			]);



		if(count($results) > 0) {

			$this->setData($results[0]);
		}

	}

		public static function setMsgError($msg)
		{
			//cria uma sessao com nome CartError contem a mensagem de erro retornada do webservice correio
			$_SESSION[Address::SESSION_ERROR] = $msg;


		}

		public static function getMsgError()
		{

			// se estiver definido a sessacao retorna o valor dela se nao retorna vazio	
			$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";
			Address::clearMsgError();
			return $msg;

		}	

		public static function clearMsgError()
		{

			$_SESSION[Address::SESSION_ERROR] = NULL;

		}




}







?>
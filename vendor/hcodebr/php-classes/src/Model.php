<?php

namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args) // preciso saber qual metodo foi chamado por isso uso o function__call
	{

		$method = substr($name, 0, 3); // pega os 3 primeiros digitos 0,1,2
		$fieldName = substr($name, 3, strlen($name));
		//var_dump($method, $fieldName);
		//exit;
		
		//var_dump($method, $fie ldName);
		
		
		switch ($method) {
			case 'get':
				return  (isset($this->values[$fieldName])) ?  $this->values[$fieldName] : NULL ;
				break;

			case 'set':
				$this->values[$fieldName] = $args[0];
				
			break;
			
			
		}		

	}
	public function setData($data = array())
	{

		foreach ($data as $key => $value) {
			$this->{"set".$key}($value); // nome da variavel dinamico vai gerar set+nome que veio da variavel tem que esta entre chaves e o mesmo que set$key daria ERRO exemplo vai ficar setThiago se o valor de key for thiago

		}
		//var_dump($this->values);
		
	}
	public function getValues()
	{

		return $this->values;
	}
	
	


}




?>
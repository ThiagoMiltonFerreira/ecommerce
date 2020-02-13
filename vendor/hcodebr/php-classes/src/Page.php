<?php

namespace Hcode;
use Rain\Tpl;

class Page 
{
	private $tpl;
	private $options = [];
	private $defaults = [
		"header" =>true,
		"footer" =>true,
		"data"=>[]
	];
	public function __construct($opts = array(), $tpl_dir = "/views/")
	{ //function __construct()  Primeiro metodo a ser executado

		$this->options = array_merge($this->defaults, $opts); // junta os arrays

		$config = array(			// $_SERVER["DOCUMENT_ROOT"] Pega o caminho atual do meu arquivo
					"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir, // Pasta dos Html
					"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", // Pasta de cache do html
					"debug"         => false // set to false to improve the speed
				   );

		Tpl::configure( $config );

		$this->tpl = new Tpl;
		$this->setData($this->options["data"]);
		if($this->options["header"] === true) $this->tpl->draw("header"); // Desenhar o template na tela, se options for true desenha na tela  o header



		
	}
	private function setData($data = array())
	{

		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}


	}
	public function setTpl($name,$data = array(), $returnHTML = false)
	{
		$this->setData($data);
		return $this->tpl->draw($name, $returnHTML); // Desenhar o template na tela
	}


	public function __destruct()
	{ //function __destruct() ultimo metodo a ser executado

		if($this->options["footer"] === true) $this->tpl->draw("footer"); // se opt for igual a true na desenha na tela o rodape

	}

}


?>
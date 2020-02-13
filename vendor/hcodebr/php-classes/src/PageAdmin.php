<?php

namespace Hcode;

class PageAdmin extends Page{


	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{ //function __construct()  Primeiro metodo a ser executado

		parent::__construct($opts, $tpl_dir); //CHAMA o construtor da classe pai, no caso esta aqui extende da pageAdmin.. NESTE CASO CHAMO A CLASSE PAI(PAGADMIN) CHAMA A CONSTRUCT

	}


}




?>
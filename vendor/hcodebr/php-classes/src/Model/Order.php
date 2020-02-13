<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model
{
	/*	
	pidorder INT,
	pidcart int(11),
	piduser int(11),
	pidstatus int(11),
	pidaddress int(11),
	pvltotal decimal(10,2)
	*/

	public function save()
	{
		$sql = new sql();


			/*
			echo "ID_ORDER - " . $this->getidorder()." - <br>";
			echo "idcart - " .$this->getidcart()." - <br>";
			echo "iduser - " .$this->getiduser()." - <br>";
			echo "idstatus - " .$this->getidstatus()." - <br>";
			echo "idaddress - " .$this->getidaddress()." - <br>";
			echo "vltotal - " .$this->getvltotal()." - <br>";
			*/


		$results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [

			':idorder'=>$this->getidorder(),
			':idcart'=>$this->getidcart(),
			':iduser'=>$this->getiduser(),
			':idstatus'=>$this->getidstatus(),
			':idaddress'=>$this->getidaddress(),
			':vltotal'=>$this->getvltotal()
		]);

		//var_dump($results[0]);
		//exit;


		if(count($results) > 0)
		{

			$this->setData($results[0]);
		}

	}

	public function get($idorder)
	{
		$sql = new sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :idorder
			", [
				':idorder'=>$idorder
			]);

			if(count($results) > 0)
			{
				

				$this->setData($results[0]);


			}

	}
}





?>
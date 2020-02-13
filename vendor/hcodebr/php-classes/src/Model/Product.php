<?php


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;






class Product extends Model {

	
	public static function listAll()
	{
		$cont=0;
		$sql = new Sql();
     	return $sql->select("SELECT *FROM tb_products ORDER BY desproduct");

	}
	
	public static function checkList($list)
	{
		$cont=0;

		foreach ($list as $key => $value) 
     	{
     		foreach ($value as $linha => $coluna) 
     		{     			
     	
     			$list[$cont]["desphoto"] = !file_exists("res/site/img/products/".$value['idproduct'].".jpg") ? 
     										"res/site/img/products/product.jpg" : 
     										"res/site/img/products/".$value['idproduct'].".jpg";			
     			$cont++;
     			break;
     		}
     		
     	}

		return $list;

	}
	
	
	public function save()
	{

		$sql = new Sql();
		$results = $sql->select("CALL sp_products_save(:pidproduct, :pdesproduct, :pvlprice, :pvlwidth, :pvlheight, :pvllength, :pvlheight, :pdesurl)", array(
			":pidproduct"=>$this->getidproduct(),
			":pdesproduct"=>$this->getdesproduct(),
			":pvlprice"=>$this->getvlprice(),
			":pvlwidth"=>$this->getvlwidth(),
			":pvlheight"=>$this->getvlheigth(),
			":pvllength"=>$this->getvllength(),
			":pvlheight"=>$this->getvlweight(),
			":pdesurl"=>$this->getdesurl()

			
		));
		//var_dump($results);
		$this->setData($results[0]);
		

	}
	public function get($idproduct)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT*FROM tb_products WHERE idproduct = :idproduct",[
			':idproduct'=>$idproduct]
		);
		$this->setData($results[0]);
	} 

	public function delete ()
	{

		$sql = new Sql();
		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			':idproduct'=>$this->getidproduct()
		]);



	}

	public function checkPhoto()
	{
		if(file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
		)){

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
		} 
		else
		{
			$url = "/res/site/img/products/product.jpg";       

		}
		return $this->setdesphoto($url);

	}

	public function getValues()
	{
		$this->checkPhoto();
		$values = parent::getValues(); // chama a funcao pai -  esta classe extende do model no model tem o getvalue entao chamo a classe do objeto pai

		return $values;

	}
	public function setPhoto($file)
	{
		$extension = explode('.',$file['name']); // gerou um array com nome do arquivo separado pelo . [0] thiago [1] Jpg
		$extension = end($extension); // pega so a ultima posição do array

		switch ($extension) {
			case "jpg":
			case "jpeg":	
			$image = imagecreatefromjpeg($file["tmp_name"]); //Criou a imagem JPG
			break;

			case "gif":	
			$image = imagecreatefromgif($file["tmp_name"]);  //Criou a imagem GIF
 			break;

			case "png":	
			$image = imagecreatefrompng($file["tmp_name"]);   //Criou a imagem PNG
			break;
		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";
		
		//Salvar a imagem no caminho + nome do arquivo
		imagejpeg($image, $dist);

		imagedestroy($image);
		
		$this->checkPhoto();

	}


	public function getFromURL($desurl)
	{


		$sql = new Sql();
		$rows = $sql->select("SELECT * FROM db_ecommerce.tb_products WHERE desurl = :desurl LIMIT 1",[
			':desurl'=>$desurl
		]);	

		$this->setData($rows[0]);
	}

	public function getCategories()
	{
		$sql = new sql();
		
		return $sql->select("


			SELECT*FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct			
			", [
				':idproduct'=>(int)$this->getidproduct()
			]);
		
	}
	
}



?>
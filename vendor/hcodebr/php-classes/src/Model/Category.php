<?php


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;






class Category extends Model {

	
	public static function listAll()
	{

		$sql = new Sql();
     	return $sql->select("SELECT *FROM tb_categories ORDER BY descategory");


	}
	
	public function save()
	{

		$sql = new Sql();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()	
		));
		//var_dump($results);
		$this->setData($results[0]);
		Category::updateFile();

	}
	public function get($idcategory)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT*FROM tb_categories WHERE idcategory = :idcategory",[
			':idcategory'=>$idcategory]
		);
		$this->setData($results[0]);
	} 

	public function delete ()
	{

		$sql = new Sql();
		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$this->getidcategory()
		]);

		Category::updateFile();

	}

	public static function updateFile()
	{    // esta classe escreve no arquivo html as categorias que estao no banco

		$Categories = Category::listAll(); // lista todas categorias do banco

		$html = [];

		foreach ($Categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>'); // adiciona o texto no array, NO CASO O O TEXTO E UM HTML
		}

		//file_put_contents = Escreve dados em um arquivo
		//caminho do arquivo -> $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "viwes"  . DIRECTORY_SEPARATOR . "categories-menu.html"
		// - > texto a ser inserido no arquivo
		//implode = transforma array em texto
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views"  . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));




	}


	public function getProducts($related = true)
	{
		$sql = new Sql();
		$product = new Product();

		if ($related === true)
		{

			/*
			return $sql->select("
					SELECT * FROM db_ecommerce.tb_products WHERE idproduct IN 
					(
						SELECT a.idproduct
						FROM db_ecommerce.tb_products a
						INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory

					);
				", [':idcategory'=>$this->getidcategory()]); 

			*/

			$list = $sql->select("
					SELECT * FROM db_ecommerce.tb_products WHERE idproduct IN 
					(
						SELECT a.idproduct
						FROM db_ecommerce.tb_products a
						INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory

					);
				", [':idcategory'=>$this->getidcategory()]); 


			return $product->checkList($list);

		}
		else
		{
			
			$list = $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN 
				(

					SELECT a.idproduct
					FROM db_ecommerce.tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory

				);
			", [':idcategory'=>$this->getidcategory()]); 	

			return $product->checkList($list);

		}
	}

	public function getProductsPage($page = 1, $itemsPerPage = 1 )
	{

		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();

	

		$results = $sql->select("
			SELECT sql_calc_found_rows* 
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start,$itemsPerPage; 

			",[
				':idcategory'=>$this->getidcategory()
			]);

		$resultTotal = $sql->select("SELECT found_rows() AS nrtotal;");


		return [
			'data' => Product::checkList($results),
			'total' => (int)$resultTotal[0]["nrtotal"],
			'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage )
		];
	}


	public function addProduct(Product $product)
	{

		$sql = new sql();

		$sql->query("INSERT INTO tb_productscategories(idcategory, idproduct)  VALUES(:idcategory, :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()

		]);
	}


	public function removeProduct(Product $product)
	{

		$sql = new sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()

		]);
	}



}



?>
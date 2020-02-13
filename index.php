<?php 
session_start();
require_once("vendor/autoload.php");
require_once("functions.php");
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


$app = new Slim(); // Instaciando o slim pora usar rotas

$app->config('debug', true);  // Ativando o debug do slim framework

$app->get('/', function() { // Criando rota principal http://www.hcodecommerce.com/
    
    $products = Product::listAll();

	$page = new Page(); // Chama o construtor de hcode page que desenha o header

	$page->setTpl("index",
	 [	
		'products'=> Product::checkList($products)

	]); // Chama o arquivo index
	// apos terminar ele executa o destruct do pag.php que desenha o rodape

});
$app->get('/admin', function() { // Criando rota principal http://www.hcodecommerce.com/
    
	//User::verifyLogin(); // verifica se tem usuario logado

	$page = new PageAdmin(); // Chama o construtor de hcode page que desenha o header
	$page->setTpl("index"); // Chama o arquivo index
	// apos terminar ele executa o destruct do pag.php que desenha o rodape

});
$app->get('/admin/login', function(){

	$page = new PageAdmin([
		"header"=>false, // Desabilita o cabeçalo padrao, A NOVA PAGINA JA TEM O SEU RODAPE E CABEÇALHO
		"footer"=>false // Desabilita o rodape padrao 
	]); // nova pagina do pageadmin  C:\ecommerce\vendor\hcodebr\php-classes\src
	$page->setTpl("login");  // seta qual pagina vai ser aberta



});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]); // se login tiver ok
	header("Location: /admin");//redireciona
	exit;//Para execução

});
$app->get('/admin/logout', function(){

	User::logout();
	
	header("Location: /admin/login");
	exit;

});

$app->get('/admin/users', function(){

	//User::verifyLogin();

	$users=User::listAll();
	//var_dump($users);
	//echo"Aqui";
	$page = new PageAdmin(); // nova pagina do pageadmin  C:\ecommerce\vendor\hcodebr\php-classes\src
	
	$page->setTpl("users",array("users"=>$users));  // seta qual pagina vai ser aberta

 //array("users"=>$users)


});
$app->get('/admin/users/create', function(){

	//User::verifyLogin();

	$page = new PageAdmin(); // nova pagina do pageadmin  C:\ecommerce\vendor\hcodebr\php-classes\src
	$page->setTpl("users-Create");  // seta qual pagina vai ser aberta



});

$app->get("/admin/users/:iduser/delete", function($iduser){ // O :iduser vem na funçao ai ele ja entende que $iduser e o :iduser da rota

	//User::verifyLogin();
	
	$user = new User();
	$user->setiduser($iduser);
	$user->delete();
	header("Location: /admin/users");
	exit;



});

$app->get("/admin/users/:iduser", function($iduser){ // O :iduser vem na funçao ai ele ja entende que $iduser e o :iduser da rota
	
	//User::verifyLogin();
	
	$users=User::listUser($iduser);
	$page = new PageAdmin(); // nova pagina do pageadmin  C:\ecommerce\vendor\hcodebr\php-classes\src
	$page->setTpl("users-update",array("users"=>$users));  // seta qual pagina vai ser aberta
	
	
});

$app->post("/admin/users/create", function(){
	
	//User::verifyLogin();
	
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; // verifica se o chack esta ativo antes de seta para salvar
	$user->setData($_POST);
	$user->save();
	header("Location: /admin/users");
	exit;
	//var_dump($user);



});
/*
$app->post("/admin/users/:iduser", function($iduser){
	
	echo "aqui";
	User::verifyLogin();



});
*/

$app->post("/admin/users/:iduser", function($iduser){ // O :iduser vem na funçao ai ele ja entende que $iduser e o :iduser da rota

	//User::verifyLogin();
	
	$users=User::listUser($iduser);
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;	
	
	foreach ($users as $key => $value) {		
		foreach ($value as $key => $value) {
			if($key === "iduser")
			{
				$_POST["iduser"] = $value;
			}
			else if($key === "despassword")
			{
				$_POST["despassword"] = $value;
			}	
		}
	}

	$user->setData($_POST);
	$user->update();
	header("Location: /admin/users");
	exit;


});

$app->get("/admin/forgot", function(){

	//User::verifyLogin();
	$page = new PageAdmin([
		"header"=>false, // Desabilita o cabeçalo padrao, A NOVA PAGINA JA TEM O SEU RODAPE E CABEÇALHO
		"footer"=>false // Desabilita o rodape padrao 
	]); 
	$page->setTpl("forgot");  // seta qual pagina vai ser aberta


});

$app->post("/admin/forgot", function(){ // *************** AQUI ***************

	//User::verifyLogin();
	$user = User::getForgot($_POST["email"]);
	header("Location: /admin/forgot/sent");
	exit;

});

$app->get("/admin/forgot/sent", function(){

	//User::verifyLogin();
	$page = new PageAdmin([
		"header"=>false, // Desabilita o cabeçalo padrao, A NOVA PAGINA JA TEM O SEU RODAPE E CABEÇALHO
		"footer"=>false // Desabilita o rodape padrao 
	]); 
	$page->setTpl("forgot-sent");


});

$app->get("/admin/forgot/reset", function(){

	//User::verifyLogin();
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false, // Desabilita o cabeçalo padrao, A NOVA PAGINA JA TEM O SEU RODAPE E CABEÇALHO
		"footer"=>false // Desabilita o rodape padrao 
	]); 
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]

	));

});

$app->post("/admin/forgot/reset", function(){

	//User::verifyLogin();
	$forgot = User::validForgotDecrypt($_POST["code"]);
	//var_dump($forgot);
	//var_dump($_POST["password"]);
	//exit();
	User::setForgotUsed($forgot["idrecovery"]);
	$user = new User();
	//$user->get((int)$forgot["iduser"]);
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT); // cost e o nivel de processamento que o servidor vai ultlizar para criptografar a senha.  https://www.php.net/manual/pt_BR/function.password-hash.php

	$user->setPassword($password,$forgot["iduser"]);

	$page = new PageAdmin([
		"header"=>false, // Desabilita o cabeçalo padrao, A NOVA PAGINA JA TEM O SEU RODAPE E CABEÇALHO
		"footer"=>false // Desabilita o rodape padrao 
	]); 
	$page->setTpl("forgot-reset-success");


});
$app->get("/admin/categories", function()
{
		//User::verifyLogin();
		$categories = Category::listAll(); // lista todas categorias do banco

		$page = new PageAdmin(); 
		
		$page->setTpl("categories", // abre o HTML categories.html
			[ 
				'categories'=>$categories   // seta para o template as categorias retornadas do banco

			]);


});

$app->get("/admin/categories/create", function()
{
		//User::verifyLogin();
		$page = new PageAdmin(); 		
		$page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function()
{	
		//User::verifyLogin();
		$category = new Category();
		$category->setData($_POST);
		$category->save();
		header('Location: /admin/categories');
		exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory)
{
	//User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();
	header('Location: /admin/categories');
	exit;


});	

$app->get("/admin/categories/:idcategory", function($idcategory)  // tudo que vem na url vem como texto
{
		//User::verifyLogin();
		$category = new Category();
		$category->get((int)$idcategory); // pega so uma categoria especifica e seta no array data
		$page = new PageAdmin(); 		
		$page->setTpl("categories-update",[
			'category'=>$category->getValues()
		]);
});

$app->post("/admin/categories/:idcategory", function($idcategory)  // tudo que vem na url vem como texto
{
		//User::verifyLogin();
		$category = new Category();
		$category->get((int)$idcategory); // pega so uma categoria especifica e seta no array data
		$category->setData($_POST);
		$category->save();
		header('Location: /admin/categories');
		exit;
});

$app->get("/categories/:idcategory",function($idcategory)
{
		$category = new Category();

		$category->get((int)$idcategory); //pesquisa qual e a categoria e sera no values

		$page = new Page(); 
		$page->setTpl("category",[
			'category'=>$category->getValues(), // retorna os dados da categoria id e nome da categoria
			'products'=>$category->getProducts() //retorna todos produtos que pertence a categoria pesquisada no get

		]);
		//var_dump($category->getProducts());

});

$app->get("/admin/products",function()
{

		//User::verifyLogin();
		$products = Product::listAll();

		$page = new PageAdmin();

		$page->setTpl("products",[
			"products"=>$products
		]);


});

$app->get("/admin/products/create", function()
{

	//User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("products-create");


});
$app->post("/admin/products/create", function()
{

	//User::verifyLogin();
	
	$product = new Product();
	$product->setData($_POST);
	$product->save();
	header("Location:/admin/products ");
	exit;

});

$app->get("/admin/products/:idproduct", function($idproduct)
{

	//User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$page = new PageAdmin();

	$page->setTpl("products-update", [
		"product"=>$product->getValues()
	]);


});

$app->post("/admin/products/:idproduct", function($idproduct)
{

	//User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$product->setData($_POST);
	$product->save();
	$product->setPhoto($_FILES["file"]);
	header('Location: /admin/products');
	exit;


});

//Criar o excluir
$app->get("/admin/products/:idproduct/delete", function($idproduct)
{

	//User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$product->delete();
	header('Location: /admin/products');
	exit;


});

$app->get("/categories/:idcategory/products", function($idcategory){


		$category = new Category();

		$category->get((int)$idcategory);

		$page = new Page(); 

		$page->setTpl("category",[
			'category'=>$category->getValues(),
			'products'=>[]

		]);
});

$app->get("/admin/categories/:idcategory/products", function($idcategory){

		//User::verifyLogin(); //verifica se o usuario esta logado
		$category = new Category();

		$category->get((int)$idcategory); // pesquisa no banco a categoria pelo id passado na rota

		$page = new PageAdmin(); // tamplates do painel de admin

		$page->setTpl("categories-products",[
			'category'=>$category->getValues(),
			'productsRelated'=>$category->getProducts(), //tras os produtos relacionados
			'productsNotRelated'=>$category->getProducts(false) // tras os produtos nao relacionados

		]);
});


$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory,$idproduct){

		//User::verifyLogin(); //verifica se o usuario esta logado
		$category = new Category();

		$category->get((int)$idcategory); // pesquisa no banco a categoria pelo id passado na rota

		$product = new Product();

		$product->get((int)$idproduct);

		$category->addProduct($product);

		header("Location: /admin/categories/".$idcategory."/products");
		exit;
});



$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory,$idproduct){

		//User::verifyLogin(); //verifica se o usuario esta logado
		$category = new Category();

		$category->get((int)$idcategory); // pesquisa no banco a categoria pelo id passado na rota

		$product = new Product();

		$product->get((int)$idproduct);

		$category->removeProduct($product);

		header("Location: /admin/categories/".$idcategory."/products");
		exit;
});



$app->get("/products/:desurl", function($desurl){

	$product = new product();

	$product->getFromURL($desurl); // BUSCA O PRODUTO PELO NOME QUE VEM DA URL E SETA NO DATA

	$page = new Page();
	$page->setTpl("product-detail",[
				'product'=>$product->getValues(), // PEGA O VALOR QUE FOI SETADO NO GETFROMURL OU SEJA O PRODUTO
				'categories'=>$product->getCategories() // RELACIONA O PRODUTO QUE VEIIO DA URL COM A CATEGORIA E RETORNA 

]);

});

$app->get("/cart", function (){

	$cart = Cart::getFromSession();
	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for($i = 0; $i < $qtd; $i++)
	{

		$cart->addProduct($product);


	}
	
	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);
	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);
	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function(){


	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});


$app->get("/checkout", function(){


	$address = new Address();
	$cart = Cart::getFromSession();



	if(isset($_GET['zipcode'])) 
	{

		$_GET['zipcode'] = $cart->getdeszipcode();


    }
	if(isset($_GET['zipcode'])) // se $_GET('zipcode') foi definida e diferente de nulo ou seja ela existe
	{

		$address->loadFromCEP($_GET['zipcode']);
		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();
		$cart->getCalculateTotal();

	}

	if(!$address->getdesaddress()) $address->setdesaddress(''); //Se nao tiver valor seta como vazio
	if(!$address->getdescomplement()) $address->setdescomplement('');
	if(!$address->getdesdistrict()) $address->setdesdistrict('');
	if(!$address->getdescity()) $address->setdescity('');
	if(!$address->getdesstate()) $address->setdesstate('');
	if(!$address->getdescountry()) $address->setdescountry('');
	if(!$address->getdeszipcode()) $address->setdeszipcode('');


	$page = new Page();
	
	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
	]);

});


$app->post("/checkout", function(){


	if(!isset($_POST['zipcode']) || $_POST['zipcode'] === '' )
	{

		Address::setMsgError("Informe o CEP. ");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['desaddress']) || $_POST['desaddress'] === '' )
	{

		Address::setMsgError("Informe o ENDEREÇO. ");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '' )
	{

		Address::setMsgError("Informe o BAIRRO. ");
		header("Location: /checkout");
		exit;

	}	

	if(!isset($_POST['descity']) || $_POST['descity'] === '' )
	{

		Address::setMsgError("Informe a CIDADE. ");
		header("Location: /checkout");
		exit;

	}	

	if(!isset($_POST['desstate']) || $_POST['desstate'] === '' )
	{

		Address::setMsgError("Informe o ESTADO. ");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['descountry']) || $_POST['descountry'] === '' )
	{

		Address::setMsgError("Informe o PAÍS. ");
		header("Location: /checkout");
		exit;

	}
	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();
	
	$address->setData($_POST);

	$address->save();
	
	$cart = Cart::getFromSession();


	$totals = $cart->getCalculateTotal();

	$order = new Order();

	$order->setData([

		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(), // nullo    idaddress
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$totals['vlprice'] + $cart->getvlfreight()
	]);


	$order->save();


	header("Location: /order/".$order->getidorder());
	exit;	


});


$app->get("/login", function(){
	
	$page = new Page();
	
	$page->setTpl("login",[
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'','email'=>'','phone'=>'']
	]);

});

$app->post("/login", function(){
	
	try{

		$user = User::login($_POST['login'], $_POST['password']);
		if($user)
		{
			header("Location: /checkout");

		}


	}catch(Exception $e){

		User::setError($e->getMessage());
		header("Location: /login");		

	}

	//header("Location: /checkout");
	exit;

});

$app->get("/logout", function(){
	
	User::logout();
	header("Location: /login");
	exit;

});

$app->post("/register", function(){
	
	$_SESSION['registerValues'] = $_POST;

	if(!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}
	if(!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha o seu email.");
		header("Location: /login");
		exit;
	}
	if(!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha o seu senha.");
		header("Location: /login");
		exit;
	}

	if(User::checkLoginExist($_POST["email"])===true)
	{
		User::setErrorRegister("Este enderenço de e-mail ja esta sendo usado por outro usuario. ");
		header("Location: /login");
		exit;
	}



	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();
	User::login($_POST['email'],$_POST['password']);
	header("Location: /checkout");
	exit;

});















$app->get("/forgot", function(){

	
	$page = new Page(); 
	$page->setTpl("forgot");  // seta qual pagina vai ser aberta


});

$app->post("/forgot", function(){ 


	$user = User::getForgot($_POST["email"], false);
	header("Location: /forgot/sent");
	exit;

});

$app->get("/forgot/sent", function(){


	$page = new Page(); 
	$page->setTpl("forgot-sent");


});

$app->get("/forgot/reset", function(){


	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page(); 
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]

	));

});

$app->post("/forgot/reset", function(){


	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);
	$user = new User();
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT); // cost e o nivel de processamento que o servidor vai ultlizar para criptografar a senha.  https://www.php.net/manual/pt_BR/function.password-hash.php

	$user->setPassword($password,$forgot["iduser"]);

	$page = new Page(); 

	$page->setTpl("forgot-reset-success");


});

$app->get("/profile", function(){

	//user::verifyLogin(false);
	$user = new User();

	$user->setData(isset($_SESSION[User::SESSION])?$_SESSION[User::SESSION]:[]);


	$loginExist = User::checkLoginExist(isset($_SESSION[User::SESSION])?$_SESSION[User::SESSION]["deslogin"]:"");

	
	if($loginExist === false)
	{
		header("Location: /login");
		exit;
	}
	
	//var_dump($_SESSION[User::SESSION]);
	//exit;

	$page = new Page();
	
	$page->setTpl("profile", [
		//'user'=>User::listUser($dataUser["iduser"])[0],
		'user'=>User::listUser($_SESSION[User::SESSION]["iduser"])[0],
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()

	]);

});

$app->post("/profile", function(){

	//User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] ==='')
	{

		User::SetError("Preencha o seu nome !");
		header('Location: /profile');
		exit;

	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] ==='')
	{

		User::SetError("Preencha o seu email !");
		header('Location: /profile');
		exit;

	}

	$setUserSession = User::getFromSession();

	$user =  User::listUser($setUserSession->getiduser());

	if($_POST['desemail'] !== $user[0]["desemail"])
	{
		
		if(User::checkLoginExist($_POST['desemail']) === true)
		{

			User::setError("Este endereço de e-mail já está cadastrado.");
			header('Location: /profile');
			exit;


		}

	}

	$setUserSession->setData($_POST);
	$setUserSession->updateAccountUser();
	User::setSuccess("Dados alterados com sucesso !");
	header("Location: /profile");
	exit;

});

$app->get("/order/:idorder", function($idorder){

	//User::verifyLogin(false);


	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();
	
	$page->setTpl("payment", [
		'order'=>$order->getValues()

	]);


});




$app->get("/boleto/:idorder", function($idorder){

	//User::verifyLogin(false);

	$order = new Order();
	$order->get((int)$idorder);
	$cart = Cart::getFromSession();
	$cartGet = (array)$cart->getValues();

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = $cartGet["vltotal"]; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal

	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] =  $order->getdescity() ." - ". $order->getdesstate() . " " . $order->getdescountry() . " - CEP: 
	" . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;
	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");





});



$app->run(); // roda tudo

 ?>





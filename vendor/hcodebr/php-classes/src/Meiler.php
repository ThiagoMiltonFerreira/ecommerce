<?php

namespace Hcode;

use Rain\Tpl;

class Mailer {

	const USERNAME = "thiagomilton.f@gmail.com";
	const PASSWORD = "<?password?>";
	const NAME_FROM= "Hcode Store";

	private $mail;

	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{


		$config = array(			// $_SERVER["DOCUMENT_ROOT"] Pega o caminho atual do meu arquivo
					"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/viwes/email/", // Pasta dos Html
					"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", // Pasta de cache do html
					"debug"         => false // set to false to improve the speed
				   );

		Tpl::configure( $config );

		$tpl = new Tpl;

		foreach ($data as $key => $value) {
			$tpl->assign($key, $value);
		}
 		
 		$html = $tpl->draw($tplName, true);

		$this->mail = new \PHPMailer(); 
		 
		// Método de envio 
		$this->mail->IsSMTP(); 
		 
		// Enviar por SMTP 
		$this->mail->Host = "smtp.gmail.com"; 
		 
		// Você pode alterar este parametro para o endereço de SMTP do seu provedor 
		$this->mail->Port = 587; 
		 
		 
		// Usar autenticação SMTP (obrigatório) 
		$this->mail->SMTPAuth = true; 
		 
		// Usuário do servidor SMTP (endereço de email) 
		// obs: Use a mesma senha da sua conta de email 
		$this->mail->Username = Mailer::USERNAME; 
		$this->mail->Password = Mailer::PASSWORD; 
		 
		// Configurações de compatibilidade para autenticação em TLS 
		$this->mail->SMTPOptions = array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ) ); 
		 
		// Você pode habilitar esta opção caso tenha problemas. Assim pode identificar mensagens de erro. 
		$this->mail->SMTPDebug = 0; 
		 
		// Define o remetente 
		// Seu e-mail 
		$this->mail->From = Mailer::USERNAME; 
		 
		// Seu nome 
		$this->mail->FromName = Mailer::NAME_FROM; 
		 
		// Define o(s) destinatário(s) 
		$this->mail->AddAddress($toAddress, $toName); 
		 
		// Opcional: mais de um destinatário
		// $this->mail->AddAddress('fernando@email.com'); 
		 
		// Opcionais: CC e BCC
		// $this->mail->AddCC('joana@provedor.com', 'Joana'); 
		// $this->mail->AddBCC('roberto@gmail.com', 'Roberto'); 
		 
		// Definir se o e-mail é em formato HTML ou texto plano 
		// Formato HTML . Use "false" para enviar em formato texto simples ou "true" para HTML.
		$this->mail->IsHTML(true); 
		 
		// Charset (opcional) 
		$this->mail->CharSet = 'UTF-8'; 
		 
		// Assunto da mensagem 
		$this->mail->Subject = $subject;

		$this->mail->msgHtml($html);

		// Corpo do email 
		$this->mail->Body = $conteudoDoEmail; 
		 
		// Opcional: Anexos 
		// $this->mail->AddAttachment("/home/usuario/public_html/documento.pdf", "documento.pdf"); 
		 
		public function send()
		{

			return $this->mail->send();

		}


	}



}



?>
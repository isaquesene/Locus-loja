<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Recuperar_senha extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
		$this->load->model('Usuarios_model');

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Email');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_login');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{
		if(!$_POST)
		{
			$data['msg_status'] = '';
			$data['msg_texto']  = '';  
			$this->load->view('recuperar_senha', $data);
		}
		else
		{
            $email = $this->input->post('login-email');
            $dados_usuario = $this->Usuarios_model->recuperar_senha($email);
            
            if($dados_usuario)
            {
                $usu_nome  = '';
                $usu_login = '';
                $usu_senha = '';
                $usu_email = '';
                        
                foreach($dados_usuario as $dados)
                {
                    $usu_nome  = $dados->usu_nome;
                    $usu_login = $dados->usu_login;
                    $usu_senha = 'acessolocus@1234';
                    $usu_email = $dados->usu_email;			
                }

                if($this->Usuarios_model->recadastrar_senha($usu_login, $usu_senha))
                {
                    $smtp_host   = 'smtp.farmaconde.com.br';
                    $smtp_name   = 'Senha de Acesso - Sistema Locus Online';
                    $smtp_mail   = 'smtp@farmaconde.com.br';
                    $smtp_user   = 'smtp@farmaconde.com.br';
                    $smtp_pswd   = 'smtp102030';
                    $smtp_port   = '587';
                    $return_path = 'smtp@farmaconde.com.br';
                    $subject     = 'Senha de Acesso - Sistema Locus Online';
    
                    $config_mail['protocol']     = 'smtp';
                    $config_mail['charset']      = 'utf-8';
                    $config_mail['wordwrap']     = TRUE;
                    $config_mail['smtp_host']    = $smtp_host;
                    $config_mail['smtp_user']    = $smtp_user;
                    $config_mail['smtp_pass']    = $smtp_pswd;
                    $config_mail['smtp_port']    = $smtp_port;
                    $config_mail['smtp_timeout'] = 60;
                    $config_mail['mailtype']     = 'html';
                    $config_mail['return-path']  = $return_path;
                    $config_mail['crlf']         = '\r\n'; 
                    $config_mail['newline']      = '\r\n';	
                    
                    $this->email->initialize($config_mail);
                    $MsgTxt = '
                        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
                        <style type="text/css">
                            a:link, a:active, a:visited, a:hover{color: #FFFFFF; font-weight: bold; text-decoration: none;}
                            p{color:#373E4A; font-size:12px;}
    
                            div.clear{clear: both;}
                        </style>
    
                        </head>
    
                        <body style="font-family: verdana, arial, sans-serif;">
                            <div style="width: 600px; margin: 0 auto; padding:0; background-color:transparent;">
                                <div style="width: 600px; height: 100px; margin: 0; padding: 0; background-color:#C21120; border: 1px solid #C21120;">
                                    <p style="color:#FFFFFF; font-size:32px; font-weight:bold; padding: 0 20px;">Locus Online</p>			
                                </div>
                                <div style="width: 560px; margin: 0; padding: 20px; background-color:#FFFFFF; border: 0 solid #C21120; border-width: 0 1px;">
                                    <p style="color:#373E4A; font-size:14px; font-weight:bold;">Prezado(a) ' . $usu_nome . '</p>
                                    <br />
                                    
                                    <p>Segue abaixo os dados de acesso para a área restrita do Sistema Locus Online.</p>
                                    <p>Para entrar no sistema utilize o seu login e sua senha:</p>
                                    <br />
                                    
                                    <p><span style="color:#373E4A; font-size:12px; font-weight:bold;">Login:</span> ' . $usu_login . '</p>
                                    <p><span style="color:#373E4A; font-size:12px; font-weight:bold;">Senha de acesso ao sistema:</span> ' . $usu_senha  . '</p>
                                    <br />
                                    
                                    <p>Atenciosamente,</p>
                                    <p>Equipe de TI</p>
                                    
                                </div>
                                <div style="width: 580px; height: 20px; margin: 0; padding: 0; background-color:#C21120;padding: 10px; border: 1px solid #C21120;">
                                    <a href="">Locus Online</a>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </body>
                        </html>
                        ';
    
                    $this->email->from($smtp_mail, $smtp_name);            // Email de envio - Site                
                    $this->email->to($usu_email);                          // Email de Recebimento - Usuário                
                    $this->email->reply_to($return_path, $return_path);    // Email de Retorno                
                    //$this->email->cc('');                                // Cópia Carbono oculta
                    //$this->email->bcc('');                               // Cópia Carbono oculta
                    $this->email->subject($subject);                       // Assunto do Email
                    $this->email->message($MsgTxt);                        // Mensagem do Email
                    
                    if (!$this->email->send())
                    {
                        $data['msg_status'] = 'ERRO';
                        $data['msg_texto']  = 'Erro ao enviar senha por email.';   
                        $this->load->view('recuperar_senha', $data);
                    }
                    else
                    {
                        $data['msg_status'] = 'OK';
                        $data['msg_texto']  = 'Senha enviada com sucesso para o email ' . $usu_email . '.';   
                        $this->load->view('recuperar_senha', $data);
                    } 
                }
                else
                {
                    $data['msg_status'] = 'ERRO';
                    $data['msg_texto']  = 'Erro ao enviar senha por email. Entre em contato com o departamento de TI.';   
                    $this->load->view('recuperar_senha', $data);
                }
            }
            else
            {
                $data['msg_status'] = 'ERRO';
                $data['msg_texto']  = 'Email não encontrado. Entre em contato com o departamento de TI.';  
                $this->load->view('recuperar_senha', $data);
            }
		}
	}
}

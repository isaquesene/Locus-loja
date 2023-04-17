<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Login extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
		$this->load->model('Usuarios_model');
		$this->load->model('Logs_model');

        // Carregar BIBLIOTECAS utilizadas

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
			$this->load->view('login', $data);
		}
		else
		{
			$usuario = $this->input->post('login-usuario');
			$senha   = trim($this->input->post('login-senha'));
	
			$dados_usuario = $this->Usuarios_model->obtem_dados_login($usuario, $senha);		
			if($dados_usuario)
			{
				// VERIFICA SE USUÁRIO ESTÁ USANDO A SENHA PADRÃO
				if($senha == 'acessolocus@1234')
				{
					redirect(base_url(). 'alterar_senha', 'refresh');
				}
				else
				{
					foreach($dados_usuario as $dados):
						// --------------------------------------------------------
						// Criar Cookie para lembrar/esquecer login e senha
						// --------------------------------------------------------
						if(!empty($this->input->post('login-lembrar'))) 
						{
							setcookie ("locus2_usu_login",$usuario,time()+ (10 * 365 * 24 * 60 * 60));
							setcookie ("locus2_usu_senha",$senha,time()+ (10 * 365 * 24 * 60 * 60));
						} 
						else 
						{
							if(isset($_COOKIE["locus2_usu_login"])) {
								setcookie ("locus2_usu_login","");
							}
							if(isset($_COOKIE["locus2_usu_senha"])) {
								setcookie ("locus2_usu_senha","");
							}
						}

						$usu_id 	= $dados->usu_id;
						$usu_nome 	= $dados->usu_nome;
						$usu_login 	= $dados->usu_login;
						$usu_email 	= $dados->usu_email;
						$usu_img 	= $dados->usu_img;
						$usu_perfil = $dados->usu_id_per;

						if($dados->usu_aloc_temporaria)
						{
							$usu_codapo = $dados->usu_aloc_temporaria;
						}
						else
						{
							$usu_codapo = $dados->usu_cod_apontamento;
						}

						$newsession = array
						(
							'usu_id'  		=> $usu_id,
							'usu_nome' 		=> $usu_nome,
							'usu_login'     => $usu_login,
							'usu_email'   	=> $usu_email,
							'usu_img'   	=> $usu_img,
							'usu_perfil'   	=> $usu_perfil,
							'usu_codapo'	=> $usu_codapo,
							'loggedin'      => TRUE
						);
						$this->session->set_userdata($newsession);

						// ---------------------------------------------------------------------
						// Gravar Log de acesso ao sistema
						// ---------------------------------------------------------------------
						$this->Logs_model->grava_log_login($dados->usu_login, 'LOGIN_OK');
							
						redirect(base_url(). 'dashboard', 'refresh'); 

					endforeach;
				}
			}
			else
			{
				// ---------------------------------------------------------------------
				// Gravar Log de acesso ao sistema
				// ---------------------------------------------------------------------
				$this->Logs_model->grava_log_login($usuario, 'LOGIN_INCORRETO');

				$data['msg_status'] = 'ERRO';
				$data['msg_texto']  = 'Login e/ou Senha incorretos.';
				$this->load->view('login', $data);
			}	
		}

	}
	
}

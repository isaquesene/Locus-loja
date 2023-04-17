<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Alterar_senha extends CI_Controller 
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
        $this->load->library('Form_validation');

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
			$this->load->view('alterar_senha', $data);
		}
		else
		{
            $this->form_validation->set_error_delimiters('<div class="erro-form-validation">', '</div>');
        
            // MONTA ARRAY PARA VALIDAÇÃO DOS CAMPOS
            $config = array
            (
                array(
                    'field' => 'new-usuario',
                    'label' => 'Login',
                    'rules' => 'required'
                ),
                array(
                    'field' => 'new-senha-antiga',
                    'label' => 'Senha Antiga',
                    'rules' => 'required|callback_senha_check'
                ),
                array(
                    'field' => 'new-senha-nova',
                    'label' => 'Senha Nova',
                    'rules' => 'required|min_length[6]|differs[new-senha-antiga]'
                ),
                array(
                    'field' => 'new-senha-conf',
                    'label' => 'Confirmação',
                    'rules' => 'required|min_length[6]|matches[new-senha-nova]'
                )
            );
    
            // VALIDAÇÃO DOS CAMPOS
            $this->form_validation->set_rules($config); 

            if ($this->form_validation->run() == FALSE)
            {
                $data['msg_status'] = 'ERRO';
                $data['msg_texto']  = 'Erro ao alterar a senha. Verifique.';  
                $this->load->view('alterar_senha', $data);
            }
            else
            {
                $usu_login = $this->input->post('new-usuario');
                $usu_senha = trim($this->input->post('new-senha-nova'));

				if($this->Usuarios_model->recadastrar_senha($usu_login, $usu_senha))
				{
                    unset($_POST);

					$data['msg_status'] = 'OK';
					$data['msg_texto']  = 'Senha alterada com sucesso. Clique <a href="' .  base_url() . '">AQUI</a> para entrar no sistema.';  
					$this->load->view('alterar_senha', $data);
				}
				else
				{
					$data['msg_status'] = 'ERRO';
					$data['msg_texto']  = 'Erro ao alterar a senha.';  
                    $this->load->view('alterar_senha', $data);
				}
            }
		}
    }
    
    /*============================================================================================*/
    // FUNÇÃO PARA VALIDAR SENHA
    function senha_check($str_senha)
    {
        // VALIDAR SE FOI INFORMADO UM USUÁRIO
        if(isset($_POST['new-usuario']) AND !empty($_POST['new-usuario']))
        {
            $str_usuario = $this->input->post('new-usuario');
            $mensagem = $this->Usuarios_model->verifica_senha_padrao($str_usuario, $str_senha);

            if($mensagem == 'OK')
            {
                return TRUE;
            }
            else
            {
                $this->form_validation->set_message('senha_check', $mensagem );
                return FALSE;
            }
        }
        else
        {
            $this->form_validation->set_message('senha_check', 'Informe um usuário primeiro.');
            return FALSE;
        }
    }
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Profile_visualizar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Usuarios_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 9999; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Form_validation');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_formulario');
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
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            $this->load->view('profile_visualizar', $data);
        }
        else
        {
            $this->form_validation->set_error_delimiters('<div class="erro-form-validation">', '</div>');
        
            // MONTA ARRAY PARA VALIDAÇÃO DOS CAMPOS
            $config = array
            (
                array(
                    'field' => 'senha-antiga',
                    'label' => 'Senha Antiga',
                    'rules' => 'required|callback_senha_check'
                ),
                array(
                    'field' => 'senha-nova',
                    'label' => 'Senha Nova',
                    'rules' => 'required|min_length[6]|differs[senha-antiga]'
                ),
                array(
                    'field' => 'senha-nova-conf',
                    'label' => 'Confirmação',
                    'rules' => 'required|min_length[6]|matches[senha-nova]'
                )
            );
    
            // VALIDAÇÃO DOS CAMPOS
            $this->form_validation->set_rules($config); 

            if ($this->form_validation->run() == FALSE)
            {
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao alterar a senha. Verifique.';  
                $this->load->view('profile_visualizar', $data);
            }
            else
            {
                $usu_login = $this->session->userdata('usu_login');
                $usu_senha = trim($this->input->post('senha-nova'));
				$usu_senha = $usu_senha;

				if($this->Usuarios_model->recadastrar_senha($usu_login, $usu_senha))
				{
                    unset($_POST);

					$data['msg_status'] = 'OK';
					$data['msg_texto']  = 'Senha alterada com sucesso.';  
                    $this->load->view('profile_visualizar', $data);
				}
				else
				{
					$data['msg_status'] = 'ERRO';
					$data['msg_texto']  = 'Erro ao alterar a senha.';  
                    $this->load->view('profile_visualizar', $data);
				}
            }
        }
    }

    /*============================================================================================*/
    // FUNÇÃO PARA VALIDAR SENHA
    function senha_check($str_senha)
    {
        $str_login = $this->session->userdata('usu_login');
        $mensagem = $this->Usuarios_model->verifica_senha_padrao($str_login, $str_senha);

        if($mensagem == 'OK')
        {
            return TRUE;
        }
        else
        {
            $this->form_validation->set_message('senha_check', $mensagem);
            return FALSE;
        }
    }

}

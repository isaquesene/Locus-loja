<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Usuarios_alocar_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Usuarios_model');
        $this->load->model('Filial_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 38; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_2');
        //$this->load->library('library_3');

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
        $usu_id = $this->uri->segment(2, 0);
        if($usu_id)
        {
            $nome_supervisor = $this->session->userdata('usu_nome');
            $data['msg_status']   = '';
            $data['msg_texto']    = '';            
            $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_regional($nome_supervisor);
            $data['lista_filiais'] = $this->Filial_model->obtem_todas_filiais();

            
            $this->load->view('usuarios_alocar_editar', $data);
        }
        else
        {
            redirect(base_url(). 'usuarios_alocar', 'refresh'); 
        }
    }
    
    public function atualizar()
	{
        $usu_id = $this->input->post('form_usu_id');
        $usu_nome = $this->input->post('form_nome');
        $usu_apontamento = $this->input->post('form_cod_apontamento');
        $usu_alocacao = $this->input->post('form_alocacao');
        $nome_supervisor = $this->session->userdata('usu_nome');
        $email_supervisor = $this->session->userdata('usu_email');
        if($usu_id)
        {
            $dados = array
            (
                'usu_aloc_temporaria' => $usu_alocacao,
                
            );

            if($this->Usuarios_model->atualizar_usuario($usu_id, $dados))
            {
                $this->load->helper('envioEmail');
                $mensagem = 'O Supervisor <b>'.$nome_supervisor.'</b> solicita a alteração temporária do código de apontamento do funcionário <b>'.$usu_nome.'</b> da loja <b>'.$usu_apontamento.'</b> para loja <b>'.$usu_alocacao.' </b>.';

                if(msgEmailAlocar($email_supervisor ,$mensagem) == true){

                    $data['msg_status']   = 'OK';
                    $data['msg_texto']    = 'Usuário Atualizado com sucesso. Clique <a href="' . base_url() . 'usuarios_alocar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                    $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_unico($usu_id);
                    $data['lista_filiais'] = $this->Filial_model->obtem_todas_filiais();
                    $this->load->view('usuarios_alocar_editar', $data);	

                } else{
                    $data['msg_status']   = 'ERRO';
                    $data['msg_texto']    = 'Erro ao Atualizar Usuário.';  
                    $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_unico($usu_id);
                    $data['lista_filiais'] = $this->Filial_model->obtem_todas_filiais();
                    $this->load->view('usuarios_alocar_editar', $data);

                }
                

            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Atualizar Usuário.';  
                $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_unico($usu_id);
                $data['lista_filiais'] = $this->Filial_model->obtem_todas_filiais();
                $this->load->view('usuarios_alocar_editar', $data);
            }
        }
        else
        {
            redirect(base_url(). 'usuarios_alocar', 'refresh'); 
        }
			
	} 
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Usuarios_editar extends CI_Controller 
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
        $tela  = 35; //Cod. da tela - Tabela t_telas
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
            $data['msg_status']   = '';
            $data['msg_texto']    = '';
            $data['lista_perfil']  = $this->Usuarios_model->obtem_todos_perfis();
            $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_unico($usu_id);
            $this->load->view('usuarios_editar', $data);
        }
        else
        {
            redirect(base_url(). 'usuarios_gerenciar', 'refresh'); 
        }
    }
    
    public function atualizar()
	{
        $usu_id = $this->input->post('form_usu_id');
        if($usu_id)
        {
            $dados = array
            (
                'usu_nome'            => mb_strtoupper($this->input->post('form_nome'), 'UTF-8'),
                'usu_email'           => mb_strtolower($this->input->post('form_email')),
                'usu_situacao'        => $this->input->post('form_situacao'),
                'usu_cod_apontamento' => $this->input->post('form_cod_apontamento'),
                'usu_id_senior'       => $this->input->post('form_id_senior'),
                'usu_id_per'          => $this->input->post('form_id_per'),
                'usu_cargo'           => $this->input->post('form_cargo'),
            );

            if($this->Usuarios_model->atualizar_usuario($usu_id, $dados))
            {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Usuário Atualizado com sucesso. Clique <a href="' . base_url() . 'usuarios_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                $data['lista_perfil']  = $this->Usuarios_model->obtem_todos_perfis();
                $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_unico($usu_id);
                $this->load->view('usuarios_editar', $data);	
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Atualizar Usuário.';  
                $data['lista_perfil']  = $this->Usuarios_model->obtem_todos_perfis();
                $data['dados_usuario'] = $this->Usuarios_model->obtem_dados_usuario_unico($usu_id);
                $this->load->view('usuarios_editar', $data);
            }
        }
        else
        {
            redirect(base_url(). 'usuarios_gerenciar', 'refresh'); 
        }
			
	} 
}

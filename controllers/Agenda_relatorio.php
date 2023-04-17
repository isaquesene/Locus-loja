<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Agenda_relatorio extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Agenda_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 18; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');
        //$this->load->library('library_2');
        //$this->load->library('library_3');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_datatable');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{
        if(!$_POST){

            $data['msg_status']   = '';
            $data['msg_texto']    = '';  

            //$data['dados_perfis'] = $this->Usuarios_model->obtem_todos_perfis();
            $this->load->view('agenda_relatorio', $data);

        } else {

            $this->consultar_coleta_agenda();
        
        }
        
    }    

    private function consultar_coleta_agenda(){

        $data['msg_status']   = '';
        $data['msg_texto']    = '';  
        
        $form_data_inicio     = $this->input->post('form_data_inicio');
		$form_data_final	  = $this->input->post('form_data_final');	
		$form_status_coleta   = $this->input->post('form_status_coleta');
		
		// CONVERTANDO A DATA DE FORMATO BRASILEIRO PARA AMERICANO
        $form_data_inicio   = implode('-', array_reverse(explode('/', $form_data_inicio)));
        $form_data_final    = implode('-', array_reverse(explode('/', $form_data_final)));

        $data['dados_coleta'] = $this->Agenda_model->obtem_coleta_filtro($form_data_inicio,$form_data_final,$form_status_coleta);           
        $this->load->view('agenda_relatorio', $data);
        

    }
	

}

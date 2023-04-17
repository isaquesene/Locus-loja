<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Relatorio_matching extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Telas_model');
        $this->load->model('RelatorioMatching_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 53; //Cod. da tela - Tabela t_telas
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
          
        $this->consultar_relatorio();
        
    }
    private function consultar_relatorio(){

        $data['msg_status']   = '';
        $data['msg_texto']    = '';
        $data['info'] = false;

        if($_POST){
        $form_data_inicio = $this->input->post('form_data_inicio');
        $form_data_final = $this->input->post('form_data_final');
        // CONVERTANDO A DATA DE FORMATO BRASILEIRO PARA AMERICANO
        $form_data_inicio   = implode('-', array_reverse(explode('/', $form_data_inicio)));
        $form_data_final    = implode('-', array_reverse(explode('/', $form_data_final)));
        $data['dados_relatorio'] = $this->RelatorioMatching_model->obtem_dados_relatorio($form_data_inicio,$form_data_final);
        $data['info'] = true;
    }

        $this->load->view('relatorio_matching', $data);
    }

}

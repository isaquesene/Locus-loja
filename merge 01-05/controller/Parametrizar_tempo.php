<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Parametrizar_tempo extends CI_Controller {

    function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados                
        $this->load->model('Nf_model'); 
        $this->load->model('Telas_model');       

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 22; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Form_validation');

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
        $filial = $this->session->userdata('usu_codapo');     
        
        $data['msg_status']   = '';
        $data['msg_texto']    = '';  

        $data['grid_dados'] = '';

        $this->load->view('parametrizar_tempo', $data);
    }

    public function salvar(){

        $tempo = $this->input->post('tempo'); 

        $this->Nf_model->set_parametro_tempo_nf_nao_entregue($tempo);   
       
        redirect(base_url().'alteracao_notas', 'refresh');
    }
    

}

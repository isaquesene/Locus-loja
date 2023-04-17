<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Inventario_gerenciar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Inventario_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 56; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

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
        $data['msg_status']   = '';
        $data['msg_texto']    = '';  

		$data['dados_inventario'] = $this->Inventario_model->obtem_dados_inventario();
		$this->load->view('inventario_gerenciar', $data);
    }
    
    public function deletar(){
        $conf_id = $this->uri->segment(3,0);
        if($this->Inventario_model->deletar_inventario($conf_id)){
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Parametrização apagada com sucesso.';  
                
                $data['dados_inventario'] = $this->Inventario_model->obtem_dados_inventario();
                $this->load->view('inventario_gerenciar', $data);		
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Apagar Parametrização.';  
                
                $data['dados_inventario'] = $this->Inventario_model->obtem_dados_inventario();
                $this->load->view('inventario_gerenciar', $data);
        }	
    }
	
	 
}

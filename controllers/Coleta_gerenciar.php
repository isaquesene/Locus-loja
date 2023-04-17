<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Coleta_gerenciar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Coleta_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 50; //Cod. da tela - Tabela t_telas
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
        $data['dados_dias'] = $this->Coleta_model->obtem_nomes_dia_semana();
		$data['dados_coleta'] = $this->Coleta_model->obtem_dados_coleta();
		$this->load->view('coleta_gerenciar', $data);
    }

    public function deletar(){

        $param_col_id = $this->uri->segment(3,0);
        if($this->Coleta_model->deletar_coleta($param_col_id)){
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Parametrização de coleta apagada com sucesso.';  
                
                $data['dados_dias'] = $this->Coleta_model->obtem_nomes_dia_semana();
		        $data['dados_coleta'] = $this->Coleta_model->obtem_dados_coleta();
                $this->load->view('coleta_gerenciar', $data);		
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Apagar Parametrização de Coleta.'; 

                $data['dados_dias'] = $this->Coleta_model->obtem_nomes_dia_semana();
		        $data['dados_coleta'] = $this->Coleta_model->obtem_dados_coleta();
                $this->load->view('coleta_gerenciar', $data);
        }	
    }
	
	 
}

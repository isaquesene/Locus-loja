<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Agenda_gerenciar extends CI_Controller 
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
        $tela  = 14; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}

        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Email');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_calendario');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{
        $data['msg_status'] = '';
        $data['msg_texto']  = '';  

        /*if($this->session->userdata('usu_perfil') == 3){

            $data['dados_agenda'] = $this->Agenda_model->obtem_calendario_logistica();

        } else {

            $data['dados_agenda'] = $this->Agenda_model->obtem_calendario_loja($this->session->userdata('usu_perfil'));

        }*/

        /*$filial =  $this->session->userdata('usu_codapo');
        if($this->session->userdata('usu_codapo') == 3){

            $perfil = 'LOGISTICA';

        } else {

            $perfil = 'LOJA';

        }*/
        $data['dados_agenda'] = $this->Agenda_model->obtem_calendario_logistica();       
        $this->load->view('agenda_gerenciar', $data);        
        
    }
    
    public function salvar() {

        $lista_notas = $this->input->post('form_num_nota');
        $nota_aux = '';
        
        for ($i=0;$i<count($lista_notas);$i++)
        {
            if($lista_notas[$i] != $nota_aux){

                $nota_aux = $lista_notas[$i];
                $this->Agenda_model->inserir_agenda_coleta($nota_aux);  
            } 
            
        }
        
        $data['msg_status'] = '';
        $data['msg_texto']  = '';
        $data['dados_agenda'] = $this->Agenda_model->obtem_calendario_logistica();       
        $this->load->view('agenda_gerenciar', $data); 
        
    }

	

}

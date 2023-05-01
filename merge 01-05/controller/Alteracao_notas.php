<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Alteracao_notas extends CI_Controller 
{
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

        $data['tempo'] = $this->Nf_model->get_tempo_nf_nao_entregue();

        $tempo = $data['tempo'][0]->param_nf_nao_recebidas;        

        $this->Nf_model->atualiza_nf_para_nao_entregue($tempo);        

        $this->load->view('alteracao_notas', $data);
    }

    public function buscar_nota_nao_entregue(){               
        
        $num_nota = $this->input->post('form-chave'); 

        $data['grid_dados'] = $this->Nf_model->obtem_nf_nao_entregue($num_nota);
        
        $this->load->view('alteracao_notas', $data);
        
    }
    
    public function maracar_como_entregue(){        
        
        $num_nota = $this->uri->segment(3, 0);

        $partes = explode("_", $num_nota);

        $num_nota = $partes[0];
        $id_nota = $partes[1]; 

        $this->Nf_model->maracar_como_entregue($num_nota,$id_nota);
        
        redirect(base_url().'alteracao_notas', 'refresh');
    }
}
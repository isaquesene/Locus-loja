<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Conferencia_volumes extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Conferencia_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 16; //Cod. da tela - Tabela t_telas
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
        
        // verifica se a loja está em inventário
        // conforme tabela de inventário
        $data['inventario'] = $this->Conferencia_model->inventario($filial);
        if (!$data['inventario']) {
            // não estando em inventário, movimenta estoque
            // que eventualmente tenha ficado na fila durante
            // um inventário
            $this->Conferencia_model->movimentaEstoqueAuxiliar($filial);
        }

        $data['msg_status']   = '';
        $data['msg_texto']    = '';  

        $data['grid_dados'] = $this->Conferencia_model->obtem_conferencias_filial($filial);
        
        $data['parametro_conferencia_obrigatoria'] = $this->Conferencia_model->parametro_conferencia_obrigatoria();

        $this->load->view('conferencia_volumes', $data);

    }
}
<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Inventario_add_ajax extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        //$this->load->model('model_1');
        $this->load->model('Regiao_model');
        $this->load->model('Filial_model');
        $this->load->model('Inventario_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');

	}
        
	public function index()
	{
        echo "FALSE";
	}

    public function getRegionais () {
        $dados['nomes_regional'] = $this->Regiao_model->obtem_nomes_regional();
        echo json_encode($dados);
    }

    public function getLojas () {
        $dados['filiais'] = $this->Regiao_model->obtem_dados_filiais();
        echo json_encode($dados);
    }

    public function getStoreByRegion () {
        $region_id = $this->input->post('region_id');
        $dados['filiais'] = $this->Filial_model->obtem_dados_filiais_por_regiao($region_id);

        echo json_encode($dados);
    }

  
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Percentual_adicionar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Veiculos_model');
        $this->load->model('Filial_model');
        $this->load->model('ClassificacaoProdutos_model');
        $this->load->model('Percentual_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 49; //Cod. da tela - Tabela t_telas
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
        $data['msg_status']   = '';
        $data['msg_texto']    = '';  
        $data['data_filial']  = $this->Filial_model->obtem_id_filiais();
        $data['data_classificacao']  = $this->ClassificacaoProdutos_model->obtem_dados_classificacao_produtos();
		$this->load->view('percentual_adicionar', $data);
	}
	
	public function adicionar()
	{

        $conf_fil_id = $this->input->post('fil_num_loja');
        $conf_cpp_id = $this->input->post('cpp_id');
        $conf_perc =  str_replace([','],'.', $this->input->post('conf_perc'));

        if(!$this->Percentual_model->verifica_cadastro_percentual($conf_fil_id,$conf_cpp_id)){

            $dados = array
            (
                'conf_fil_id'	 => $conf_fil_id,
                'conf_cpp_id'	 => $conf_cpp_id,
                'conf_perc' 	 => $conf_perc	
            );

            if($this->Percentual_model->adicionar_percentual($dados))
            {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Percentual Cadastrado com sucesso. Clique <a href="' . base_url() . 'percentual_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                $this->load->view('percentual_adicionar', $data);
            
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Cadastrar Percentual.';  
                $this->load->view('percentual_adicionar', $data);
            }
        }else{
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Loja e percentual para o tipo de produto já cadastrada no sistema. Clique <a href="' . base_url() . 'percentual_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
            $this->load->view('percentual_adicionar', $data);
        }
		
	} 
}

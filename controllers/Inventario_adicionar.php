<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Inventario_adicionar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Filial_model');
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
		$this->load->view('inventario_adicionar', $data);
	}
	
	public function adicionar()
	{
        $parinv_loja = $this->input->post('fil_num_loja');
        $parinv_hora_limite = $this->input->post('parinv_hora_limite');
        if(!$this->Inventario_model->verifica_cadastro_inventario($parinv_loja)){
            $dados = array(
                'parinv_loja'	 => $parinv_loja,
                'parinv_hora_limite'	 => $parinv_hora_limite
            );
            if($this->Inventario_model->adicionar_inventario($dados)) {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Item cadastrado com sucesso. Clique <a href="' . base_url() . 'inventario_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                $this->load->view('inventario_adicionar', $data);
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao cadastrar item.';  
                $this->load->view('inventario_adicionar', $data);
            }
        }else{
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Loja já cadastrada no sistema. Clique <a href="' . base_url() . 'inventario_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
            $this->load->view('inventario_adicionar', $data);
        }
	} 
}

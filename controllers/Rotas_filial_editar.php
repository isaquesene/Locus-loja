<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Rotas_filial_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Rotas_model');
        $this->load->model('Filial_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 31; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');

		$this->_init();
	}

	private function _init()
	{
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
        $fil_num_loja = $this->uri->segment(2, 0);
        $data['msg_status']   = '';
        $data['msg_texto']    = '';  
		$data['dados_rotas']  = $this->Rotas_model->obtem_dados_rotas();
		$data['dados_filial'] = $this->Filial_model->obtem_dados_filial_numloja($fil_num_loja);
		$this->load->view('rotas_filial_editar', $data);
	}

	public function atualizar()
	{
	    $fil_id = $this->input->post('fil_id');
		$dados = array
		(
			'fil_id_rota' => $this->input->post('rot_descricao'),
		);
 
		if($this->Filial_model->atualizar_filial($fil_id, $dados))
		{
            $data['msg_status']   = 'OK';
            $data['msg_texto']    = 'Rota Alterada com sucesso. Clique <a href="' . base_url() . 'rotas_filial_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
            $data['dados_rotas']  = $this->Rotas_model->obtem_dados_rotas();
            $data['dados_filial'] = $this->Filial_model->obtem_dados_filial_numloja($fil_id);
            $this->load->view('rotas_filial_editar', $data);
		
		}else{
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Erro ao Alterar Rota.';  
            $data['dados_rotas']  = $this->Rotas_model->obtem_dados_rotas();
            $data['dados_filial'] = $this->Filial_model->obtem_dados_filial_numloja($fil_id);
            $this->load->view('rotas_filial_editar', $data);
		}	
			
		
		
		
	}
}

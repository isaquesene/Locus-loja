<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Inventario_editar extends CI_Controller 
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
        $parinv_id = $this->uri->segment(2, 0);
        if($parinv_id) {
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            $data['dados_inventario'] = $this->Inventario_model->obtem_dados_inventario_unico($parinv_id);
            $this->load->view('inventario_editar', $data);
        } else {
            redirect(base_url(). 'inventario_gerenciar', 'refresh'); 
        }
	}
	
	public function atualizar()
	{
        $parinv_id = $this->input->post('parinv_id');
        if($parinv_id) {
            $parinv_loja	 = $this->input->post('parinv_loja');
            $parinv_hora_limite	 = substr($this->input->post('parinv_hora_limite'), 0, 5);
            $dados = array(
                'parinv_loja'	 => $parinv_loja,
                'parinv_hora_limite'	 => $parinv_hora_limite,
            );
            if ($this->Inventario_model->atualizar_inventario($parinv_id, $dados)) {
                    $data['msg_status']   = 'OK';
                    $data['msg_texto']    = 'Item atualizado com sucesso. Clique <a href="' . base_url() . 'inventario_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                    $data['dados_inventario'] = $this->Inventario_model->obtem_dados_inventario_unico($parinv_id);
                    $this->load->view('inventario_editar', $data);		
            } else {
                    $data['msg_status']   = 'ERRO';
                    $data['msg_texto']    = 'Erro ao atualizar item. Clique <a href="' . base_url() . 'inventario_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                    $data['dados_inventario'] =$this->Inventario_model->obtem_dados_inventario_unico($parinv_id);
                    $this->load->view('inventario_editar', $data);
            }
        } else {
            redirect(base_url(). 'inventario_editar', 'refresh'); 
        }
		
	} 
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Modulos_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 26; //Cod. da tela - Tabela t_telas
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
        $mod_id = $this->uri->segment(2, 0);

        $data['msg_status']   = '';
        $data['msg_texto']    = '';  
		$data['dados_modulos'] = $this->Telas_model->obtem_dados_modulo_unico($mod_id);
		$this->load->view('modulos_editar', $data);
	}
	
	public function atualizar()
	{
        $mod_id        = $this->input->post('mod_id');
        $mod_descricao = mb_strtoupper($this->input->post('mod_descricao'), 'UTF-8');
        $mod_icone     = mb_strtolower($this->input->post('mod_icone'), 'UTF-8');

		$dados = array
        (
            'mod_descricao'	=> $mod_descricao,
            'mod_icone'	    =>  $mod_icone
        );

        if($this->Telas_model->valida_nome_modulo($mod_descricao) == FALSE)
        {
            if($this->Telas_model->atualizar_modulo($mod_id, $dados))
            {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Módulo Atualizado com sucesso. Clique <a href="' . base_url() . 'modulos_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                $data['dados_modulos'] = $this->Telas_model->obtem_dados_modulo_unico($mod_id);
                $this->load->view('modulos_editar', $data);
            
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Atualizar Módulo.';  
                $data['dados_modulos'] = $this->Telas_model->obtem_dados_modulo_unico($mod_id);
                $this->load->view('modulos_editar', $data);
            }	
        }
        else
        {
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Já existe Módulo cadastrado com esse nome.';  
            $data['dados_modulos'] = $this->Telas_model->obtem_dados_modulo_unico($mod_id);
            $this->load->view('modulos_editar', $data);
        }
		
		
	} 
}

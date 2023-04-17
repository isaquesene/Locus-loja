<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Chamados_resp_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Chamados_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 43; //Cod. da tela - Tabela t_telas
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
        $chr_id = $this->uri->segment(2, 0);
        if($chr_id)
        {
            if(!$_POST)
            {
                $data['msg_status']   = '';
                $data['msg_texto']    = '';
                $data['dados_formulario'] = $this->Chamados_model->obtem_dados_chamado_resp($chr_id);
                $this->load->view('chamados_resp_editar', $data);
            }
            else
            {
                $this->alterar($chr_id);
            }
        }
        else
        {
            redirect(base_url(). 'chamados_resp_gerenciar', 'refresh'); 
        }
        
	}
	
	private function alterar($chr_id)
	{
		$dados = array
		(
            'chr_descricao' => $this->input->post('form_mensagem'),
            'chr_status'    => $this->input->post('form_status')
		);

		if($this->Chamados_model->atualizar_chamado_resp($chr_id, $dados))
		{
            $data['msg_status']   = 'OK';
            $data['msg_texto']    = 'Resposta Alterada com Sucesso. Clique <a href="' . base_url() . 'chamados_resp_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior.';
            $data['dados_formulario'] = $this->Chamados_model->obtem_dados_chamado_resp($chr_id);
            $this->load->view('chamados_resp_editar', $data);
		
		}else{
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Erro ao Alterar Resposta.';
            $data['dados_formulario'] = $this->Chamados_model->obtem_dados_chamado_resp($chr_id);
            $this->load->view('chamados_resp_editar', $data);
		}	

	} 
}

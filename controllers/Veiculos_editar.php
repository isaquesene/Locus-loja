<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Veiculos_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Veiculos_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 37; //Cod. da tela - Tabela t_telas
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
        $vei_id = $this->uri->segment(2, 0);
        if($vei_id)
        {
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            $data['dados_veiculos'] = $this->Veiculos_model->obtem_dados_veiculo_unico($vei_id);
            $this->load->view('veiculos_editar', $data);
        }
        else
        {
            redirect(base_url(). 'veiculos_gerenciar', 'refresh'); 
        }
	}
	
	public function atualizar()
	{
        $vei_id = $this->input->post('vei_id');
        if($vei_id)
        {
            $vei_nome	 = $this->input->post('vei_nome');
            $vei_placa	 = mb_strtoupper($this->input->post('vei_placa'));
            $vei_tipo 	 = $this->input->post('vei_tipo');

            if($this->Veiculos_model->verifica_placa_existe($vei_placa) == FALSE)
            {
                $dados = array
                (
                    'vei_nome'	 => $vei_nome,
                    'vei_placa'	 => $vei_placa,
                    'vei_tipo' 	 => $vei_tipo	
                );
        
                if($this->Veiculos_model->atualizar_veiculo($vei_id, $dados))
                {
                    $data['msg_status']   = 'OK';
                    $data['msg_texto']    = 'Veiculo Atualizado com sucesso. Clique <a href="' . base_url() . 'veiculos_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                    $data['dados_veiculos'] = $this->Veiculos_model->obtem_dados_veiculo_unico($vei_id);
                    $this->load->view('veiculos_editar', $data);		
                }else{
                    $data['msg_status']   = 'ERRO';
                    $data['msg_texto']    = 'Erro ao Atualizar Veiculo.';  
                    $data['dados_veiculos'] = $this->Veiculos_model->obtem_dados_veiculo_unico($vei_id);
                    $this->load->view('veiculos_editar', $data);
                }
            }
            else
            {
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Placa já cadastrada no sistema.';  
                $this->load->view('veiculos_adicionar', $data);
            }

            	
        }
        else
        {
            redirect(base_url(). 'veiculos_gerenciar', 'refresh'); 
        }
		
	} 
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Parametrohoras_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Parametrohoras_model');
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
        $conf_obrigatoria_id = $this->uri->segment(2, 0);
        if($conf_obrigatoria_id)
        {
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            $data['dados_parametrohoras'] = $this->Parametrohoras_model->obtem_dados_conferencia_obrigatoria_unico($conf_obrigatoria_id);
            $this->load->view('parametrohoras_editar', $data);
        }
        else
        {
            redirect(base_url(). 'parametrohoras_gerenciar', 'refresh'); 
        }
	}
	
	public function atualizar()
	{
        $conf_obrigatoria_id = $this->input->post('conf_obrigatoria_id');

        if($conf_obrigatoria_id){

            $conf_obrigatoria_valor	 = $this->input->post('conf_obrigatoria_valor');
            $dados = array
                (
                    'conf_obrigatoria_valor'	 => $conf_obrigatoria_valor,
                );
            if (preg_match('/^[0-9]+$/',$conf_obrigatoria_valor)){
                if($this->Parametrohoras_model->atualizar_parametro_conferencia_obrigatoria($conf_obrigatoria_id, $dados))
                {
                        $data['msg_status']   = 'OK';
                        $data['msg_texto']    = 'Paramêtro de horas atualizado com sucesso. Clique <a href="' . base_url() . 'parametrohoras_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                        $data['dados_parametrohoras'] = $this->Parametrohoras_model->obtem_dados_conferencia_obrigatoria_unico($conf_obrigatoria_id);
                        $this->load->view('parametrohoras_editar', $data);		
                }else{
                        $data['msg_status']   = 'ERRO';
                        $data['msg_texto']    = 'Erro ao Atualizar parâmetro de horas. Clique <a href="' . base_url() . 'parametrohoras_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                        $data['dados_parametrohoras'] =$this->Parametrohoras_model->obtem_dados_conferencia_obrigatoria_unico($conf_obrigatoria_id);
                        $this->load->view('parametrohoras_editar', $data);
                }    
            }else{
                        $data['msg_status']   = 'ERRO';
                        $data['msg_texto']    = 'Erro ao Atualizar parâmetro de horas. Insira um valor válido. Clique <a href="' . base_url() . 'parametrohoras_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                        $data['dados_parametrohoras'] =$this->Parametrohoras_model->obtem_dados_conferencia_obrigatoria_unico($conf_obrigatoria_id);
                        $this->load->view('parametrohoras_editar', $data);
            }
            	
        }else{
            redirect(base_url(). 'parametrohoras_editar', 'refresh'); 
        }
		
	} 
}

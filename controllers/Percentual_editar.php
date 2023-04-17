<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Percentual_editar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
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
        $conf_id = $this->uri->segment(2, 0);
        if($conf_id)
        {
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            $data['dados_perc'] = $this->Percentual_model->obtem_dados_percentual_unico($conf_id);
            $this->load->view('percentual_editar', $data);
        }
        else
        {
            redirect(base_url(). 'percentual_gerenciar', 'refresh'); 
        }
	}
	
	public function atualizar()
	{
        

        $conf_id = $this->input->post('conf_id');
        if($conf_id)
        {
            $conf_fil_id	 = $this->input->post('conf_fil_id');
            $conf_cpp_id	 = $this->input->post('conf_cpp_id');
            $conf_perc =  str_replace([','],'.', $this->input->post('conf_perc'));

            $dados = array
                (
                    'conf_fil_id'	 => $conf_fil_id,
                    'conf_cpp_id'	 => $conf_cpp_id,
                    'conf_perc' 	 => $conf_perc	
                );
        
            if($this->Percentual_model->atualizar_percentual($conf_id, $dados))
            {
                    $data['msg_status']   = 'OK';
                    $data['msg_texto']    = 'Percentual Atualizado com sucesso. Clique <a href="' . base_url() . 'percentual_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                    $data['dados_perc'] = $this->Percentual_model->obtem_dados_percentual_unico($conf_id);
                    $this->load->view('percentual_editar', $data);		
            }else{
                    $data['msg_status']   = 'ERRO';
                    $data['msg_texto']    = 'Erro ao Atualizar Percentual. Clique <a href="' . base_url() . 'percentual_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                    $data['dados_perc'] =$this->Percentual_model->obtem_dados_percentual_unico($conf_id);
                    $this->load->view('percentual_editar', $data);
            }
            
            	
        }
        else
        {
            redirect(base_url(). 'percentual_editar', 'refresh'); 
        }
		
	} 
}

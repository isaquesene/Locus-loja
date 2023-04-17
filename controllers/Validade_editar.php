<?php defined('BASEPATH') or exit('No direct scripts acess allowed');

class Validade_editar extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Validade_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 50; //Cod. da tela - Tabela t_telas
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
    
    public function index(){
        $val_fil_id = $this->uri->segment(2,0);
        if($val_fil_id){
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            $data['dados_validade'] = $this->Validade_model->obtem_dados_validade_unico($val_fil_id);
            $this->load->view('validade_editar', $data);
        }else{
            edirect(base_url(). 'validade_gerenciar', 'refresh'); 
        }
    }

    public function atualizar(){

        $val_id = $this->input->post('val_id');
        if($val_id)
        {
            $val_id = $this->input->post('val_id');
            $val_fil_id = $this->input->post('val_fil_id');
            $val_dias =  $this->input->post('val_dias');

            $dados = array
                (
                    'val_fil_id'	 => $val_fil_id,
                    'val_dias' 	 => $val_dias	
                );
            if (preg_match('/^[0-9]+$/', $val_dias)){
                if($this->Validade_model->atualizar_validade($val_id, $dados))
                {
                        $data['msg_status']   = 'OK';
                        $data['msg_texto']    = 'Parâmetro de validade atualizado com sucesso. Clique <a href="' . base_url() . 'validade_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                        $data['dados_validade'] = $this->Validade_model->obtem_dados_validade_unico($val_id);
                        $this->load->view('validade_editar', $data);		
                }else{
                        $data['msg_status']   = 'ERRO';
                        $data['msg_texto']    = 'Erro ao atualizar paramêtro de validade. Clique <a href="' . base_url() . 'validade_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                        $data['dados_validade'] =$this->Validade_model->obtem_dados_validade_unico($val_id);
                        $this->load->view('validade_editar', $data);
                }
                
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao cadastrar o parâmetro no sistema. Insira um valor valido para dias. Clique <a href="' . base_url() . 'validade_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior';  
                $data['dados_validade']  = $this->Validade_model->obtem_dados_validade_unico($val_id);
                $this->load->view('validade_editar', $data);
            }
            	
        }
        else
        {
            redirect(base_url(). 'validade_editar', 'refresh'); 
        }
    }
}


?>
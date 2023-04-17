<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Lacres_cadastrar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Lacres_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 9; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Form_validation');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_datatable');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{
        if(!$_POST)
		{
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
    
            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
            $this->load->view('lacres_cadastrar', $data);
		}
		else
		{
            $this->adicionar();
        }

	}

    private function adicionar()
    {
        $this->form_validation->set_error_delimiters('<div class="erro-form-validation">', '</div>');
        
        // MONTA ARRAY PARA VALIDAÇÃO DOS CAMPOS
        $config = array
        (
            array(
                'field' => 'form-lacre',
                'label' => 'Número do Lacre',
                'rules' => 'required|callback_num_lacre_check'
            )
        );

        // VALIDAÇÃO DOS CAMPOS
        $this->form_validation->set_rules($config); 

        if ($this->form_validation->run() == FALSE)
        {
            $data['msg_status']  = 'ERRO';
            $data['msg_texto']   = 'Erro ao inserir dados. Verifique.';  

            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
            $this->load->view('lacres_cadastrar', $data);
        }
        else
        {
            $dados = array
            (
                'lac_numero'         => mb_strtoupper($this->input->post('form-lacre')),
                'lac_data_cadastro'  => date('Y-m-d H:i:s', strtotime('NOW')),
                'lac_status'         => 'SEM VINCULO',
                'lac_observacao'     => ''
            );

            if($this->Lacres_model->adicionar_lacre($dados))
            {
                $data['msg_status']  = 'OK';
                $data['msg_texto']   = 'Lacre Cadastrado com Sucesso.';

                $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
                $this->load->view('lacres_cadastrar', $data);
            }
            else
            {
                $data['msg_status']  = 'ERRO';
                $data['msg_texto']   = 'Erro ao Cadastrar Lacre.';

                $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
                $this->load->view('lacres_cadastrar', $data);
            }
        }
    }
	
    function num_lacre_check($num_lacre)
    {
        if($this->Lacres_model->verifica_lacre_existe($num_lacre) == TRUE)
        {
            $this->form_validation->set_message('num_lacre_check', 'Lacre <strong>' . $num_lacre . '</strong> já está cadastrado no sistema!');
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    public function deletar()
	{	
        $lac_id = $this->uri->segment(3, 0);	

        if($this->Lacres_model->verifica_status_lacre($lac_id) == 'SEM VINCULO')
        {
            if($this->Lacres_model->deletar_lacre($lac_id))
            {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Lacre apagado com sucesso.';  
                
                $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
                $this->load->view('lacres_cadastrar', $data);		
            }else{
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao apagar lacre.';  
                
                $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
                $this->load->view('lacres_cadastrar', $data);
            }
        }
        else
        {
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Lacre não pode ser apagado!';  
            
            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
            $this->load->view('lacres_cadastrar', $data);
        }
	}

    public function desvincular()
    {
        $lac_id = $this->uri->segment(3, 0);

        if($this->Lacres_model->desvincular_lacre($lac_id) == 'TRUE')
        {
            $data['msg_status']   = 'OK';
            $data['msg_texto']    = 'Lacre desvinculado com sucesso.';  
            
            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
            $this->load->view('lacres_cadastrar', $data);	
        }
        else
        {
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Lacre não pode ser desvinculado!';  
            
            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres();
            $this->load->view('lacres_cadastrar', $data);
        }
    }
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lacres_vincular_rotas extends CI_Controller 
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
        $this->load->model('Rotas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 10; //Cod. da tela - Tabela t_telas
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
    
            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres_rotas();
            $data['lista_rotas']  = $this->Rotas_model->obtem_dados_rotas();
            $this->load->view('lacres_vincular_rotas', $data);
		}
		else
		{
            $this->vincular();
        }
    }
    
    private function vincular()
    {
        $this->form_validation->set_error_delimiters('<div class="erro-form-validation">', '</div>');
        
        // VALIDAÇÃO DOS CAMPOS INFORMADOS
        $config = array
        (
            array(
                'field' => 'form-lac-numero',
                'label' => 'Número do Lacre',
                'rules' => 'required|callback_num_lacre_check'
            )
        );
        $this->form_validation->set_rules($config); 

        // ALTERAÇÂO DOS DADOS
        if ($this->form_validation->run() == FALSE)
        {
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Erro ao inserir dados. Verifique.';  

            $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres_rotas();
            $data['lista_rotas']  = $this->Rotas_model->obtem_dados_rotas();
            $this->load->view('lacres_vincular_rotas', $data);
        }
        else
        {
            $lac_numero = $this->input->post('form-lac-numero');
            $dados = array
            (
                'lac_id_rota'   => $this->input->post('form-lac-rota'),
                'lac_status'    => 'VINCULADO'
            );
            if($this->Lacres_model->vincular_lacres_rota($lac_numero, $dados))
            {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Lacre Vinculado com sucesso.';  

                $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres_rotas();
                $data['lista_rotas']  = $this->Rotas_model->obtem_dados_rotas();
                $this->load->view('lacres_vincular_rotas', $data);
            }
            else
            {
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao Vincular Lacre.';  

                $data['dados_lacres'] = $this->Lacres_model->obtem_todos_lacres_rotas();
                $data['lista_rotas']  = $this->Rotas_model->obtem_dados_rotas();
                $this->load->view('lacres_vincular_rotas', $data);
            }
        }
    }

    // VALIDAÇÂO DO NUMERO DO LACRE
    function num_lacre_check($num_lacre)
    {
        if($this->Lacres_model->verifica_lacre_existe($num_lacre) == FALSE)
        {
            $this->form_validation->set_message('num_lacre_check', 'Lacre <strong>' . $num_lacre . '</strong> não cadastrado no sistema!');
            return FALSE;
        }
        elseif($this->Lacres_model->verifica_vinculo_lacre($num_lacre) == FALSE)
        {
            $this->form_validation->set_message('num_lacre_check', 'Lacre <strong>' . $num_lacre . '</strong> já vinculado a uma rota!');
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

}

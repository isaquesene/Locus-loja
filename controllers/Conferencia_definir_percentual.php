<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Conferencia_definir_percentual extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Conferencia_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 15; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Form_validation');

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
        if(!$_POST)
		{
            $data['msg_status']   = '';
            $data['msg_texto']    = '';  

            $this->load->view('conferencia_definir_percentual', $data);
        }
        else
        {
            $this->listar();
        }
    }

    private function listar()
    {
        $this->form_validation->set_error_delimiters('<div class="erro-form-validation">', '</div>');
        
        // MONTA ARRAY PARA VALIDAÇÃO DOS CAMPOS
        $config = array
        (
            array(
                'field' => 'form-data-inicial',
                'label' => 'Data Inicial',
                'rules' => 'required'
            ),
            array(
                'field' => 'form-data-final',
                'label' => 'Data Final',
                'rules' => 'required'
            )
        );

        // VALIDAÇÃO DOS CAMPOS
        $this->form_validation->set_rules($config); 

        if ($this->form_validation->run() == FALSE)
        {
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Erro ao buscar dados . Verifique!';  
    
            //$data['dados_grid'] = $this->Conferencia_model->obtem_nf_conferencia();
            $this->load->view('conferencia_definir_percentual', $data);
        }
        else
        {
            $data_inicial = implode('-', array_reverse(explode('/', $this->input->post('form-data-inicial'))));
            $data_final   = implode('-', array_reverse(explode('/', $this->input->post('form-data-final'))));
            $nota_fiscal  = trim($this->input->post('form-nota-fiscal'));

            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
            
            $data['dados_grid']          = $this->Conferencia_model->obtem_lista_nf_conferencia($data_inicial, $data_final, $nota_fiscal);
            $data['lista_controlados']   = $this->Conferencia_model->obtem_volumes_controlados($nota_fiscal);
            $data['filtro_data_inicial'] = implode('/', array_reverse(explode('-', $data_inicial)));;
            $data['filtro_data_final']   = implode('/', array_reverse(explode('-', $data_final)));;
            $data['filtro_nota_fiscal']  = $nota_fiscal;
            $this->load->view('conferencia_definir_percentual', $data);
        }
    }

    public function salvar()
    {
        $grid_itens_array   = $this->input->post("grid_item_id");
        $grid_cbn_id        = $this->input->post("grid_cbn_id");
        $data_inicial       = implode('-', array_reverse(explode('/', $this->input->post("grid-data-inicial"))));
        $data_final         = implode('-', array_reverse(explode('/', $this->input->post("grid-data-final"))));
        $nota_fiscal        = $this->input->post("grid-nota-fiscal");

        for ($i=0; $i<count($grid_itens_array); $i++)
        {
            $grid_volume        = $grid_itens_array[$i];
            $nome_checkbox      = 'grid-checkbox-' . $grid_volume;
            $flag_controlado    = 'grid-controlado-' . $grid_volume;
            
            // Se volume tem controlados, define automaticamente como 100%
            if ($this->input->post($flag_controlado) == 'SIM')
            {
                $status_volume = 'SIM';
            }
            else
            {
                if($this->input->post($nome_checkbox) == 'on')
                {
                    $status_volume = 'SIM';
                }
                else
                {
                    $status_volume = 'NAO';
                }
            }

            $this->Conferencia_model->mudar_status_conferencia($grid_volume, $grid_cbn_id, $status_volume);

        }

        $data['msg_status']   = 'OK';
        $data['msg_texto']    = 'Conferência 100% Atualizada com Sucesso!';

        $data['dados_grid']          = $this->Conferencia_model->obtem_lista_nf_conferencia($data_inicial, $data_final, $nota_fiscal);
        $data['lista_controlados']   = $this->Conferencia_model->obtem_volumes_controlados($nota_fiscal);
        $data['filtro_data_inicial'] = implode('/', array_reverse(explode('-', $data_inicial)));;
        $data['filtro_data_final']   = implode('/', array_reverse(explode('-', $data_final)));;
        $data['filtro_nota_fiscal']  = $nota_fiscal;
        $this->load->view('conferencia_definir_percentual', $data);
    }

}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Recebimento_divergencia extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Nf_model');
        $this->load->model('Telas_model');
        $this->load->model('Chamados_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 48; //Cod. da tela - Tabela t_telas
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
        $num_nota       = $this->uri->segment(3,0);
        $num_volume     = $this->uri->segment(4,0);
        $data['action_gravar']   = base_url().'recebimento_divergencia/conferir/'.$num_nota.'/'.$num_volume.'';
        
        if(!$_POST){

            $data['msg_status']   = '';
            $data['msg_texto']    = '';  
    
            $data['dados_tela'] = $this->Nf_model->obtem_dados_divergencia($num_nota,$num_volume);
            $this->load->view('Recebimento_divergencia', $data);

        } else {

            //$this->carregar_grid($num_nota,$num_volume);
            $this->gravar_dados_grid($num_nota,$num_volume);
        
        }

    

    }
    
    
    public function gravar_dados_grid($num_nota,$num_volume)
	{
        
        $data['action_gravar']   = base_url().'recebimento_divergencia/conferir/'.$num_nota.'/'.$num_volume.'';        
        $array_pro_id       = $this->input->post('pro_id');
        $array_divergencia  = $this->input->post('itd_id_tipo_divergencia');
        $array_obs          = $this->input->post('itd_obs');
        $array_qtd          = $this->input->post('itd_qtd');
        $array_codigo       = $this->input->post('itd_codigo');
        $teste              = "";

        for($i = 0, $j = count($array_pro_id); $i < $j; $i++){
            
            $pro_id         = $array_pro_id[$i];
            $divergencia    = $array_divergencia[$pro_id];
            $obs            = $array_obs[$pro_id];
            $qtd            = $array_qtd[$pro_id];
            $codigo         = $array_codigo[$pro_id];

            
            $this->Nf_model->gravar_item_divergencia($num_volume,$num_nota,$pro_id,$qtd,$divergencia,$obs);
            
            $filial          = $this->session->userdata('usu_codapo');
            $data_volume     = date('Y/m/d H:i:s', strtotime('NOW'));            
            
            $this->Nf_model->gravar_movimentacao_estoque($data_volume,$filial,$pro_id,$qtd,$divergencia,$num_nota);
            
                
            $this->load->helper('envioEmail');
            $mensagem = 'TESTE RECEBIMENTO DIVERGENCIA';
            $email_usuario = $this->session->userdata('usu_email');
            msgEmailDivergencia($email_usuario ,$mensagem);
        
        }   

        $tipo_divergencia_aux = '';
        $dados_divergencias = $this->Nf_model->buscar_itens_divergencias($num_nota,$num_volume);
        foreach($dados_divergencias AS $dados){

            if($dados->itd_id_tipo_divergencia != $tipo_divergencia_aux ){

                $tipo_divergencia_aux = $dados->itd_id_tipo_divergencia;

                $array_chamado =  array
                (
                    'cha_data_abertura'     => $data_volume,
                    'cha_solicitante'       => $filial,
                    'cha_nota_fiscal'       => $num_nota,
                    'cha_id_mensagem'       => '1', // A DEFINIR
                    'cha_status'            => 'PENDENTE',
                    'cha_tipo'              => $divergencia,
                    'cha_id_volume'         => $num_volume
                );

                $id_chamado = $this->Chamados_model->adicionar_chamado($array_chamado);
            }

            $array_chamado_item =  array
            (
                'chi_id_chamado'        => $id_chamado,
                'chi_id_pro'            => $filial,
                'chi_qtd'               => $num_nota,
                'chi_obs'               => '1' // A DEFINIR            
            );
            $this->Chamados_model->adicionar_item_chamado($array_chamado_item);
        }           
        
        $data['msg_status']   = 'OK';
        $data['msg_texto']    = 'Divergência gravada com sucesso!';  

        $data['dados_tela'] = $this->Nf_model->obtem_dados_divergencia($num_nota,$num_volume);
        $this->load->view('Recebimento_divergencia', $data);

            
    }
	

}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Conferencia_recebimento_externo extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Conferencia_model');
        $this->load->model('Produto_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 44; //Cod. da tela - Tabela t_telas
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
        $chave = $this->uri->segment(3, 0);
        $data['action_grid'] = base_url() . 'conferencia_recebimento_externo/conferir/' . $chave;


        if(!$_POST)
        {
            $filial = $this->session->userdata('usu_codapo');
    
            $data['msg_status']     = '';
            $data['msg_texto']      = '';
            $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
            $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
            $data['volume_controlado']  = $this->Conferencia_model->verificar_nota_controlada($chave, $filial);
            // obtem um array com os pro_id dos produtos controlados
            $data['produtos_controlados']  = $this->Conferencia_model->get_codigos_produtos_controlados_nota($chave, $filial);

            $this->load->view('conferencia_recebimento_externo', $data);

        }
        else
        {
            $num_nf = $this->input->post('form_nota_fiscal');
            $chave = $this->input->post('form_chave_nota');
            $filial = $this->session->userdata('usu_codapo');


            
            if(!empty($this->input->post('form_ean')))
            {
                $cod_ean = trim($this->input->post('form_ean'));                         
                $naoLocalizadoNaNota= false;
                // tenta localizar na nota, considerando o EAN
                $dados_conf = $this->Conferencia_model->obtem_conferencia_ean_externo($num_nf, $chave, $cod_ean);
                if (!$dados_conf) {
                    // não está na nota, então busca como antes, sem considerar o EAN
                    $naoLocalizadoNaNota = true;
                    $dados_conf = $this->Conferencia_model->obtem_conferencia_ean_externo($num_nf, $chave);
                }
                $dados_produtos = $this->Produto_model->obtem_produto_ean($cod_ean);

                if( $dados_produtos == FALSE ) // não achou o produto na base, independente da nota
                {
                    $data['msg_status']     = 'ERRO';
                    $data['msg_texto']      = 'EAN não foi localizado!';
                    // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                    // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
            
                    // $this->load->view('conferencia_recebimento_externo', $data);
                    $this->session->set_flashdata('flash-data', $data);
                    redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh');
                }
                // não faz mais sentido, nunca entrará aqui
                // elseif( $dados_conf == FALSE ) // encontra o produto, mas não localiza ele nos itens da nota
                // {
                //     $data['msg_status']     = 'ERRO';
                //     $data['msg_texto']      = 'EAN do produto informado não pertence a nota!';
                //     // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                //     // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
            
                //     // $this->load->view('conferencia_recebimento_externo', $data);
                //     $this->session->set_flashdata('flash-data', $data);
                //     redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh');
                // }
                else // as duas condicoes anteriores são TRUE
                {
                    $cnf_nota_fiscal    = 0;
                    $cnf_volume         = '';
                    $cnf_produto_id     = 0;
                    $cnf_produto_dsc    = '';

                    foreach($dados_conf AS $dados)
                    {
                        $cnf_nota_fiscal    = $dados->cbn_num_nota;
                        $cnf_chave_nota     = $dados->cbn_chave_nota;
                        $cnf_produto_id     = $dados_produtos[0]->pro_id;
                        $cnf_produto_dsc    = $dados_produtos[0]->pro_descricao;
                        $cnf_cbn_id         = $dados->cbn_id;
                        $cnf_itn_id         = ($naoLocalizadoNaNota) ? null : $dados->itn_id;
                    }

                    if($this->Conferencia_model->inserir_conferencia_externa_temporaria($cnf_nota_fiscal, $cnf_chave_nota, $cnf_produto_id, $cnf_produto_dsc, $cnf_cbn_id, $cnf_itn_id) == TRUE)
                    {

                        // verifica se a validade do produto está fora do parametro da loja
                        $data['passou_da_validade_permitida'] = false;
                        if (!empty($cnf_itn_id)) {
                            $filial = $this->session->userdata('usu_codapo');
                            if ($this->Conferencia_model->passou_da_validade_permitida($cnf_itn_id, $filial)) {
                                $data['passou_da_validade_permitida'] = true;
                            }
                        }

                        $data['modal_abrir']     = $cnf_produto_id;
                        $data['msg_status']     = 'OK';
                        $data['msg_texto']      = 'EAN ' . $cod_ean . ' Adicionado / Atualizado com Sucesso!';
                        // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                        // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
                
                        // $this->load->view('conferencia_recebimento_externo', $data);
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
                    }
                    else
                    {
                        $data['msg_status']     = 'ERRO';
                        $data['msg_texto']      = 'Erro ao inserir EAN!';
                        // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                        // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
                
                        // $this->load->view('conferencia_recebimento_externo', $data);
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
                    }
                }
            }
            elseif(!empty($this->input->post('form_produto')))
            {
                $cod_produto = trim($this->input->post('form_produto'));
                $naoLocalizadoNaNota= false;
                // tenta localizar na nota, considerando o EAN
                $dados_conf = $this->Conferencia_model->obtem_conferencia_produto_externo($num_nf, $chave, $cod_produto);
                if (!$dados_conf) {
                    // não está na nota, então busca como antes, sem considerar o EAN
                    $naoLocalizadoNaNota = true;
                    $dados_conf = $this->Conferencia_model->obtem_conferencia_produto_externo($num_nf, $chave);
                }
                $dados_produtos = $this->Produto_model->obtem_produto_codigo($cod_produto);

                if($dados_produtos == FALSE) // não achou o produto na base, independente da nota
                {
                    $data['msg_status']     = 'ERRO';
                    $data['msg_texto']      = 'Código do produto não localizado!';
                    // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                    // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
            
                    //$this->load->view('conferencia_recebimento_externo', $data);
                    $this->session->set_flashdata('flash-data', $data);
                    redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 

                }
                elseif($dados_conf == FALSE) // achou o produto na base, mas não nos itens da nota
                {
                    $data['msg_status']     = 'ERRO';
                    $data['msg_texto']      = 'Código do produto informado não pertence a nota!';
                    // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                    // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
            
                    //$this->load->view('conferencia_recebimento_externo', $data);
                    $this->session->set_flashdata('flash-data', $data);
                    redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
                }
                else
                {
                    $cnf_nota_fiscal    = 0;
                    $cnf_volume         = '';
                    $cnf_produto_id     = 0;
                    $cnf_produto_dsc    = '';

                    foreach($dados_conf AS $dados)
                    {
                        $cnf_nota_fiscal    = $dados->cbn_num_nota;
                        $cnf_chave_nota         = $dados->cbn_chave_nota;
                        $cnf_produto_id     = $dados_produtos[0]->pro_id;
                        $cnf_produto_dsc    = $dados_produtos[0]->pro_descricao;
                        $cnf_cbn_id         = $dados->cbn_id;
                        $cnf_itn_id         = ($naoLocalizadoNaNota) ? null : $dados->itn_id;
                    }
                    
                    if($this->Conferencia_model->inserir_conferencia_externa_temporaria($cnf_nota_fiscal, $cnf_chave_nota, $cnf_produto_id, $cnf_produto_dsc, $cnf_cbn_id, $cnf_itn_id) == TRUE)
                    {

                        // verifica se a validade do produto está fora do parametro da loja
                        $data['passou_da_validade_permitida'] = false;
                        if (!empty($cnf_itn_id)) {
                            $filial = $this->session->userdata('usu_codapo');
                            if ($this->Conferencia_model->passou_da_validade_permitida($cnf_itn_id, $filial)) {
                                $data['passou_da_validade_permitida'] = true;
                            }
                        }
                        
                        $data['modal_abrir']     = $cnf_produto_id;
                        $data['msg_status']     = 'OK';
                        $data['msg_texto']      = 'Produto ' . $cod_produto . ' Adicionado / Atualizado com Sucesso!';
                        // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                        // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
                
                        // $this->load->view('conferencia_recebimento_externo', $data);
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
                    }
                    else
                    {
                        $data['msg_status']     = 'ERRO';
                        $data['msg_texto']      = 'Erro ao inserir Cod. Produto!';
                        // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                        // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
                
                        // $this->load->view('conferencia_recebimento_externo', $data);
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
                    }
                }
            }
            else
            {
                $data['msg_status']     = 'ERRO';
                $data['msg_texto']      = 'Informe um EAN ou Produto';
                // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);

                // $this->load->view('conferencia_recebimento_externo', $data);
                $this->session->set_flashdata('flash-data', $data);
                redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
            }
        }
    }

    // ATUALIZA MODAL DE QUANTIDADE DE AVARIAS
    public function conferir2()
    {

        echo '1';
        $chave = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');
        $data['action_grid'] = base_url() . 'conferencia_recebimento_externo/conferir/' . $chave;

        $produto        = $this->input->post('fmodal_avarias_codproduto');
        $recebimento    = $this->input->post('fmodal_avarias_recebimento');
        $qtd_manchado   = $this->input->post('fmodal_avaria_manchado');
        $qtd_amassado   = $this->input->post('fmodal_avaria_amassado');
        $qtd_rasgado    = $this->input->post('fmodal_avaria_rasgado');
        $qtd_quebrado   = $this->input->post('fmodal_avaria_quebrado');
        $qtd_violado    = $this->input->post('fmodal_avaria_violado');

        $this->Conferencia_model->gravar_modal_avarias($recebimento, 'MANCHADO', $qtd_manchado);
        $this->Conferencia_model->gravar_modal_avarias($recebimento, 'AMASSADO', $qtd_amassado);
        $this->Conferencia_model->gravar_modal_avarias($recebimento, 'RASGADO', $qtd_rasgado);
        $this->Conferencia_model->gravar_modal_avarias($recebimento, 'QUEBRADO', $qtd_quebrado);
        $this->Conferencia_model->gravar_modal_avarias($recebimento, 'EMBALAGEM VIOLADA', $qtd_violado);
        
        $data['msg_status']     = 'OK';
        $data['msg_texto']      = 'Quantidade de avarias alterada com sucesso!';
        // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
        // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
                
        // $this->load->view('conferencia_recebimento_externo', $data);
        $this->session->set_flashdata('flash-data', $data);
        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
    }

    // ATUALIZA MODAL COM QUANTIDADE DE VENCIDOS
    public function conferir3()
    {

        echo'1';
        $chave = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');
        $data['action_grid'] = base_url() . 'conferencia_recebimento_externo/conferir/' . $chave;

        $produto        = $this->input->post('fmodal_vencidos_codproduto');
        $recebimento    = $this->input->post('fmodal_vencidos_recebimento');
        $dt_vencimento  = implode('-', array_reverse(explode('/', $this->input->post('fmodal_vencidos_data'))));
        $qtd_vencidos   = $this->input->post('fmodal_vencidos_quantidade');

        $this->Conferencia_model->gravar_modal_vencimento($recebimento, $dt_vencimento, $qtd_vencidos);

        $data['msg_status']     = 'OK';
        $data['msg_texto']      = 'Quantidade de vencidos alterada com sucesso!';
        // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
        // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
                
        // $this->load->view('conferencia_recebimento_externo', $data);
        $this->session->set_flashdata('flash-data', $data);
        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh');

    }

    public function reiniciar()
    {
        $chave = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');

        if($chave)
        {
            $this->Conferencia_model->reiniciar_conferencia_chave($chave);
            $data['msg_status']     = 'OK';
            $data['msg_texto']      = 'Conferencia reiniciada com sucesso!';
            // $data['dados_volume']   = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
            // $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid_chave($chave);
    
            // $this->load->view('conferencia_recebimento_externo', $data);
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh');
        }
        else
        {
            redirect(base_url(). 'conferencia_externa', 'refresh'); 
        }

    }

    public function apagar_registro_grid()
    {
        $chave          = $this->uri->segment(3, 0);
        $id_recebimento = $this->uri->segment(4, 0);

        // INCLUIR CODIGO DE EXCLUSÃO AQUI
        $this->Conferencia_model->apagar_registro_conferencia($id_recebimento);
        redirect(base_url(). 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh'); 
    }

    public function conferir_uso()	{

        $data['msg_status']     = '';
        $data['msg_texto']      = '';

        $filial = $this->session->userdata('usu_codapo');
        $chave = $this->uri->segment(3, 0);

        $data['dados_volume']  = $this->Conferencia_model->obtem_dados_nota_uso($filial, $chave);

		// echo json_encode($data['dados_volume']);
        
        $data['grid_prod']  = $this->Conferencia_model->grid_chave($chave);

        // echo json_encode ($data['grid_prod']);

        $this->load->view('conferencia_recebimento_externo_uso', $data);

	}  

}
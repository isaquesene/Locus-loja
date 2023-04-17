<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Conferencia_volume_recebimento extends CI_Controller 
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
        $tela  = 46; //Cod. da tela - Tabela t_telas
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
        $volume = $this->uri->segment(3, 0);
        $data['action_grid'] = base_url() . 'conferencia_volume_recebimento/conferir/' . $volume;

        $data['volume_controlado'] = $this->Conferencia_model->verificar_volume_controlado($volume, $this->session->userdata('usu_codapo'));
        if($volume)
        {
            if(!$_POST)
            {
                $filial = $this->session->userdata('usu_codapo');
        
                $data['msg_status']     = '';
                $data['msg_texto']      = '';
                $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
        
                $this->load->view('conferencia_volume_recebimento', $data);
            }
            else
            {
                $num_nf = $this->input->post('form_nota_fiscal');
                $volume = $this->input->post('form_volume');
                $filial = $this->session->userdata('usu_codapo');
                
                if(!empty($this->input->post('form_ean')))
                {
                    $cod_ean = trim($this->input->post('form_ean'));
                    $naoLocalizadoNaNota = false;
                    // tenta localizar na nota, considerando o EAN
                    $dados_conf = $this->Conferencia_model->obtem_conferencia_ean($num_nf, $volume, $cod_ean);
                    if (!$dados_conf) {
                        // não está na nota, então busca como antes, sem considerar o EAN
                        $naoLocalizadoNaNota = true;
                        $dados_conf = $this->Conferencia_model->obtem_conferencia_ean($num_nf, $volume);
                    }
                    $dados_produtos = $this->Produto_model->obtem_produto_ean($cod_ean);

                    if($dados_produtos == FALSE)
                    {
                        $data['msg_status']     = 'ERRO';
                        $data['msg_texto']      = 'EAN do produto não encontrado: ' .  $cod_ean . '!';
                        $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                        $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
                
                        $this->load->view('conferencia_volume_recebimento', $data);
                    }
                    else
                    {
                        $cnf_nota_fiscal    = 0;
                        $cnf_volume         = '';
                        $cnf_produto_id     = 0;
                        $cnf_produto_dsc    = '';
                        $cnf_itn_id = NULL;

                        foreach($dados_conf AS $dados)
                        {
                            // se a solução do com e sem ean não funcionar
                            // fazer aqui um if para ver o qual o produto pode ser outra saída
                            $cnf_nota_fiscal    = $dados->cbn_num_nota;
                            $cnf_volume         = $dados->itn_cod_barras;
                            $cnf_produto_id     = $dados_produtos[0]->pro_id;
                            $cnf_produto_dsc    = $dados_produtos[0]->pro_descricao;
                            $cnf_cbn_id         = $dados->cbn_id;
                            $cnf_itn_id         = ($naoLocalizadoNaNota) ? null : $dados->itn_id;
                        }

                        if($this->Conferencia_model->inserir_conferencia_temporaria($cnf_nota_fiscal, $cnf_volume, $cnf_produto_id, $cnf_produto_dsc, $cnf_cbn_id, $cnf_itn_id) == TRUE)
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
                            /*$data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                            $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
                    
                            $this->load->view('conferencia_volume_recebimento', $data);
                            */
                            $this->session->set_flashdata('flash-data', $data);
                            header('location:'. base_url() . 'conferencia_volume_recebimento/conferir/' . $volume);
                        }
                        else
                        {
                            $data['msg_status']     = 'ERRO';
                            $data['msg_texto']      = 'Erro ao inserir EAN!';
                            $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                            $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
                    
                            $this->load->view('conferencia_volume_recebimento', $data);
                        }
                    }
                }
                elseif(!empty($this->input->post('form_produto')))
                {
                    $cod_produto = trim($this->input->post('form_produto'));
                    $naoLocalizadoNaNota = false;
                    // tenta localizar na nota, considerando o COD_PRODUTO (similar EAN)
                    $dados_conf = $this->Conferencia_model->obtem_conferencia_produto($num_nf, $volume, $cod_produto);
                    if (!$dados_conf) {
                        // não está na nota, então busca como antes, sem considerar o COD_PRODUTO
                        $naoLocalizadoNaNota = true;
                        $dados_conf = $this->Conferencia_model->obtem_conferencia_produto($num_nf, $volume);
                    }
                    $dados_produtos = $this->Produto_model->obtem_produto_codigo($cod_produto);

                    if($dados_produtos == FALSE)
                    {
                        $data['msg_status']     = 'ERRO';
                        $data['msg_texto']      = 'Codigo do produto não encontrado: ' .  $cod_produto . '!';
                        $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                        $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
                
                        $this->load->view('conferencia_volume_recebimento', $data);
                    }
                    else
                    {
                        $cnf_nota_fiscal    = 0;
                        $cnf_volume         = '';
                        $cnf_produto_id     = 0;
                        $cnf_produto_dsc    = '';
                        $cnf_itn_id = NULL;

                        foreach($dados_conf AS $dados)
                        {
                            $cnf_nota_fiscal    = $dados->cbn_num_nota;
                            $cnf_volume         = $dados->itn_cod_barras;
                            $cnf_produto_id     = $dados_produtos[0]->pro_id;
                            $cnf_produto_dsc    = $dados_produtos[0]->pro_descricao;
                            $cnf_cbn_id         = $dados->cbn_id;
                            $cnf_itn_id         = ($naoLocalizadoNaNota) ? null : $dados->itn_id;
                        }
                        
                        if($this->Conferencia_model->inserir_conferencia_temporaria($cnf_nota_fiscal, $cnf_volume, $cnf_produto_id, $cnf_produto_dsc, $cnf_cbn_id, $cnf_itn_id) == TRUE)
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
                            /*$data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                            $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
                    
                            $this->load->view('conferencia_volume_recebimento', $data);
                            */
                            $this->session->set_flashdata('flash-data', $data);
                            header('location:'. base_url() . 'conferencia_volume_recebimento/conferir/' . $volume);
                        }
                        else
                        {
                            $data['msg_status']     = 'ERRO';
                            $data['msg_texto']      = 'Erro ao inserir Cod. Produto!';
                            $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                            $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
                    
                            $this->load->view('conferencia_volume_recebimento', $data);
                        }
                    }
                }
                else
                {
                    $data['msg_status']     = 'ERRO';
                    $data['msg_texto']      = 'Informe um EAN ou Produto';
                    $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
                    $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);

                    $this->load->view('conferencia_volume_recebimento', $data);
                }
            }
        }
        else
        {
            redirect(base_url(). 'conferencia_volume', 'refresh'); 
        }
    }

    // ATUALIZA MODAL DE QUANTIDADE DE AVARIAS
    public function conferir2()
    {
        $volume = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');
        $data['action_grid'] = base_url() . 'conferencia_volume_recebimento/conferir/' . $volume;

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
        
        $data['msg_status']         = 'OK';
        $data['msg_texto']          = 'Quantidade de avarias alterada com sucesso!';

        $data['dados_volume']       = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
        $data['grid_produtos']      = $this->Conferencia_model->obtem_dados_grid($volume);
        $data['volume_controlado']  = $this->Conferencia_model->verificar_volume_controlado($volume, $this->session->userdata('usu_codapo'));

        $this->load->view('conferencia_volume_recebimento', $data);
    }

    // ATUALIZA MODAL COM QUANTIDADE DE VENCIDOS
    public function conferir3()
    {
        $volume = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');
        $data['action_grid'] = base_url() . 'conferencia_volume_recebimento/conferir/' . $volume;

        $produto        = $this->input->post('fmodal_vencidos_codproduto');
        $recebimento    = $this->input->post('fmodal_vencidos_recebimento');
        $dt_vencimento  = implode('-', array_reverse(explode('/', $this->input->post('fmodal_vencidos_data'))));
        $qtd_vencidos   = $this->input->post('fmodal_vencidos_quantidade');

        $this->Conferencia_model->gravar_modal_vencimento($recebimento, $dt_vencimento, $qtd_vencidos);

        $data['msg_status']         = 'OK';
        $data['msg_texto']          = 'Quantidade de vencidos alterada com sucesso!';

        $data['dados_volume']       = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
        $data['grid_produtos']      = $this->Conferencia_model->obtem_dados_grid($volume);
        $data['volume_controlado']  = $this->Conferencia_model->verificar_volume_controlado($volume, $this->session->userdata('usu_codapo'));

        $this->load->view('conferencia_volume_recebimento', $data);

    }

    public function reiniciar()
    {
        $volume = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');

        if($volume)
        {
            $this->Conferencia_model->reiniciar_conferencia_volume($volume);
            $data['msg_status']     = 'OK';
            $data['msg_texto']      = 'Conferencia reiniciada com sucesso!';
            $data['dados_volume']   = $this->Conferencia_model->obtem_dados_volume($filial, $volume);
            $data['grid_produtos']  = $this->Conferencia_model->obtem_dados_grid($volume);
    
            $this->load->view('conferencia_volume_recebimento', $data);
        }
        else
        {
            redirect(base_url(). 'conferencia_volume', 'refresh'); 
        }

    }

    public function apagar_registro_grid()
    {
        $volume         = $this->uri->segment(3, 0);
        $id_recebimento = $this->uri->segment(4, 0);

        // INCLUIR CODIGO DE EXCLUSÃO AQUI
        $this->Conferencia_model->apagar_registro_conferencia($id_recebimento);

        redirect(base_url(). 'conferencia_volume_recebimento/conferir/'. $volume, 'refresh'); 
    }

}
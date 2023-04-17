<?php defined('BASEPATH') or exit('No direct script access allowed');


class Chamados_adicionar extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Chamados_model');
        $this->load->model('Conferencia_model');
        $this->load->model('Nf_model');
        $this->load->model('Telas_model');

        // Carregar HELPERS
        $this->load->helper('envioEmail');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 39; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        $this->_init();
    }

    private function _init()
    {
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
        if (!$_POST) {
            $data['msg_status']   = '';
            $data['msg_texto']    = '';

            if(isset($_GET['id'])){
                $data['detalhes_chamado'] = $this->Chamados_model->obtem_chamado_id($_GET['id']);
                $data['lista_respostas'] = $this->Chamados_model->obtem_todos_resp_chamados();
                $data['isExterno'] = $this->Chamados_model->isChamadoNotaExterna($_GET['id']);
                $data['anexo_chamado'] = $this->Chamados_model->obtem_arquivo_chamado($_GET['id']);
                $data['comentarios'] = $this->Chamados_model->obtem_comentarios_chamado($_GET['id']);
            }else{
                $data['lista_mensagens'] = $this->Chamados_model->obtem_todos_chamados_ativos();
                $data['isExterno'] = false;
            }

            $this->load->view('chamados_adicionar', $data);
        } else {
            $this->adicionar();
        }
    }

    public function ajaxRequestPost()
    {
        $d['produto'] = $this->Nf_model->obtem_produto_volume_nf(
            $this->input->post('ajax_nf'),
            $this->input->post('ajax_volume'),
            $this->input->post('ajax_ean')
        );

        if ($d['produto'] != false) {
            foreach ($d['produto'] as $p) :
                $valor1 = $p->pro_cod_pro_cli;
                $valor2 = $p->pro_descricao;
                $valor3 = $p->pro_id;
                $valor4 = '000';
                $valor5 = '000'; //$this->input->post('ajax_tipo');
                $valor6 = '000'; //$this->input->post('ajax_obs');

                echo $valor1 . '|' . $valor2 . '|' . $valor3 . '|' . $valor4 . '|' . $valor5 . '|' . $valor6 . '|';
            endforeach;
        } else {
            echo "false";
        }
        die;
    }

    private function adicionar()
    {
        $itensSelecionados = false;
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'form_id_prod') !== false) {
                $itensSelecionados = true;
            }
        }

        if ($itensSelecionados == true) {
            $arrayAvaria = array();
            $arrayVencido = array();
            $arraySobra = array();
            $arrayFalta = array();

            if ($this->session->userdata('usu_codapo') == '') {
                $solicitante = 1;
            } else {
                $solicitante = $this->session->userdata('usu_codapo');
            }

            $item = '';
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'form_id_prod') !== false) {
                    $item = $this->input->post('form_id_prod');
                }
                if (strpos($key, 'grid_item_qtd_') !== false) {
                    $item = $item . "|" . $value;
                }
                if (strpos($key, 'grid_item_tipo_') !== false) {
                    $item = $item . "|" . $value;
                }
                if (strpos($key, 'grid_item_obs_') !== false) {
                    $item = $item . "|" . $value;

                    if (explode('|', $item)[2] == 'Avaria') {
                        array_push($arrayAvaria, $item);
                    } else if (explode('|', $item)[2] == 'Vencido') {
                        array_push($arrayVencido, $item);
                    } else if (explode('|', $item)[2] == 'Sobra') {
                        array_push($arraySobra, $item);
                    } else if (explode('|', $item)[2] == 'Falta') {
                        array_push($arrayFalta, $item);
                    }

                    $item = '';
                }
            }

            //INSERT AVARIAS
            if (count($arrayAvaria) > 0) {
                $dt = date("Y-m-d H:m:i.v");
                $dados = array(
                    'cha_nota_fiscal'    => $this->input->post('form_nota_fiscal'),
                    'cha_data_abertura'  => str_replace(" ", "T", $dt),
                    'cha_solicitante'    => $solicitante,
                    'cha_id_volume'         => $this->input->post('form_volume'),
                    'cha_status'         => 'PENDENTE',
                    'cha_tipo'           => 'AVARIA',
                    'cha_id_mensagem'    => $this->input->post('form_status')
                );

                $r = $this->Chamados_model->adicionar_chamado($dados);
                if ($r != false) {
                    foreach ($arrayAvaria as $p) {
                        $dadosItem = array(
                            'chi_id_chamado' => $r,
                            'chi_id_pro' => explode("|", $p)[0],
                            'chi_qtd' => explode("|", $p)[1],
                            'chi_obs' => explode("|", $p)[3]
                        );

                        $this->Chamados_model->adicionar_item_chamado($dadosItem);
                    }
                }
            }

            if (count($arrayVencido) > 0) {
                $dt = date("Y-m-d H:m:i.v");
                $dados = array(
                    'cha_nota_fiscal'    => $this->input->post('form_nota_fiscal'),
                    'cha_data_abertura'  => str_replace(" ", "T", $dt),
                    'cha_solicitante'    => $solicitante,
                    'cha_id_volume'         => $this->input->post('form_volume'),
                    'cha_status'         => 'PENDENTE',
                    'cha_tipo'           => 'VENCIDO',
                    'cha_id_mensagem'    => $this->input->post('form_status')
                );

                $r = $this->Chamados_model->adicionar_chamado($dados);
                if ($r != false) {
                    foreach ($arrayVencido as $p) {
                        $dadosItem = array(
                            'chi_id_chamado' => $r,
                            'chi_id_pro' => explode("|", $p)[0],
                            'chi_qtd' => explode("|", $p)[1],
                            'chi_obs' => explode("|", $p)[3]
                        );

                        $this->Chamados_model->adicionar_item_chamado($dadosItem);
                    }
                }
            }

            if (count($arrayFalta) > 0) {
                $dt = date("Y-m-d H:m:i.v");
                $dados = array(
                    'cha_nota_fiscal'    => $this->input->post('form_nota_fiscal'),
                    'cha_data_abertura'  => str_replace(" ", "T", $dt),
                    'cha_solicitante'    => $solicitante,
                    'cha_id_volume'         => $this->input->post('form_volume'),
                    'cha_status'         => 'PENDENTE',
                    'cha_tipo'           => 'FALTA',
                    'cha_id_mensagem'    => $this->input->post('form_status')
                );

                $r = $this->Chamados_model->adicionar_chamado($dados);
                if ($r != false) {
                    foreach ($arrayFalta as $p) {
                        $dadosItem = array(
                            'chi_id_chamado' => $r,
                            'chi_id_pro' => explode("|", $p)[0],
                            'chi_qtd' => explode("|", $p)[1],
                            'chi_obs' => explode("|", $p)[3]
                        );

                        $this->Chamados_model->adicionar_item_chamado($dadosItem);
                    }
                }
            }

            if (count($arraySobra) > 0) {
                $dt = date("Y-m-d H:m:i.v");
                $dados = array(
                    'cha_nota_fiscal'    => $this->input->post('form_nota_fiscal'),
                    'cha_data_abertura'  => str_replace(" ", "T", $dt),
                    'cha_solicitante'    => $solicitante,
                    'cha_id_volume'         => $this->input->post('form_volume'),
                    'cha_status'         => 'PENDENTE',
                    'cha_tipo'           => 'SOBRA',
                    'cha_id_mensagem'    => $this->input->post('form_status')
                );

                $r = $this->Chamados_model->adicionar_chamado($dados);
                if ($r != false) {
                    foreach ($arraySobra as $p) {
                        $dadosItem = array(
                            'chi_id_chamado' => $r,
                            'chi_id_pro' => explode("|", $p)[0],
                            'chi_qtd' => explode("|", $p)[1],
                            'chi_obs' => explode("|", $p)[3]
                        );

                        $this->Chamados_model->adicionar_item_chamado($dadosItem);
                    }
                }
            }

            $data['msg_status']  = 'OK';
            $data['msg_texto']   = 'Chamado Cadastrado com Sucesso.';
            
            $_POST = array();
            $this->load->view('chamados_gerenciar', $data);
        } else {
            $data['msg_status']  = 'ERRO';
            $data['msg_texto']   = 'Selecione pelo menos 1 item da nota fiscal.';
            
            $data['lista_mensagens'] = $this->Chamados_model->obtem_todos_chamados_ativos();
            $this->load->view('chamados_adicionar', $data);
        }
    }

    public function aprovar()
    {
        $idAtendente = $this->session->userdata('usu_id');
        $idChamado = $this->uri->segment(3, 0);
        $idResposta = $this->uri->segment(4, 0);
        $dataColeta = $this->uri->segment(5, 0);
        $qtdAprovadaArr = (isset($_POST['qtdAprovada'])) ? $_POST['qtdAprovada'] : [];

        // Descobre se é recebimento interno ou externo
        $isExterno = $this->Chamados_model->isChamadoNotaExterna($idChamado);
        $isInterno = !$isExterno;

        // die('xx:'.$isExterno);

        if ($isExterno) {
            if (!empty($dataColeta) && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{4})/', $dataColeta, $regs)) {
                $dataColeta = sprintf("%d-%d-%d", $regs[3], $regs[2], $regs[1]);
            }
        } else {
            $dataColeta = '';
        }

        $chamados = $this->Chamados_model->obtem_chamado_id($idChamado);

        if($this->Chamados_model->aprovarChamado($idChamado, $idAtendente, $idResposta, $dataColeta)){

            $idLojaSolicitante = "";

            foreach($chamados as $c) :

                $idLojaSolicitante = $c->cha_solicitante;
                
                if ($isExterno) {
                    $nota = $this->Nf_model->obtem_dados_nota_fiscal_externo($c->cha_nota_fiscal);
                } else {
                    $nota = $this->Nf_model->obtem_dados_nota_fiscal($c->cha_nota_fiscal, $c->cha_id_volume);
                }
                
                $dados = array(
                    'ped_id_filial'    => $nota[0]->cbn_id_fil,
                    'ped_data'  => date('Y-m-d H:i:s', strtotime('NOW')),
                    'ped_id_cabecalho_nota'    => $nota[0]->cbn_id,
                    'ped_controle_integracao'    => 'L',
                    'ped_id_pro'                => $c->chi_id_pro,
                    'ped_qtd'                   => $c->chi_qtd,
                    'ped_tipo_recebimento'      => ($isInterno) ? 1 : 2,
                    'ped_id_chamado'            => $c->chi_id,
                );

                // if ($isInterno) {
                   // $this->Chamados_model->adicionarPedidoNota($dados);
                // }

                if ($c->cha_tipo == 'VENCIDO' || $c->cha_tipo == 'AVARIA') {
                    
                    // caso seja receb. externo
                    if ($isExterno) {

                        $dt = date("Y-m-d H:m:i.v");

                        // altera o status da NF
                        $this->Chamados_model->alterar_status_nota_externa_pendente_de_recolha($dados['ped_id_cabecalho_nota']);

                        // quantidade aprovada (se não existir, considera total)
                        $qtdAprovado = (!isset($qtdAprovadaArr[$c->chi_id])) ? $c->chi_qtd : $qtdAprovadaArr[$c->chi_id];

                        // salva qtd aprovada do item
                        $this->Chamados_model->alterar_qtd_aprovada($c->chi_id, $qtdAprovado);//$qtdAprovadaArr[$c->chi_id]);

                        // movimenta o estoque para os negados (diferenca)
                        $qtdNegado = $c->chi_qtd - $qtdAprovado; //$qtdAprovadaArr[$c->chi_id];
                        if ($qtdNegado > 1) {
                            
                            $estoque_destino = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '01';
                            $estoque_origem = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '02';

                            $arraymovimento = array(
                                'mov_data'                  => str_replace(" ", "T", $dt),
                                'mov_id_filial'             => $nota[0]->cbn_id_fil,
                                'mov_id_produto'            => $c->chi_id_pro,
                                'mov_qtd_movimentada'       => $qtdNegado, //$c->chi_qtd,
                                'mov_estoque_destino'       => $estoque_destino,
                                'mov_controle_integracao'   => 'L',
                                'mov_chave_nfe'             => $nota[0]->cbn_chave_nota,
                                'mov_estoque_origem'        => $estoque_origem
                            );

                            $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);

                        }
                        //echo $qtdNegado . '-' . $c->chi_qtd . '-' . $qtdAprovadaArr[$c->chi_id] . '-' . $c->chi_id; die;
                    }

                    $this->Chamados_model->adicionarPedidoNota($dados);
                    
                } elseif ($c->cha_tipo == 'FALTA') {

                    $dt = date("Y-m-d H:m:i.v");
                    
                    if ($isExterno) {
                        $estoque_destino = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '03';
                    } else {
                        $estoque_destino = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '02';
                    }
                    
                    $arraymovimento = array(
                        'mov_data'                  => str_replace(" ", "T", $dt),
                        'mov_id_filial'             => $nota[0]->cbn_id_fil,
                        'mov_id_produto'            => $c->chi_id_pro,
                        'mov_qtd_movimentada'       => $c->chi_qtd,
                        'mov_estoque_destino'       => $estoque_destino,
                        'mov_controle_integracao'   => 'L',
                        'mov_chave_nfe'             => $nota[0]->cbn_chave_nota,
                    );

                    $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                }
            endforeach;

            $data['msg_status']  = 'OK';
            $data['msg_texto']   = 'Chamado Aprovado com Sucesso.';
            
            // Envia e-mail chamado aprovado
            msgEmailChamadoAprovadoLoja("loja".$idLojaSolicitante."@farmaconde.com.br");
            //msgEmailChamadoAprovadoLoja("rodrigo.pereira@artechs.com.br");

            // $_POST = array();
            // $this->load->view('chamados_gerenciar', $data);
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'chamados_gerenciar', 'refresh');
        }else{
            $data['msg_status']  = 'ERRO';
            $data['msg_texto']   = 'Ocorreu um erro ao aprovar chamado.';
            
            // $_POST = array();
            // $this->load->view('chamados_gerenciar', $data);
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'chamados_gerenciar', 'refresh');
        }
    }
 
    public function negar()
    {
        $idAtendente = $this->session->userdata('usu_id');
        $idChamado = $this->uri->segment(3, 0);
        $idResposta = $this->uri->segment(4, 0);

        // Descobe se é recebimento interno ou externo
        $isExterno = $this->Chamados_model->isChamadoNotaExterna($idChamado);
        $isInterno = !$isExterno;

        if($this->Chamados_model->negarChamado($idChamado, $idAtendente, $idResposta)){
            $chamados = $this->Chamados_model->obtem_chamado_id($idChamado);
            
            if ($isExterno) {
                $nota = $this->Nf_model->obtem_dados_nota_fiscal_externo($chamados[0]->cha_nota_fiscal, $chamados[0]->cha_id_volume);
            } else {
                $nota = $this->Nf_model->obtem_dados_nota_fiscal($chamados[0]->cha_nota_fiscal, $chamados[0]->cha_id_volume);
            }

            $idLojaSolicitante = '';

            foreach($chamados as $c) :

                $idLojaSolicitante = $c->cha_solicitante;

                if($c->cha_tipo == 'VENCIDO' || $c->cha_tipo == 'AVARIA'){
                    
                    $estoque_destino = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '01';
                    
                    if ($isExterno) {
                        $estoque_origem = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '03';
                    } else {
                        $estoque_origem = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '02';
                    }

                    $arraymovimento = array(
                        'mov_data'                  => date('Y-m-d H:i:s', strtotime('NOW')),
                        'mov_id_filial'             => $nota[0]->cbn_id_fil,
                        'mov_id_produto'            => $c->chi_id_pro,
                        'mov_qtd_movimentada'       => $c->chi_qtd,
                        'mov_estoque_destino'       => $estoque_destino,
                        'mov_controle_integracao'   => 'L',
                        'mov_chave_nfe'             => $nota[0]->cbn_chave_nota,
                        'mov_estoque_origem'        => $estoque_origem
                    );

                    $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);

                }else if($c->cha_tipo == 'FALTA'){
                    $dt = date("Y-m-d H:m:i.v");
                    $estoque_destino = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '01';
                    $estoque_origem = '1' . (string) str_pad($nota[0]->cbn_id_fil, 4, '0', STR_PAD_LEFT) . '00';
                    $arraymovimento = array(
                        'mov_data'                  => str_replace(" ", "T", $dt),
                        'mov_id_filial'             => $nota[0]->cbn_id_fil,
                        'mov_id_produto'            => $c->chi_id_pro,
                        'mov_qtd_movimentada'       => $c->chi_qtd,
                        'mov_estoque_destino'       => $estoque_destino,
                        'mov_controle_integracao'   => 'L',
                        'mov_chave_nfe'             => $nota[0]->cbn_chave_nota,
                        'mov_estoque_origem'        => $estoque_origem
                    );

                    $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                }
            endforeach;

            $data['msg_status']  = 'OK';
            $data['msg_texto']   = 'Chamado Negado com Sucesso.';
            
            msgEmailChamadoNegadoLoja("loja".$idLojaSolicitante."@farmaconde.com.br");

            // $_POST = array();
            // $this->load->view('chamados_gerenciar', $data);
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'chamados_gerenciar', 'refresh');
        }else{
            $data['msg_status']  = 'ERRO';
            $data['msg_texto']   = 'Ocorreu um erro ao negar chamado.';
            
            // $_POST = array();
            // $this->load->view('chamados_gerenciar', $data);
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'chamados_gerenciar', 'refresh');
        }
    }
    public function adicionar_comentario(){
        $ch_coment_id_chamado = $this->input->post('ch_coment_id_chamado');
        $ch_coment_text = $this->input->post('ch_coment_text');
        $ch_coment_user_reader = json_encode([$this->session->userdata('usu_id')]);
        $dados = array(
            'ch_coment_id_chamado' => $ch_coment_id_chamado,
            'ch_coment_id_user_send' => $this->session->userdata('usu_id'),
            'ch_coment_text' => $ch_coment_text,
            'ch_coment_user_reader' => $ch_coment_user_reader,
            'ch_coment_data' => date('Y/m/d H:i:s', strtotime('NOW')),
        );
        if($this->Chamados_model->cadastrar_comentario($dados)){
            $data['msg_status']     = 'OK';
            $data['msg_texto']      =  'Mensagem cadastrada com sucesso';
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'chamados_adicionar?id=' . $ch_coment_id_chamado, 'refresh');
        }else{
            $data['msg_status']     = 'ERRO';
            $data['msg_texto']      =  'Erro ao salvar mensagem no banco de dados.';
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'chamados_adicionar?id=' . $cha_id, 'refresh');
        }
    }
    
    public function adicionar_arquivo(){
        $arquivo = $_FILES['arquivo'];
        $observacao = $this->input->post('observacao');
        $cha_id = $this->input->post('cha_id');
        $token = uniqid("");
        $path_upload = "./uploads/";
        $path_upload_chamados = "./uploads/chamados/";

        if(!is_dir($path_upload)){
            mkdir($path_upload);
            chmod($path_upload, 0777);
        }

        if(!is_dir($path_upload_chamados)){
            mkdir($path_upload_chamados);
            chmod($path_upload_chamados, 0777);
        }
        $configuracao = array(
            'upload_path'   => $path_upload_chamados,
            'allowed_types' => 'jpg|pdf',
            'file_name'     => $token.'_'.preg_replace('/[^A-Za-z0-9-_]/','-',preg_replace('/^(.*)\..*$/', '$1', $arquivo['name'])).preg_replace('/^.*(\..*)$/', '$1', $arquivo['name']),
            'max_size'      => '1000'
                 );      
        $dados = array(
            'ch_ane_usu_id'   => $this->session->userdata('usu_id'),
            'ch_ane_id_chamado' => $cha_id,
            'ch_ane_arquivo'     => $configuracao['file_name'],
            'ch_ane_obs'      => $observacao,
            'ch_ane_data' => date('Y/m/d H:i:s', strtotime('NOW')),
                );   

                    $this->load->library('upload');
                    $this->upload->initialize($configuracao);
                    if ($this->upload->do_upload('arquivo')){
                            if($this->Chamados_model->adicionar_arquivo_chamado($dados)){
                                $data['msg_status']     = 'OK';
                                $data['msg_texto']      =  'Arquivo cadastrado com sucesso';
                                $this->session->set_flashdata('flash-data', $data);
                                redirect(base_url(). 'chamados_adicionar?id=' . $cha_id, 'refresh');
                            }else{
                                $data['msg_status']     = 'ERRO';
                                $data['msg_texto']      =  'Erro ao salvar arquivo no banco de dados.';
                                $this->session->set_flashdata('flash-data', $data);
                                redirect(base_url(). 'chamados_adicionar?id=' . $cha_id, 'refresh');
                            }
                    }else{
                        $data['msg_status']     = 'ERRO';
                        $data['msg_texto']      =  $this->upload->display_errors();
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'chamados_adicionar?id=' . $cha_id, 'refresh');
                    }
    }

    public function deletar_anexo(){
            $this->load->helper("file");
            $ch_ane_id = $this->uri->segment(3,0);

            $dados['arquivo'] = $this->Chamados_model->obtem_arquivo_chamado_por_id($ch_ane_id); 
            
            foreach($dados['arquivo'] as $d){
                $ch_ane_arquivo = $d->ch_ane_arquivo;
		        $ch_ane_id_chamado = $d->ch_ane_id_chamado;
            }

             $caminhoArquivo = './uploads/chamados/'.$ch_ane_arquivo;

            if(in_array($this->session->userdata('usu_perfil'),[1,2])){
                if(unlink($caminhoArquivo)){
                    if($this->Chamados_model->remover_arquivo_chamado($ch_ane_id)){
                        $data['msg_status']   = 'OK';
                        $data['msg_texto']    = 'Arquivo apagado com sucesso.'; 
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'chamados_adicionar?id='.$ch_ane_id_chamado, 'refresh');
                    }else{
                        $data['msg_status']   = 'ERRO';
                        $data['msg_texto']    = 'Erro ao apagar arquivo no banco de dados.'; 
                        $this->session->set_flashdata('flash-data', $data);
                        redirect(base_url(). 'chamados_adicionar?id='.$ch_ane_id_chamado, 'refresh');
                    }
                }else{
                    $data['msg_status']   = 'ERRO';
                    $data['msg_texto']    = 'Erro ao apagar do diretorio.'; 
                    $this->session->set_flashdata('flash-data', $data);
                    redirect(base_url(). 'chamados_adicionar?id='.$ch_ane_id_chamado, 'refresh');
                }
        }
    }

    public function download(){

        $this->load->helper('download');
        $ch_ane_id = $_GET['id'];

        $dados['arquivo'] = $this->Chamados_model->obtem_arquivo_chamado_por_id($ch_ane_id); 
        
        foreach($dados['arquivo'] as $d){
            $ch_ane_arquivo = $d->ch_ane_arquivo;
            $ch_ane_id_chamado = $d->ch_ane_id_chamado;
        }

         $caminhoArquivo = './uploads/chamados/'.$ch_ane_arquivo;
        force_download($caminhoArquivo,null);
    }
}

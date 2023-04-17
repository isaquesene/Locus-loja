<?php defined('BASEPATH') or exit('No direct script access allowed');

class Conferencia_externo_validar extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Conferencia_model');
        $this->load->model('Chamados_model');
        $this->load->model('Telas_model');

        // Carregar HELPERS
        $this->load->helper('envioEmail');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if (!$this->session->userdata('loggedin')) {
            redirect(base_url() . 'login', 'refresh');
        }

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 45; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if ($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE) {
            redirect(base_url() . 'acesso_negado', 'refresh');
        }

        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Form_validation');
        $this->load->library('Email');

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
        $chave = $this->uri->segment(2, 0);
        $filial = $this->session->userdata('usu_codapo');

        if (!$chave) {
            redirect(base_url() . 'conferencia_externa', 'refresh');
        } else {
            $controlados = $this->Conferencia_model->verificar_nota_controlada($chave, $this->session->userdata('usu_codapo'));
            if ($controlados[0]->cont > 0) {
                $dados = $this->Conferencia_model->validacao_nota_controlada($chave);
                //if($dados){
                $possuiControladosNulos = false;
                if ($dados) {
                    foreach ($dados as $d) {
                        if ($d->reccon_lote == '' || $d->reccon_data_validade == '' || $d->reccon_data_fabricacao == '') {
                            $possuiControladosNulos = true;
                        }
                    }
                }

                if ($possuiControladosNulos) {
                    // echo "<script>alert('Essa conferência possui medicamentos controlados sem lote e validade, preencha por favor!')</script>";
                    $msg = 'Essa conferência possui medicamentos controlados sem lote e validade, preencha por favor!';
                    $this->session->set_flashdata('flash-alert', ['Erro', $msg, 'error']);
                    redirect(base_url() . 'conferencia_recebimento_externo/conferir/' . $chave, 'refresh');
                } else {
                    $data['msg_status']     = '';
                    $data['msg_texto']      = '';
                    $data['numero_nota']  = $chave;

                    $data['dados_nota']         = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                    $data['dados_avarias']      = $this->Conferencia_model->obtem_avarias_nota($chave);
                    $data['dados_vencidos']     = $this->Conferencia_model->obtem_vencidos_nota($chave);
                    $data['dados_sobra_falta']  = $this->Conferencia_model->obtem_sobra_falta_nota($chave);

                    $controlado  = $this->Conferencia_model->verificar_nota_controlada($chave, $this->session->userdata('usu_codapo'));
                    if ($controlado[0]->cont > 0) {
                        $data['dados_controlados']  = $this->Conferencia_model->validacao_nota_controlada($chave);
                    }

                    $this->load->view('conferencia_externo_validar', $data);
                }
                //}else{

                //}
            } else {
                $data['msg_status']     = '';
                $data['msg_texto']      = '';
                $data['numero_nota']  = $chave;

                $data['dados_nota']         = $this->Conferencia_model->obtem_dados_nota($filial, $chave);
                $data['dados_avarias']      = $this->Conferencia_model->obtem_avarias_nota($chave);
                $data['dados_vencidos']     = $this->Conferencia_model->obtem_vencidos_nota($chave);
                $data['dados_sobra_falta']  = $this->Conferencia_model->obtem_sobra_falta_nota($chave);

                $controlado  = $this->Conferencia_model->verificar_nota_controlada($chave, $this->session->userdata('usu_codapo'));
                if ($controlado[0]->cont > 0) {
                    $data['dados_controlados']  = $this->Conferencia_model->validacao_nota_controlada($chave);
                }

                $this->load->view('conferencia_externo_validar', $data);
            }
        }
    }

    public function finalizar()
    {
        $chave = $this->uri->segment(3, 0);
        $filial = $this->session->userdata('usu_codapo');

        // Gravar volume conferido
        $this->gravar_dados_recebimento($chave, $filial);

        // Gravar controlados SEMC
        $this->gravar_dados_semc($chave);

        // Mudar status na tabela t_cabecalho_notas
        //$this->Conferencia_model->mudar_status_nota($volume);

        // Limpar dados de avarias da tabela temporaria, para o volume informado
        //// $this->Conferencia_model->reiniciar_conferencia_volume($volume);

        // Verifica novamente se mantém divergência após
        // as alterações terem sido salvas durante a finalização
        $controlado  = $this->Conferencia_model->verificar_nota_controlada($chave, $this->session->userdata('usu_codapo'));
        if ($controlado[0]->cont > 0) {
            $dados_controlados_divergentes  = $this->Conferencia_model->validacao_nota_controlada($chave);
            if ( $dados_controlados_divergentes !== false ) {
                $dados_nota = $this->Conferencia_model->obtem_dados_nota($filial, $chave, false); // o false é para ignorar o status da NF
                $dados_nota = (isset($dados_nota[0])) ? $dados_nota[0] : [];
                $destinatario = 'loja'.$filial.'@farmaconde.com.br';
                $this->_enviaEmailDivergencias($destinatario, $dados_nota, $dados_controlados_divergentes);
            }
        }

        // redireciona para não ter problema com F5
        // $this->session->set_flashdata('flash-data', $data);
        redirect(base_url(). 'conferencia_externa/', 'refresh');
    }

    private function _enviaEmailDivergencias($destinatario, $dados_nota, $dados_divergencias)
    {

        $smtp_host   = 'smtp.farmaconde.com.br';
        $smtp_name   = 'Divergências - Sistema Locus Online';
        $smtp_mail   = 'smtp@farmaconde.com.br';
        $smtp_user   = 'smtp@farmaconde.com.br';
        $smtp_pswd   = 'smtp102030';
        $smtp_port   = '587';

        $return_path = 'smtp@farmaconde.com.br';
        $subject     = 'Divergências - Sistema Locus Online';

        $config_mail['protocol']     = 'smtp';
        $config_mail['charset']      = 'utf-8';
        $config_mail['wordwrap']     = TRUE;
        $config_mail['smtp_host']    = $smtp_host;
        $config_mail['smtp_user']    = $smtp_user;
        $config_mail['smtp_pass']    = $smtp_pswd;
        $config_mail['smtp_port']    = $smtp_port;
        $config_mail['smtp_timeout'] = 60;
        $config_mail['mailtype']     = 'html';
        $config_mail['return-path']  = $return_path;
        $config_mail['crlf']         = '\r\n'; 
        $config_mail['newline']      = '\r\n';	
        
        $this->email->initialize($config_mail);


        $itens = '';
        foreach ($dados_divergencias as $item) {
            $itens.= '<p>
                Código: '.@$item->pro_cod_pro_cli.'<br>
                Descrição: '.@$item->pro_descricao.'<br>
                Lote: '.@$item->reccon_lote.'<br>
                Validade: '. @date('d/m/Y', strtotime(@$item->reccon_data_validade)).'<br>
                Fabricação: '. @date('d/m/Y', strtotime(@$item->reccon_data_fabricacao)) .'<br>
            </p>';
        }
        // itn_lote, itn_validade e itn_fabricacao se quiser incluir comparativo posteriormente

        $MsgTxt = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
            <style type="text/css">
                a:link, a:active, a:visited, a:hover{color: #FFFFFF; font-weight: bold; text-decoration: none;}
                p{color:#373E4A; font-size:12px;}

                div.clear{clear: both;}
            </style>

            </head>

            <body style="font-family: verdana, arial, sans-serif;">
                <div style="width: 600px; margin: 0 auto; padding:0; background-color:transparent;">
                    <div style="width: 600px; height: 100px; margin: 0; padding: 0; background-color:#C21120; border: 1px solid #C21120;">
                        <p style="color:#FFFFFF; font-size:32px; font-weight:bold; padding: 0 20px;">Locus Online</p>			
                    </div>
                    <div style="width: 560px; margin: 0; padding: 20px; background-color:#FFFFFF; border: 0 solid #C21120; border-width: 0 1px;">
                        <p style="color:#373E4A; font-size:14px; font-weight:bold;">Prezado(a) Farmacêutico,</p>
                        <br />
                        
                        <p>Texto a ser criado.</p>

                        <hr>

                        <p>
                            Data de Emissão: '. @date('d/m/Y', strtotime(@$dados_nota->cbn_data_emissao)) .'<br>
                            Número da Nota: '.@$dados_nota->cbn_num_nota .'<br>
                            Emitente: '.@$dados_nota->cbn_nome_emitente .'<br>
                            Chave: '.@$dados_nota->cbn_chave_nota .'<br>
                        </p>

                        <hr>

                        <p>Divergências:</p>
                        
                        '.$itens.'

                        <hr>
                        
                        <p>Atenciosamente,</p>
                        <p>Equipe de TI</p>
                        
                    </div>
                    <div style="width: 580px; height: 20px; margin: 0; padding: 0; background-color:#C21120;padding: 10px; border: 1px solid #C21120;">
                        <a href="">Locus Online</a>
                    </div>
                    <div class="clear"></div>
                </div>
            </body>
            </html>
            ';

        $this->email->from($smtp_mail, $smtp_name);            // Email de envio - Site                
        $this->email->to($destinatario);                       // Email de Recebimento - Usuário                
        $this->email->reply_to($return_path, $return_path);    // Email de Retorno                
        //$this->email->cc('');                                // Cópia Carbono
        //$this->email->bcc('');                               // Cópia Carbono oculta
        $this->email->subject($subject);                       // Assunto do Email
        $this->email->message($MsgTxt);                        // Mensagem do Email

        if (!$this->email->send())
        {
            $data['msg_status'] = 'ERRO';
            $data['msg_texto']  = 'Erro ao enviar senha por email.';   
        }
        else
        {
            $data['msg_status'] = 'OK';
            $data['msg_texto']  = 'E-mail de divergência enviado com sucesso!';   
        } 
    }



    private function gravar_dados_semc($chave)
    {
        // Obtem dados de controlados, da tabela temporaria
        $dados_controlados = $this->Conferencia_model->obtem_controlados_nota($chave);
        if ($dados_controlados) {
            foreach ($dados_controlados as $d_controlados) :

                $rec_cbn_id         = $d_controlados->rec_cbn_id;
                $rec_itn_id         = $d_controlados->rec_itn_id;
                $rec_nota_fiscal    = $d_controlados->rec_nota_fiscal;
                $cbn_data_emissao   = $d_controlados->cbn_data_emissao;
                $cbn_cnpj_emitente  = $d_controlados->cbn_cnpj_emitente;
                $pro_cod_pro_cli    = $d_controlados->pro_cod_pro_cli;
                $reccon_quantidade  = $d_controlados->reccon_quantidade;
                $rec_produto_nome   = $d_controlados->rec_produto_nome;
                $reccon_lote        = $d_controlados->reccon_lote;
                $reccon_dt_validade = $d_controlados->reccon_data_validade;

                // se não tiver o itn_id, é produto fora da nota
                // e não será salvo no SEMC
                if (is_null($rec_itn_id)) {
                    continue;
                }

                $arraydados = array(
                    'semc_cbn_id'               => $rec_cbn_id,
                    'semc_itn_id'               => $rec_itn_id,
                    'semc_flag'                 => 'E',
                    'semc_data_movimentacao'    => date('Y-m-d', strtotime('NOW')),
                    'semc_num_nf'               => $rec_nota_fiscal,
                    'semc_data_nf'              => $cbn_data_emissao,
                    'semc_num_cnpj'             => $cbn_cnpj_emitente,
                    'semc_cod_pro_cli'          => $pro_cod_pro_cli,
                    'semc_qtd_produto'          => $reccon_quantidade,
                    'semc_desc_produto'         => $rec_produto_nome,
                    'semc_num_lote'             => $reccon_lote,
                    'semc_validade_lote'        => $reccon_dt_validade,
                    'semc_subcodigo'            => '1',
                    'semc_local_codigo'         => '1',
                    'semc_tipo'                 => 'PJ',
                );

                // Grava dados de controlados, na tabela t_recebimentos_semc
                $this->Conferencia_model->gravar_controlado_semc_produto($arraydados);

            endforeach;
        }
    }

    private function gravar_dados_recebimento($chave, $filial)
    {

        $teveChamadoAberto = false;

        // Dados do recebimento
        $dados_recebimentos = $this->Conferencia_model->obtem_recebimentos_nota($chave);
        if ($dados_recebimentos) {
            foreach ($dados_recebimentos as $d_recebimentos) :
                $arraydados = array(
                    'rcb_id_recebimento'    => $d_recebimentos->rec_id,
                    'rcb_nota_fiscal'       => $d_recebimentos->rec_nota_fiscal,
                    'rcb_volume'            => '',
                    'rcb_data_recebimento'  => $d_recebimentos->rec_data_recebimento,
                    'rcb_produto'           => $d_recebimentos->rec_produto,
                    'rcb_produto_nome'      => $d_recebimentos->rec_produto_nome,
                    'rcb_quantidade'        => $d_recebimentos->quantidade_recebida,
                    'rcb_cbn_id'            => $d_recebimentos->rec_cbn_id,
                    'rcb_itn_id'            => $d_recebimentos->rec_itn_id,
                    'rcb_chave_nota'        => $d_recebimentos->rec_chave_nota
                );

                // Grava dados de recebimentos
                $this->Conferencia_model->gravar_recebimento_produto($arraydados);

                $finalizaConf = array(
                    'tce_chave_nota'        => $d_recebimentos->rec_chave_nota
                );
                $this->Conferencia_model->gravar_conferencia_iniciada($finalizaConf);

            endforeach;
        }
        
        // SOBRA/FALTAS ENCONTRADAS
        $dados_falta_sobra = $this->Conferencia_model->obtem_sobra_falta_nota_abertura_chamado($chave);

        // AVARIAS ENCONTRADAS
        $dados_avarias = $this->Conferencia_model->obtem_recebimentos_avarias_nota($chave);

        // VENCIDOS ENCONTRADOS
        $dados_vencidos = $this->Conferencia_model->obtem_recebimentos_vencidos_nota($chave);

        if ($dados_falta_sobra) {
            // GERA CHAMADO PARA AS AVARIAS ENCONTRADAS
            foreach ($dados_falta_sobra as $d) :

                $id_chamado = "";

                if ($d->itn_qtd_ven < $d->rec_quantidade) {
                    $date =  new DateTime();
                    $n = $date->format("Y-m-d H:i:s.v");                  
                    $arraydados = array(
                        'cha_data_abertura' => str_replace(" ", "T", $n),
                        'cha_solicitante'   => $this->session->userdata('usu_codapo'),
                        'cha_nota_fiscal'   => $d->cbn_num_nota,
                        'cha_id_mensagem'   => '1',
                        'cha_status'        => 'PENDENTE',
                        'cha_tipo'          => 'SOBRA',
                        'cha_id_volume'     => ''
                    );

                    $id_chamado = $this->Chamados_model->adicionar_chamado($arraydados);
                    $teveChamadoAberto = true;

                    $arrayitens = array(
                        'chi_id_chamado'    => $id_chamado,
                        'chi_id_pro'        => $d->itn_id_pro,
                        'chi_qtd'           => ($d->itn_qtd_ven - $d->rec_quantidade) * (-1),
                        'chi_obs'           => ''
                    );
                    $this->Chamados_model->adicionar_item_chamado($arrayitens);
                } else if ($d->itn_qtd_ven > $d->rec_quantidade) {
                    $date =  new DateTime();
                    $n = $date->format("Y-m-d H:i:s.v");   
                    $arraydados = array(
                        'cha_data_abertura' => str_replace(" ", "T", $n),
                        'cha_solicitante'   => $this->session->userdata('usu_codapo'),
                        'cha_nota_fiscal'   => $d->cbn_num_nota,
                        'cha_id_mensagem'   => '1',
                        'cha_status'        => 'PENDENTE',
                        'cha_tipo'          => 'FALTA',
                        'cha_id_volume'     => ''
                    );

                    $id_chamado = $this->Chamados_model->adicionar_chamado($arraydados);
                    $teveChamadoAberto = true;

                    $arrayitens = array(
                        'chi_id_chamado'    => $id_chamado,
                        'chi_id_pro'        => $d->itn_id_pro,
                        'chi_qtd'           => $d->itn_qtd_ven - $d->rec_quantidade,
                        'chi_obs'           => ''
                    );
                    $this->Chamados_model->adicionar_item_chamado($arrayitens);
                }

                $qtdAvarias = 0;
                if ($dados_avarias) {
                    foreach ($dados_avarias as $ava) :
                        if ($ava->rec_produto == $d->itn_id_pro) {
                            $qtdAvarias = $qtdAvarias + $ava->recava_quantidade;
                        }
                    endforeach;
                }

                $qtdVencidos = 0;
                if ($dados_vencidos) {
                    foreach ($dados_vencidos as $ven) :
                        if ($ven->rec_produto == $d->itn_id_pro) {
                            $qtdVencidos = $qtdVencidos + $ven->recven_quantidade;
                        }
                    endforeach;
                }

                $existe = false;
                if ($dados_recebimentos) {
                    foreach ($dados_recebimentos as $d_recebimentos) :
                        if ($d_recebimentos->rec_produto == $d->itn_id_pro) {
                            $existe = true;
                        }
                    endforeach;
                }

                if ($existe) {
                    $qtdDiv = $d->rec_quantidade - $d->itn_qtd_ven;
                    $date =  new DateTime();
                    $n = $date->format("Y-m-d H:i:s.v");
                    if ($qtdDiv > 0) {
                        // GRAVAR DADOS DA MOVIMENTAÇAO DE ESTOQUE SUBTRAINDO DIVERGENCIAS
                        $estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '01';
                        $arraymovimento = array(
                            'mov_data'                  => str_replace(" ", "T", $n),
                            'mov_id_filial'             => $filial,
                            'mov_id_produto'            => $d->itn_id_pro,
                            'mov_qtd_movimentada'       => $d->rec_quantidade - $qtdDiv - $qtdAvarias - $qtdVencidos,
                            'mov_estoque_destino'       => $estoque_destino,
                            'mov_controle_integracao'   => 'L',
                            'mov_chave_nfe'             => $dados_recebimentos[0]->cbn_chave_nota,
                        );

                        $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                    } else if ($qtdDiv < 0) {
                        $estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '01';
                        $arraymovimento = array(
                            'mov_data'                  => str_replace(" ", "T", $n),
                            'mov_id_filial'             => $filial,
                            'mov_id_produto'            => $d->itn_id_pro,
                            'mov_qtd_movimentada'       => $d->itn_qtd_ven - ($qtdDiv * -1) - $qtdAvarias - $qtdVencidos,
                            'mov_estoque_destino'       => $estoque_destino,
                            'mov_controle_integracao'   => 'L',
                            'mov_chave_nfe'             => $dados_recebimentos[0]->cbn_chave_nota,
                        );

                        $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                    } else {
                        $estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '01';
                        $dt = date("Y-m-d H:m:i.v");
                        $arraymovimento = array(
                            'mov_data'                  => str_replace(" ", "T", $dt),
                            'mov_id_filial'             => $filial,
                            'mov_id_produto'            => $d->itn_id_pro,
                            'mov_qtd_movimentada'       => $d->itn_qtd_ven - $qtdAvarias - $qtdVencidos,
                            'mov_estoque_destino'       => $estoque_destino,
                            'mov_controle_integracao'   => 'L',
                            'mov_chave_nfe'             => $dados_recebimentos[0]->cbn_chave_nota,
                        );

                        $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                    }
                }

                if ($qtdAvarias > 0) {
                    $estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '03';
                    $date =  new DateTime();
                    $n = $date->format("Y-m-d H:i:s.v");
                    $arraymovimento = array(
                        'mov_data'                  => str_replace(" ", "T", $n),
                        'mov_id_filial'             => $filial,
                        'mov_id_produto'            => $d->itn_id_pro,
                        'mov_qtd_movimentada'       => $qtdAvarias,
                        'mov_estoque_destino'       => $estoque_destino,
                        'mov_controle_integracao'   => 'L',
                        'mov_chave_nfe'             => $d->cbn_chave_nota,
                    );

                    $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                }

                if ($qtdVencidos > 0) {
                    $estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '03';
                    $date =  new DateTime();
                    $n = $date->format("Y-m-d H:i:s.v");
                    $arraymovimento = array(
                        'mov_data'                  => str_replace(" ", "T", $n),
                        'mov_id_filial'             => $filial,
                        'mov_id_produto'            => $d->itn_id_pro,
                        'mov_qtd_movimentada'       => $qtdVencidos,
                        'mov_estoque_destino'       => $estoque_destino,
                        'mov_controle_integracao'   => 'L',
                        'mov_chave_nfe'             => $d->cbn_chave_nota,
                    );

                    $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
                }
            endforeach;
        }

        if ($dados_avarias) {
            // GRAVA AVARIAS DOS PRODUTOS
            foreach ($dados_avarias as $d_avarias) :
                $arraydados = array(
                    'rcbava_id_recebimento' => $d_avarias->recava_id_recebimento,
                    'rcbava_tipo'           => $d_avarias->recava_tipo,
                    'rcbava_quantidade'     => $d_avarias->recava_quantidade
                );

                $this->Conferencia_model->gravar_recebimento_avarias_produto($arraydados);
            endforeach;

            // GERA CHAMADO PARA AS AVARIAS ENCONTRADAS
            foreach ($dados_avarias as $d_avarias_2) :

                $rec_tipo_aux   = "";
                $id_chamado     = "";

                if ($d_avarias_2->recava_tipo != $rec_tipo_aux) {
                    $date =  new DateTime();
                    $n = $date->format("Y-m-d H:i:s.v");
                    $rec_tipo_aux = $d_avarias_2->recava_tipo;
                    $arraydados = array(
                        'cha_data_abertura' => str_replace(" ", "T", $n),
                        'cha_solicitante'   => $this->session->userdata('usu_codapo'),
                        'cha_nota_fiscal'   => $d_avarias_2->rec_nota_fiscal,
                        'cha_id_mensagem'   => '1',
                        'cha_status'        => 'PENDENTE',
                        'cha_tipo'          => 'AVARIA',
                        'cha_id_volume'     => ''
                    );

                    $id_chamado = $this->Chamados_model->adicionar_chamado($arraydados);
                    $teveChamadoAberto = true;
                }

                $arrayitens = array(
                    'chi_id_chamado'    => $id_chamado,
                    'chi_id_pro'        => $d_avarias_2->rec_produto,
                    'chi_qtd'           => $d_avarias_2->recava_quantidade,
                    'chi_obs'           => $d_avarias_2->recava_tipo
                );
                $this->Chamados_model->adicionar_item_chamado($arrayitens);

                // para AVARIA, adiciona pedido de espelho de nota
                // um para cada item

                $date =  new DateTime();
                $n = $date->format("Y-m-d H:i:s.v");
                $dados_pedido_nota = array(
                    'ped_data' => str_replace(" ", "T", $n),
                    'ped_id_filial' => $filial,
                    'ped_id_cabecalho_nota' => $d_avarias_2->rec_cbn_id,
                    'ped_controle_integracao' => 'L',
                    'ped_id_pro' => $arrayitens['chi_id_pro'],
                    'ped_qtd' => $arrayitens['chi_qtd'],
                    'ped_tipo_recebimento' => 2,
                    'ped_id_chamado' => $arrayitens['chi_id_chamado']
                );
                $this->Chamados_model->adicionarPedidoNota($dados_pedido_nota);

            endforeach;

            // GRAVAR DADOS DA MOVIMENTAÇAO DE ESTOQUE

            /*$estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '03';
            $arraymovimento = array(
                'mov_data'                  => date('Y-m-d H:i:s', strtotime('NOW')),
                'mov_id_filial'             => $filial,
                'mov_id_produto'            => $d_avarias_2->rec_produto,
                'mov_qtd_movimentada'       => $d_avarias_2->recava_quantidade,
                'mov_estoque_destino'       => $estoque_destino,
                'mov_controle_integracao'   => 'L',
                'mov_chave_nfe'             => $d_recebimentos->cbn_chave_nota,
            );

            $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);*/
        }

        if ($dados_vencidos) {
            // GRAVA PRODUTOS VENCIDOS
            foreach ($dados_vencidos as $d_vencidos) :
                $arraydados = array(
                    'rcbven_id_recebimento' => $d_vencidos->recven_id_recebimento,
                    'rcbven_data'           => $d_vencidos->recven_data,
                    'rcbven_quantidade'     => $d_vencidos->recven_quantidade
                );

                $this->Conferencia_model->gravar_recebimento_vencidos_produto($arraydados);
            endforeach;

            // GERAR CHAMADOS PARA PRODUTOS VENCIDOS
            foreach ($dados_vencidos as $d_vencidos_2) :
                $date =  new DateTime();
                $n = $date->format("Y-m-d H:i:s.v");
                $arraydados = array(
                    'cha_data_abertura' => str_replace(" ", "T", $n),
                    'cha_solicitante'   => $this->session->userdata('usu_codapo'),
                    'cha_nota_fiscal'   => $d_vencidos_2->rec_nota_fiscal,
                    'cha_id_mensagem'   => '2',
                    'cha_status'        => 'PENDENTE',
                    'cha_tipo'          => 'VENCIDO',
                    'cha_id_volume'     => ''
                );

                $id_chamado = $this->Chamados_model->adicionar_chamado($arraydados);
                $teveChamadoAberto = true;

                $arrayitens = array(
                    'chi_id_chamado'    => $id_chamado,
                    'chi_id_pro'        => $d_vencidos_2->rec_produto,
                    'chi_qtd'           => $d_vencidos_2->recven_quantidade,
                    'chi_obs'           => $d_vencidos_2->recven_data
                );
                $this->Chamados_model->adicionar_item_chamado($arrayitens);

                // para VENCIDO, adiciona pedido de espelho de nota
                // um para cada item
                $date =  new DateTime();
                $n = $date->format("Y-m-d H:i:s.v");
                $dados_pedido_nota = array(
                    'ped_data' => date('Y-m-d H:i:s', strtotime('NOW')),
                    'ped_id_filial' => $filial,
                    'ped_id_cabecalho_nota' => $d_vencidos_2->rec_cbn_id,
                    'ped_controle_integracao' => 'L',
                    'ped_id_pro' => $arrayitens['chi_id_pro'],
                    'ped_qtd' => $arrayitens['chi_qtd'],
                    'ped_tipo_recebimento' => 2,
                    'ped_id_chamado' => $arrayitens['chi_id_chamado']
                );
                $this->Chamados_model->adicionarPedidoNota($dados_pedido_nota);

            endforeach;

            // GRAVAR DADOS DA MOVIMENTAÇAO DE ESTOQUE
            /*$estoque_destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '03';
            $arraymovimento = array(
                'mov_data'                  => date('Y-m-d H:i:s', strtotime('NOW')),
                'mov_id_filial'             => $filial,
                'mov_id_produto'            => $d_vencidos_2->rec_produto,
                'mov_qtd_movimentada'       => $d_vencidos_2->recven_quantidade,
                'mov_estoque_destino'       => $estoque_destino,
                'mov_controle_integracao'   => 'L',
                'mov_chave_nfe'             => $d_recebimentos->cbn_chave_nota,
            );

            $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);*/
        }

        $controlado  = $this->Conferencia_model->verificar_nota_controlada($chave, $this->session->userdata('usu_codapo'));
        if ($controlado[0]->cont > 0) {

            $dados = $this->Conferencia_model->validacao_nota_controlada($chave);

            if ($dados) {
                $post_array = array();
                $i = 0;
                $n = '';
                foreach ($_POST as $key => $value) {
                    if ($i == 0) {
                        $n = $n . $value . ";";
                        $i = $i + 1;
                    } else if ($i == 1) {
                        $n = $n . $value . ";";
                        $i = $i + 1;
                    } else if ($i == 2) {
                        $n = $n . $value . ";";
                        $i = $i + 1;
                    } else if ($i == 3) {
                        $n = $n . $value . ";";
                        $i = 0;
                        array_push($post_array, $n);
                        $n = '';
                    }
                }

                foreach ($dados as $d) :
                    foreach ($post_array as $v) :
                        $v2 = explode(";", $v);
                        if ($d->pro_cod_pro_cli == $v2[0]) {
                            $this->Conferencia_model->update_conferencia_controlados($d->reccon_id_recebimento, $v2[1], $v2[2], $v2[3]);
                        }
                    endforeach;
                endforeach;
            }
        }

        $data['msg_status']   = '';
        $data['msg_texto']    = '';

        // Caso tenha aberto chamado, notifica por e-mail
        if ( $teveChamadoAberto ) {
            //msgEmailChamadoAberto("loja".$filial."@farmaconde.com.br");
            msgEmailChamadoAberto("rodrigo.pereira@artechs.com.br");
        }

        $data['grid_dados'] = $this->Conferencia_model->buscar_conferencias($filial);
        $this->load->view('conferencia_externa', $data);
    }
}

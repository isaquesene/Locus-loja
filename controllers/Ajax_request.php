<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author    Rodrigo Colodiano Diniz <kollodiano@gmail.com>
 * @copyright AR Technologies
 * @version   1.0.0
 *|--------------------------------------------------------------------------------------------------------
 *| Data Criação: 31/10/2019 - Rodrigo Colodiano
 *|--------------------------------------------------------------------------------------------------------
 *| Controller criado para trabalhar com as requisições AJAX
 *| OBS: Este controller retorna apenas os JSON dos dados, não realiza chamada de VIEWS
 *|
 */

class Ajax_request extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Agenda_model');
        $this->load->model('Conferencia_model');
        $this->load->model('Nf_model');
        $this->load->model('Chamados_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        /*
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}
        */
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');
        //$this->load->library('library_2');

        // Carregar HELPERS
        $this->load->helper('envioEmail');
	}

    public function index()
    {
        echo "FALSE";
    }

    public function msg_lido() {
        $usrId = $_POST['usr'];
        $msgId = $_POST['msg']; // ch_coment_id
        $leitores = $this->Chamados_model->obtem_leitores_comentarios_chamado($msgId);
        if ($leitores) {
            $leitores = json_decode($leitores->leitores);
            if (!in_array($usrId, $leitores)) {
                $leitores[] = $usrId;
                $this->Chamados_model->atualiza_leitores_comentarios_chamado($msgId, json_encode($leitores));
            }
        }
        echo "OK";
    }
    
    public function msg_novo_chamado_interno()
    {
        $filial = $_GET['cha_lj'];
        msgEmailChamadoAberto("loja".$filial."@farmaconde.com.br");
        msgEmailChamadoAberto("tarciano.cd@farmaconde.com.br");
        msgEmailChamadoAberto("sal@farmaconde.com.br");
        msgEmailChamadoAberto("gerencia@farmacondeatacado.com.br");
        //msgEmailChamadoAberto("rodrigo.pereira@artechs.com.br");
        echo 'OK';
    }
    

    // Obtem as possíveis respostas para chamado
    public function modal_respostas_chamado() {
        header('Content-Type: application/json');
        echo json_encode($this->Chamados_model->obtem_todos_resp_chamados());
    }

    // Obtem dados para o modal de avarias por produto
    public function modal_avarias()
    {
        $recebimento_id = $this->input->post('ajax_id');
        $dados_avarias  = $this->Conferencia_model->obtem_dados_avarias($recebimento_id);

        foreach($dados_avarias AS $d_avarias):
            // Total do SpinnerInput considerando vencidos
            $total_produtos = $d_avarias->rec_quantidade - $d_avarias->total_vencidos;
            // $total_produtos = $d_avarias->rec_quantidade;

            $dados = 
            [
                'status'            => TRUE, 
                'id_recebimento'    => $d_avarias->rec_id,
                'id_produto'        => $d_avarias->rec_produto,
                'cod_produto'       => $d_avarias->pro_cod_pro_cli,
                'descricao_produto' => $d_avarias->rec_produto_nome,
                'total_manchado'    => $d_avarias->total_manchado,
                'total_amassado'    => $d_avarias->total_amassado,
                'total_rasgado'     => $d_avarias->total_rasgado,
                'total_quebrado'    => $d_avarias->total_quebrado,
                'total_violado'     => $d_avarias->total_violado,
                'total_produtos'    => $total_produtos
            ];
        endforeach;

        echo json_encode($dados);
    }

	// Obtem dados para o modal de vencidos por produto
    public function modal_vencidos()
    {
        $recebimento_id = $this->input->post('ajax_id');
        $dados_vencidos  = $this->Conferencia_model->obtem_dados_vencidos($recebimento_id);
        
        foreach($dados_vencidos AS $d_vencidos):
            // Total do SpinnerInput considerando avarias
            $total_produtos = $d_vencidos->rec_quantidade - $d_vencidos->total_avarias;
            // $total_produtos = $d_vencidos->rec_quantidade;

            $dados = 
            [
                'status'            => TRUE, 
                'id_recebimento'    => $d_vencidos->rec_id,
                'id_produto'        => $d_vencidos->rec_produto,
                'cod_produto'       => $d_vencidos->pro_cod_pro_cli,
                'descricao_produto' => $d_vencidos->rec_produto_nome,
                'data_vencimento'   => implode('/', array_reverse(explode('-', $d_vencidos->recven_data))),
                'quantidade'        => $d_vencidos->recven_quantidade,
                'total_produtos'    => $total_produtos,
            ];
        endforeach;

        echo json_encode($dados);
    }

    //

    public function buscar_modal_nf()
    {
        
        $cbn_num_nota = $this->input->post('ajax_nf');       
        $cbn_id                 = '';
        $cbn_tipo_operacao      = '';
        $cbn_tipo_nota          = '';
        $itn_id_pro             = '';
        $pro_descricao          = '';
        $itn_qtd_ven            = '';
        $itn_vlr_liq            = '';
        

        if(strlen($cbn_num_nota) == 44){

            $tipo_nf = "CHAVE";

        } else {

            $tipo_nf = "NUMERO";
        }

        $dados_nf = $this->Agenda_model->NotaFiscalExiste($cbn_num_nota,$tipo_nf);    
    
        echo json_encode($dados_nf);

    }


    public function buscar_recebimento_divergencia()
    {
        
        $cbn_num_nota   = $this->input->post('ajax_nf');       
        $vol_cod_barras = $this->input->post('ajax_volume');
        $form_num_ean   = $this->input->post('ajax_ean');        

        $dados_nf = $this->Nf_model->RecebimentoDivergenteExiste($cbn_num_nota,$form_num_ean); 
        
        
        echo json_encode($dados_nf);
    }

	
	 // Atualiza a quantidade do input-spinner do produto selecionado
    public function atualiza_recebimento_qtd()
    {
        $recebimento_id = $this->input->post('ajax_id');
        $qtd_produto    = $this->input->post('ajax_qtd');

        $this->Conferencia_model->atualizar_recebimento_qtd($recebimento_id, $qtd_produto);

        $dados = 
            [
                'status'            => TRUE, 
                'id_recebimento'    => $recebimento_id,
                'quantidade'        => $qtd_produto,
            ];

        echo json_encode($dados);
    }

    // Obtem os dados para o modal de controlados por produto
    public function modal_controlados()
    {
        $recebimento_id = $this->input->post('ajax_id');
        $dados_controlados = $this->Conferencia_model->obtem_dados_controlados($recebimento_id);
        $dados_grid     = $this->Conferencia_model->obtem_dados_grid_controlados($recebimento_id);        

        $linhas_grid = array();
        $soma_lotes  = 0;

        if($dados_grid)
        {
            foreach($dados_grid AS $d_grid):
                $linhas_grid[] = 
                [
                    'contr_id'          => $d_grid->reccon_id,
                    'contr_recebimento' => $d_grid->reccon_id_recebimento,
                    'contr_lote'        => $d_grid->reccon_lote,
                    'contr_fabricacao'  => date('d/m/Y', strtotime($d_grid->reccon_data_fabricacao)),
                    'contr_validade'    => date('d/m/Y', strtotime($d_grid->reccon_data_validade)),
                    'contr_quantidade'  => $d_grid->reccon_quantidade
                ];

                // Soma as quantidades de cada lote informado
                $soma_lotes = $soma_lotes + $d_grid->reccon_quantidade;
            endforeach;
        }

        

        foreach($dados_controlados AS $d_controlados):
            $total_produto = $d_controlados->rec_quantidade;

            if($d_controlados->rec_quantidade > $soma_lotes)
            {
                $total_produto = $total_produto - $soma_lotes;
            }
            else
            {
                $total_produto = 0;
            }

            $dados = 
            [
                'status'            => TRUE, 
                'id_recebimento'    => $d_controlados->rec_id,
                'id_produto'        => $d_controlados->rec_produto,
                'cod_produto'       => $d_controlados->pro_cod_pro_cli,
                'descricao_produto' => $d_controlados->rec_produto_nome,
                'data_fabricacao'   => date('d/m/Y', strtotime($d_controlados->itn_fabricacao)),
                'data_validade'     => date('d/m/Y', strtotime($d_controlados->itn_validade)),
                'total_produto'     => $total_produto,
                'soma_lotes'        => $soma_lotes,
                'linhas_grid'       => $linhas_grid
            ];
        endforeach;

        echo json_encode($dados);
    }

    // Insere dados do grid de controlados do modal
    public function atualiza_grid_controlados()
    {
        $recebimento    = $this->input->post('ajax_c_recebimento');
        $lote           = $this->input->post('ajax_c_lote');
        $fabricacao     = implode('-', array_reverse(explode('/', $this->input->post('ajax_c_fabricacao'))));
        $validade       = implode('-', array_reverse(explode('/', $this->input->post('ajax_c_validade'))));
        $quantidade     = $this->input->post('ajax_c_quantidade');

        $status = $this->Conferencia_model->inserir_conferencia_controlados($recebimento, $lote, $fabricacao, $validade, $quantidade);
        $dados = 
        [
            'status' => $status
        ];
        echo json_encode($dados);

    }
    
    // Insere dados do grid de controlados do modal
    public function apaga_controlados_grid()
    {
        $contr_id       = trim($this->input->post('controlado_id'));
        $qtd_removida   = $this->Conferencia_model->obtem_qtd_controlado($contr_id);
        $status         = $this->Conferencia_model->excluir_controlados_grid($contr_id);
        $dados = 
        [
            'status'        => $status,
            'qtd_removida'  => $qtd_removida
        ];
        echo json_encode($dados);
    }

    // Insere dados do grid de controlados do modal
    public function atualiza_chave_complementar()
    {
        $chave_nf   = $this->input->post('ajax_c_chave');
        $loja       = $this->session->userdata('usu_codapo');
        $status     = $this->Conferencia_model->gravar_chave_complementar($chave_nf, $loja);

        if($status == 'OK')
        {
            $flag_erro  = 'N';
            $msg_erro   = '';
        }
        else
        {
            $flag_erro  = 'S';
            $msg_erro   = $status;
        }

        $dados = 
        [
            'flag_erro' => $flag_erro,
            'msg_erro'  => $msg_erro
        ];
        echo json_encode($dados);

    }

    public function busca_notas_agendamento(){
        $agenf_id_agenda = $this->input->post('agenf_id_agenda');      

        $dados_agendamento = $this->Agenda_model->busca_notas_agendamento($agenf_id_agenda);    
    
        echo json_encode($dados_agendamento);

    }

    public function adicionar_nf_agenda(){

        $cbn_nota = $this->input->post('cbn_nota');
        if(!empty($this->session->userdata('notas_coleta'))){
            $this->session->set_userdata('notas_coleta',[]);
        }

        $retorno =  $this->session->userdata('notas_coleta');

        if(strlen($cbn_nota) == 44){
            if(!$this->Agenda_model->verificaExistenciaNotaPorChave($cbn_nota)){
                $data['msg_status'] = 'ERRO';
                $data['msg_texto'] = 'A nota fiscal não existe na base de dados.';
                return;
            }
            $retorno[] = $this->Agenda_model->verificaExistenciaNotaPorChave($cbn_nota);
            $this->session->set_userdata('notas_coleta',$retorno); 
            $dadosNf = $this->session->userdata('notas_coleta');
            echo json_encode($dadosNf);
            return;
        }
        if(!$this->Agenda_model->verificaExistenciaNotaPorID($cbn_nota)){
            $data['msg_status'] = 'ERRO';
            $data['msg_texto'] = 'A nota fiscal não existe na base de dados.';
            return;
        }
        $retorno[] = $this->Agenda_model->verificaExistenciaNotaPorID($cbn_nota);
        $this->session->set_userdata('notas_coleta',$retorno); 
        $dadosNf = $this->session->userdata('notas_coleta');
        echo json_encode($dadosNf);
    }

    function cadastrar_nf_agenda(){
        $agenf_id_agenda = $this->input->post('agenf_id_agenda');
        foreach($this->session->userdata('notas_coleta') as $dados){
            $dadosAgendaNf = array
            (
                'agenf_id_agenda'	 => $agenf_id_agenda,
                'agenf_nota_fiscal'	 => $dados[0]->cbn_num_nota,
                'agenf_data_cadastro' 	 => date("Y-m-d H:i:s")	
            );

            if(!$this->Agenda_model->criar_agenda_coleta_nf($dadosAgendaNf)){  
                echo json_encode(false);
            }
        }
            echo json_encode(true);
    }

    /**
     * Exibe a NF 
     */
    public function verNota($id_cbn, $tk){
        
        if (empty($id_cbn) || empty($tk) || $tk != 'f2da5d3220600fc2feabebee816ac130a03ae719cda916db01042ac9ca60eb04588bdb6b4e835983102ae39eb87d01006a8b4ed4cadf32f58095f0e3c1af27f6') {
            die('err');
        }

        $this->load->helper('montaxml');
        
        $detalhes_nf = $this->Nf_model->getNotaDanfe($id_cbn);
        $produtos_nf = $this->Nf_model->getItensNota($id_cbn);
        // $itens_nf = $this->Nf_model->obtem_detalhes_itens_nota($id_cbn);

        $xml = montaXML($detalhes_nf, $produtos_nf);
        return $xml;
    }
    

}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Conferencia_externa_recusa extends CI_Controller 
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
        $this->load->model('Nf_model');
        $this->load->model('Siad_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 22; //Cod. da tela - Tabela t_telas
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
        $filial = $this->session->userdata('usu_codapo');

        // verifica se a loja está em inventário
        // conforme tabela de inventário
        $data['inventario'] = $this->Conferencia_model->inventario($filial);

        $data['msg_status']   = '';
        $data['msg_texto']    = '';  

        $data['grid_dados'] = $this->Conferencia_model->buscar_conferencias_recusadas($this->session->userdata('usu_codapo'));
        
        $data['parametro_conferencia_obrigatoria'] = $this->Conferencia_model->parametro_conferencia_obrigatoria();

        $this->load->view('conferencia_externa_recusa', $data);

    }

    public function buscar_nota(){        
        
        $chave = $this->input->post('form-chave');

        $valida = $this->Conferencia_model->valida_nota($chave);

        $validaInterna = $this->Conferencia_model->valida_nota_interna($chave);

        if ($validaInterna == true) {

            $data['msg_status']   = 'Nota Interna';
            $data['msg_texto']    = 'Este módulo não aceita notas de fornecedores internos';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'],'error']);   

            redirect(base_url() . 'conferencia_externa_recusa', 'refresh');

        }

        // Isso valida se a nota já esta como entregue, pq se nao a logica quebra

        if ($valida == false) {

            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Já foi encontrado uma nota associado a esta chave';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'],'error']);   

            redirect(base_url() . 'conferencia_externa_recusa', 'refresh');

        }

        $uso = $this->Conferencia_model->buscar_nota_uso($chave);

        $nota = $this->Conferencia_model->buscar_nota_externa($chave, $this->session->userdata('usu_codapo'));  

        if($uso > 0){

            $data['msg_status']   = 'NOTA DE USO E CONSUMO';
            $data['msg_texto']    = 'Foi encontrado uma nota de uso associado a esta chave';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'],'warning']);

        }
        elseif($uso <= 0){

            $chave = $this->input->post('form-chave');
            
            $nota = $this->Conferencia_model->buscar_nota_externa($chave, $this->session->userdata('usu_codapo')); 
                
            $matchingPermiteAvancar = false;       
             
            if ($nota){
                
                // Consulta matching para a nota
                $matching = $this->Conferencia_model->consultar_matching_nota_externa($chave);
                
                if (!$matching) {
                    /*$msg = 'Matching não encontrado.';
                    $this->session->set_flashdata('flash-alert', ['Erro', $msg, 'error']);*/
                    $matchingPermiteAvancar = true;
    
                    // echo $matching;
                }
                elseif ($matching->mat_sit_nfe == 3) {
                    $matchingPermiteAvancar = true;
                }
                elseif ($matching->mat_sit_nfe == 2 && $matching->mat_num_pedido == 0) {
                    $msg = 'Divergência de pedido, favor entrar em contato com o departamento de Compras.';
                    $this->session->set_flashdata('flash-alert', ['Erro', $msg, 'error']);
                }
                elseif ($matching->mat_sit_nfe == 2 && $matching->mat_num_pedido != 0) {
                    $msg = 'A nota fiscal possui divergências favor entrar em contato com o departamento fiscal.';
                    $this->session->set_flashdata('flash-alert', ['Erro', $msg, 'error']);
                }else{
                    $matchingPermiteAvancar = true;
                }
    
            } else {
    
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Nota fiscal não encontrada na base de dados!';
                $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'],'info']);
   
            }
    
    
    
            if ( $nota && $matchingPermiteAvancar ) {
                
                // Procura por produtos termolabil no pedido
                $numero_termolabil = $this->Conferencia_model->conta_termolabil_nota_externa($nota[0]->cbn_id);
                
                if ($numero_termolabil > 0) {
                    $msg = 'Essa nota contém um produto termolábil que deve ser conferido imediatamente!';
                    $this->session->set_flashdata('flash-alert', ['Atenção', $msg, 'warning']);
                }
    
            }
            
            $this->Conferencia_model->alterar_status_nota_externa($chave);
            $this->Conferencia_model->gravar_recebimento_nota_em_transportes($nota[0]->cbn_id, $this->session->userdata('usu_id'));


            // insere no SIAD
            // internamente está ignorando erros que retornem
            // deste insert
            $this->Siad_model->finalizar_entrega_chave(
                $chave,
                $nota[0]->cbn_data_emissao,
                $this->session->userdata('usu_codapo'),
                $nota[0]->cbn_cnpj_destinatario,
                $nota[0]->cbn_qtd_item,
                $this->session->userdata('usu_nome')
            );
            

            $data['grid_dados'] = $this->Conferencia_model->buscar_conferencias($this->session->userdata('usu_codapo'));

            $itens = $this->Nf_model->obtem_itens_nota($chave);

            foreach($itens AS $i)
            {
                $arraymovimento = array(
                    'mov_data'                  => date('Y-m-d H:i:s', strtotime('NOW')),
                    'mov_id_filial'             => $i->cbn_id_fil,
                    'mov_id_produto'            => $i->itn_id_pro,
                    'mov_qtd_movimentada'       => $i->itn_qtd_ven,
                    'mov_estoque_destino'       => 'FECHAMENTO',
                    'mov_controle_integracao'   => 'L',
                    'mov_chave_nfe'             => $i->cbn_chave_nota,
                );
    
                // echo json_encode($arraymovimento);
                // $this->Conferencia_model->gravar_movimentacao_estoque($arraymovimento);
            }            
        } 
        
        else{    
            
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Nota fiscal não encontrada na base de dados!';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'],'error']);   

            redirect(base_url() . 'conferencia_externa_recusa', 'refresh');

        }

        $filial = $this->session->userdata('usu_codapo');

        $data['grid_dados'] = $this->Conferencia_model->buscar_nota_uso($chave);

        $this->Conferencia_model->alterar_status_nota_externa($chave);

        if ($uso > 0 ){

            $this->Conferencia_model->gravar_recebimento_nota_em_transportes_uso($uso[0]->cbn_id, $this->session->userdata('usu_id'));

            $this->load->view('conferencia_uso', $data);

        }
        else{

            redirect(base_url() . 'conferencia_externa_recusa', 'refresh');
            // $this->load->view('conferencia_externa_recusa', $data);
        }
    }
}
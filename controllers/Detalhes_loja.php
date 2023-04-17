<?php defined('BASEPATH') or exit('No direct script access allowed');


class Detalhes_loja extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Inventario_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if (!$this->session->userdata('loggedin')) {
            redirect(base_url() . 'login', 'refresh');
        }

        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');

        $this->_init();
    }

    private function _init()
    {
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
        $inv = $this->uri->segment(2, 0);
        $data['produtos'] = $this->Inventario_model->obtem_produto_realizacoes($inv);
        $data['inv_finalizado'] = $this->Inventario_model->verifica_contagem_aprovada($inv);
        foreach ($data['produtos']  as $produto) {
            if ($produto->con_pro_contagem === null) {
                $produto->con_pro_contagem = 'Não Contado';
            }
        }
        //echo json_encode($data);
        $this->load->view('detalhes_loja', $data);
    
    }
    public function calculoContagem(){
        $id = basename($_SERVER['HTTP_REFERER']);
        $data['produtos'] = $this->Inventario_model->obtem_produto_realizacoes($id);
        $data['post'] = $_POST;
        if($data['post']['finalizar'] == "aprovar"){
            for($i=0;$i < count($data['produtos']);$i++){
                $prod = $data['produtos'][$i];
                $cod_produto = $prod->pro_cod_pro_cli;
                if($prod->con_transferencia != $data['post']['transferencia'.$cod_produto]){
                    // $control[$i]["transferencia"] = true;
                    $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_transferencia',$data['post']['transferencia'.$cod_produto]);
                }
                if($prod->con_cupom_fiscal != $data['post']['cupom'.$cod_produto]){
                    // $control[$i]["cupom"] = true;
                    $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_cupom_fiscal',$data['post']['cupom'.$cod_produto]);
                }
                if($prod->con_dev_cliente != $data['post']['cliente'.$cod_produto]){
                    // $control[$i]["cliente"] = true;
                    $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_dev_cliente',$data['post']['cliente'.$cod_produto]);
                }
                if($prod->con_nf_entrada != $data['post']['entrada'.$cod_produto]){
                    // $control[$i]["entrada"] = true;
                    $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_nf_entrada',$data['post']['entrada'.$cod_produto]);
                }
                if($prod->con_dev_fornecedor != $data['post']['fornecedor'.$cod_produto]){
                    // $control[$i]["fornecedor"] = true;
                    $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_dev_fornecedor',$data['post']['fornecedor'.$cod_produto]);
                }

                $temp = $this->Inventario_model->obtem_produto_realizacoes_unico($id,$cod_produto);

                $conta = $temp[0]->con_pro_estoque+($temp[0]->con_nf_entrada+$temp[0]->con_dev_cliente)+(-$temp[0]->con_transferencia-$temp[0]->con_cupom_fiscal-$temp[0]->con_dev_fornecedor);
                $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_estoque_ajustado',$conta);
                $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_inv_finalizado',1);
                $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_inv_data_finalizado',date('Y-m-d H:i:s'));
            }

        }else{
            for($i=0;$i < count($data['produtos']);$i++){
                $prod = $data['produtos'][$i];
                $cod_produto = $prod->pro_cod_pro_cli;
            $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_inv_finalizado',2);
            $this->Inventario_model->atualiza_contagem($id,$cod_produto,'con_inv_data_finalizado',date('Y-m-d H:i:s'));
            }
        }
        redirect(base_url() . 'relatorio_loja', 'refresh'); 
    }
}

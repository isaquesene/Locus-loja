<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Realizar_inventario_ajax extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        //$this->load->model('model_1');
        $this->load->model('Regiao_model');
        $this->load->model('Filial_model');
        $this->load->model('Inventario_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');

	}
        
	public function index()
	{
        echo "FALSE";
	}

    public function getProdutos() {

        $agen_numero = $this->input->post('agen_numero');
        $rea_numero = $this->input->post('rea_numero');
        $param['con_id_agendamento'] = $agen_numero;
        $param['con_id_realizacao'] = $rea_numero;
        $data = array();
        $data['agen_numero'] = $agen_numero;
        $data['rea_numero'] = $rea_numero;
        $data['produtos'] = $this->Inventario_model->obtem_produtos($agen_numero);
        

        // $prods = $this->Inventario_model->obtem_produtos_cesta($agen_numero);
        //     $prd = array_values(get_object_vars($prods[0]));
        //     unset($prd[1]);
        //     unset($prd[0]);
        //     array_values($prd);
        //     $arrayProd = array();
        //     foreach($prd as $i) {
        //         $produto = $this->Inventario_model->obtem_produto_id($i);
        //         if($produto != false){
        //             array_push($arrayProd, $produto[0]);
        //         }
        //     }
        //     $data['produtos'] = $arrayProd;
       
        // if(!$data['produtos']){
        //     $data['produtos'] = $this->Inventario_model->obtem_produtos($agen_numero);  
        // }
        $agendamento = $this->Inventario_model->obtem_agendamento_por_realizacao($agen_numero);
        $tempo_limite =  $agendamento[0]->agen_tempo_limite;

        $realizacao = $this->Inventario_model->obtem_realizacao_por_id($rea_numero);
        $horaini =  $realizacao[0]->rea_hora_ini;

        $data['hora_inicio_contagem'] = $horaini;
        $data['tempo_limite'] = $tempo_limite;
        
        $newTime = new DateTime();
        $dateDb = date_format($newTime, 'Y-m-d H:i:s');
        
        if($realizacao[0]->rea_status <> 'EM ANDAMENTO'){
            foreach ($data['produtos'] as $prod) {
                $param['con_id_pro'] = $prod->pro_cod_pro_cli;
                $param['con_pro_estoque'] = 0;
                $param['con_pro_contagem'] = 0;
                $param['con_pro_divergencia'] = 0;
                $param['con_transferencia'] = 0;
                $param['con_cupom_fiscal'] = 0;
                $param['con_dev_cliente'] = 0;
                $param['con_nf_entrada'] = 0;
                $param['con_dev_fornecedor'] = 0;
                $param['con_estoque_ajustado'] = 0;
                $param['con_inv_finalizado'] = 0; // 1 sim //  2 nao
                $param['con_inv_data_finalizado'] = null; //null
                $this->Inventario_model->insere_contagem($param);
            }
            $realizacao[0]->rea_hora_ini = $dateDb;
            
            $realizacao[0]->rea_status = "EM ANDAMENTO";
            $realizacao[0]->rea_usuario = $_SESSION['usu_id'];
            $rea_id = $realizacao[0]->rea_id;
            unset($realizacao[0]->rea_id);

            $data['hora_inicio_contagem'] = $dateDb;            
            
            $this->Inventario_model->update_realizacao($realizacao[0], $rea_id);            
            
            echo json_encode($data);

        } else {

            $realizacao[0]->rea_status = "EM ANDAMENTO";
            $realizacao[0]->rea_usuario = $_SESSION['usu_id'];
            $rea_id = $realizacao[0]->rea_id;
            unset($realizacao[0]->rea_id);
            $this->Inventario_model->update_realizacao($realizacao[0], $rea_id);
            echo json_encode($data);

        }

        
    }
}
 

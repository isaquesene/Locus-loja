<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Auditar_produto extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Notas_fiscais_model');
        $this->load->model('Usuario_model');
        //$this->load->model('model_2');
        //$this->load->model('model_3');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}
		


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
        $volume = $this->uri->segment(4);
        $data['status'] = $this->uri->segment(2);
        $_SESSION['status_vol'] = $this->uri->segment(2);
        $_SESSION['volume'] = $volume;
        $id_nota = $_SESSION['cbn_id_auditoria'];

        $data['dados_nfs'] = $this->Notas_fiscais_model->consulta_produtos_banco_auditoria($id_nota, $volume);
        
        $_SESSION['eans'] = $this->Notas_fiscais_model->consulta_produtos_ean($id_nota, $volume);
        $_SESSION['auditoria_em_andamento'] = $data['dados_nfs'];

        $this->load->view('auditar_produto', $data); 
    
    
        // if(isset($_SESSION['auditoria_em_andamento'])){
            
        //     $data['dados_nfs'] = $_SESSION['auditoria_em_andamento'];

        //     $this->load->view('auditar_produto', $data); 
        // }else{

        //     $data['dados_nfs'] = $this->Notas_fiscais_model->consulta_produtos_banco_auditoria($id_nota, $volume);
        
        //     $_SESSION['eans'] = $this->Notas_fiscais_model->consulta_produtos_ean($id_nota, $volume);
        //     $_SESSION['auditoria_em_andamento'] = $data['dados_nfs'];

        //     $this->load->view('auditar_produto', $data); 
        // }
    }
    
    public function adicionar()
    {
        $cod_pro = 0;
        $fil_id =  $_SESSION['filialaud'];
        
        $id_nota = $_SESSION['cbn_id_auditoria'];

        $ean_conferido = $_POST['ean_prod'];
        $lista_produtos = $_SESSION['eans'];
        foreach($lista_produtos as $a){
            if(trim($a->ean) == trim($ean_conferido)){
                $cod_pro = $a->codigo_pro;
            }
        }

        if ($cod_pro != 0)
        {
            $lista_produtos2 = $_SESSION['auditoria_em_andamento'];
            foreach($lista_produtos2 as $a){
                if($a->pro_codigo == $cod_pro){
                    $a->qtd_conferida = $a->qtd_conferida+1;
                    $pro_id = $a->pro_id;
                    $volume2 = $a->itn_cod_barras;
                    $andamento['ada_id_fil'] = $fil_id;
					$andamento['ada_id_pro'] = $pro_id;
					$andamento['ada_id_usu'] = $_SESSION['usu_id'];
                    $andamento['ada_qtd'] = 1;
                    $andamento['ada_data'] = date('Y-m-d', strtotime('NOW'));
                    $andamento['ada_id_cbn'] = $id_nota;
                    $andamento['ada_cod_barras'] = $a->itn_cod_barras;

                    // echo json_encode($andamento);
                    
                    

                    $this->Notas_fiscais_model->inserir_auditoria_andamento($andamento);

                    
                }
            }
            
            $lista_produtos2 = $this->Notas_fiscais_model->consulta_produtos_banco_auditoria($id_nota, $volume2);
            $_SESSION['auditoria_em_andamento'] = $lista_produtos2;
            $data['dados_nfs'] = $lista_produtos2;
            $this->load->view('auditar_produto', $data); 
        
        
        }else{
            echo  '<script>alert("Ean não encontrado")</script>';
            $data['dados_nfs'] = $_SESSION['auditoria_em_andamento'];
            $this->load->view('auditar_produto', $data); 
        }
    }

    public function auditoria()
    {
        redirect(base_url().'auditoria', 'refresh');
    }

    public function auditar_lista_volume()
    {
        redirect(base_url().'auditar_lista_volume', 'refresh');
    }



    public function salvar()
    {
        $fil_id =  $_SESSION['filialaud'];
        $id_nota = $_SESSION['cbn_id_auditoria'];
        $volume = $_SESSION['volume'];
        $status = $_SESSION['status_vol'];
        $data['dados_nfs'] = $this->Notas_fiscais_model->consulta_produtos_nota($id_nota, $volume);
        $lista_produtos = $_SESSION['auditoria_em_andamento'];
        $array = array();
        $array2 = array();

        foreach($lista_produtos as $dadosaudi){
            $array[] = $dadosaudi->pro_codigo. ' | ' .$dadosaudi->pro_descricao. ' | ' .$dadosaudi->qtd_conferida. ' | ' .$dadosaudi->pro_id;
        }

        foreach($data['dados_nfs'] as $dadosoriginal){
            $array2[] = $dadosoriginal->pro_codigo. ' | ' .$dadosoriginal->pro_descricao. ' | ' .$dadosoriginal->qtd. ' | ' .$dadosoriginal->pro_id;
            $numeroloja = $dadosoriginal->fil_num_loja;
            $numeronota = $dadosoriginal->num_nota;
            $cbn_id	= $dadosoriginal->cbn_id;
        }

        $result = array_diff($array, $array2);

        $diferencabanco =  $this->Notas_fiscais_model->consulta_produtos_banco_auditoria($id_nota, $volume);	

        $_SESSION['numeroloja'] = $numeroloja;
        $_SESSION['numnotadiv'] = $numeronota;
                
        foreach($_SESSION['auditoria_em_andamento'] as $r) {
            if ($r->diferenca != 0) {

                $historico['hia_id_cbn'] = $id_nota;
                $historico['hia_status'] = 'DIVERGENTE';
                $historico['hia_data_inicio'] = str_replace(' ', 'T', $_SESSION['datainicioaud']);
                $historico['hia_data_fim'] 	= str_replace(' ', 'T', date('Y-m-d H:i:s', strtotime('NOW')));
                $historico['hia_id_usu'] = $_SESSION['usu_id'];
                $historico['hia_divergencia'] = $r->diferenca;
                $historico['hia_id_pro'] = $r->pro_id;
                $historico['hia_cod_barras'] = $volume;


                $historico2['hid_id_cbn'] = $id_nota;
                $historico2['hid_status'] = 'DIVERGENTE';
                $historico2['hid_data_inicio'] = str_replace(' ', 'T', $_SESSION['datainicioaud']);
                $historico2['hid_data_fim'] 	= str_replace(' ', 'T', date('Y-m-d H:i:s', strtotime('NOW')));
                $historico2['hid_id_usu'] = $_SESSION['usu_id'];
                $historico2['hid_divergencia'] = $r->diferenca;
                $historico2['hid_id_pro'] = $r->pro_id;
                $historico2['hid_cod_barras'] = $volume;
                
                $this->Notas_fiscais_model->gravar_historico($historico, $historico2);
            }
            else {
                $historico['hia_id_cbn'] = $cbn_id;
                $historico['hia_status'] = 'AUDITADA';
                $historico['hia_data_inicio'] = str_replace(' ', 'T', $_SESSION['datainicioaud']);
                $historico['hia_data_fim'] 	= str_replace(' ', 'T', date('Y-m-d H:i:s', strtotime('NOW')));
                $historico['hia_id_usu'] = $_SESSION['usu_id'];
                $historico['hia_divergencia'] = $r->diferenca;
                $historico['hia_id_pro'] = $r->pro_id;
                $historico['hia_cod_barras'] = $volume;


                $historico2['hid_id_cbn'] = $cbn_id;
                $historico2['hid_status'] = 'AUDITADA';
                $historico2['hid_data_inicio'] = str_replace(' ', 'T', $_SESSION['datainicioaud']);
                $historico2['hid_data_fim'] = str_replace(' ', 'T', date('Y-m-d H:i:s', strtotime('NOW')));
                $historico2['hid_id_usu'] = $_SESSION['usu_id'];
                $historico2['hid_divergencia'] = $r->diferenca;
                $historico2['hid_id_pro'] = $r->pro_id;
                $historico2['hid_cod_barras'] = $volume;
                $this->Notas_fiscais_model->gravar_historico($historico, $historico2);
            }
        }
        // $arraydiferenca = array();
        // foreach($diferencabanco as $d){
        //     if($d->diferenca == 0) {
        //     $arraydiferenca[] = 0;
        //     }else {
        //     $arraydiferenca[] = 1; 
        //     }
        // }     
        // if(in_array(1,$arraydiferenca)) {

        //     $completo =  $volume .'|'. $id_nota .'|'. $status;

        //     $key = array_search($completo, $_SESSION['arrayteste']);
        //     $_SESSION['arrayteste'][$key] = $volume .'|'. $id_nota.'|'.'2';

        //     echo "teste1";
        //     echo  $completo .'|'. $key;
        // }
        // else {
    
        //     $completo =  $volume .'|'. $id_nota .'|'. $status;
        
        //     $key = array_search($completo, $_SESSION['arrayteste']);
        //     $_SESSION['arrayteste'][$key] = $volume .'|'.$id_nota.'|'.'1';

        // }
					
        unset($_SESSION['auditoria_em_andamento']);

        redirect(base_url().'auditar_lista_volume', 'refresh');
    }

    public function zerarContagem()
    {
        $this->autenticacao_gerente();

        $fil_id =  $_SESSION['filialaud'];
        $id_nota = $_SESSION['cbn_id_auditoria'];

        $cod_pro = $this->uri->segment(3);
        
        $cod_pro_qt = explode('_', $cod_pro);

        $cod_pro = $cod_pro_qt[0];
        $qt = $cod_pro_qt[1];

    
        if ($cod_pro != 0)
        {
            $lista_produtos2 = $_SESSION['auditoria_em_andamento'];

            foreach($lista_produtos2 as $a){
                if($a->pro_codigo == $cod_pro){
                    $a->qtd_conferida = 0;
                    $pro_id = $a->pro_id;
                    $volume2 = $a->itn_cod_barras;
                    
                    $this->Notas_fiscais_model->limpar_auditoria_andamento_um_a_um($pro_id, $id_nota, $qt);

                    //$this->Notas_fiscais_model->limpar_auditoria_andamento($pro_id, $id_nota);
                }
            }
            
            $lista_produtos2 = $this->Notas_fiscais_model->consulta_produtos_banco_auditoria($id_nota, $volume2);
            $_SESSION['auditoria_em_andamento'] = $lista_produtos2;
            $data['dados_nfs'] = $lista_produtos2;
            $this->load->view('auditar_produto', $data); 
        
        
        }else{
            echo  '<script>alert("Produto não encontrado")</script>';
            $data['dados_nfs'] = $_SESSION['auditoria_em_andamento'];
            $this->load->view('auditar_produto', $data); 
        }
    }

    public function autenticacao_gerente(){

        if($_SESSION['data_auth_gerente'] != date("Y-m-d H")){
            $this->destroy_foo();
        }
    
        $_SESSION['data_auth_gerente'] = date("Y-m-d H");

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            redirect(base_url().'auditar_lista_volume', 'refresh');
        } 
        else {
            // VALIDA SE O LOGIN E DE UM GERENTE AUDITORIA
            /*if(!$this->Usuario_model->valida_gerente_auditoria($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])){
                // SE NAO FOR GERENTE RETORNA 'Unauthorized'
                echo "<script>alert('Autenticacao Inválida!');</script>";

                $this->destroy_foo();
                $_SESSION['data_auth_gerente'] = '';

                redirect(base_url().'auditar_lista_volume', 'refresh');
            }*/
        }

        $this->destroy_foo();
        $_SESSION['data_auth_gerente'] = '';
    
    }
    
    public function destroy_foo() {
        if(isset($_SERVER['PHP_AUTH_USER'])){
            unset($_SERVER['PHP_AUTH_USER']);
        }
    
        if (isset($_SERVER['PHP_AUTH_PW'])){
            unset($_SERVER['PHP_AUTH_PW']);
        }
    }

}

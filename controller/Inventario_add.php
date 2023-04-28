<?php defined('BASEPATH') or exit('No direct script access allowed');


class Inventario_add extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        //$this->load->model('model_1');
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
        $title = 'LOCUS ONLINE';
        $description = 'description';
        $keywords = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
        //$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
    }

    public function index()
    {
        $data['msg_status'] = '';
        $data['msg_texto'] = '';

        $this->load->view('inventario_add');
    }

    public function saveAgendamento($postParam)
    {
        $data = $postParam;
        $agendamentos = [];
        $weekDays = array('segunda' => false, 'terca' => false, 'quarta' => false, 'quinta' => false, 'sexta' => false, 'sabado' => false);

        $tipo = $postParam['inlineRadioOptions'];
        $local = $postParam['idFixo'];
        $stores = @$postParam['checkStores'];
        $numAgendamento = 0;
        $res = [];

        if ($tipo == 'regional') {
            foreach ($stores as $store) {
                $index = 'dias_semana_loja_' . $store;
                $indexReps = 'reps_loja_' . $store;
                if ($data[$index]) {
                    $arrayInt = array("store" => $store, "dias" => $data[$index], 'repeticoes' => $data[$indexReps]);

                    array_push($agendamentos, $arrayInt);
                }
            }
        } else {
            $index = 'dias_semana_loja_' . $local;
            $indexReps = 'reps_loja_' . $local;
            $arrayInt = array("store" => $local, "dias" => $data[$index], 'repeticoes' => $data[$indexReps]);
            array_push($agendamentos, $arrayInt);
        }

        $ultimoAgendamento = $this->Inventario_model->obtem_numero_ultimo_agendamento();

        if (!$ultimoAgendamento) {
            $numAgendamento = 1;
        } else {
            $numAgendamento = $ultimoAgendamento[0]->agen_numero + 1;
        }

        if ($data['divergencia'] == null) {
            $data['divergencia'] = 0;
        }
        if ($data['naoMovimentados'] == null) {
            $data['naoMovimentados'] = 0;
        }
        if ($data['falteiros'] == null) {
            $data['falteiros'] = 0;
        }

        foreach ($agendamentos as $loja) {
            $res['agen_numero'] = $numAgendamento;
            $res['agen_id_loja'] = $loja['store'];
            $res['agen_dias'] = implode(",", $loja['dias']);
            $res['agen_numero'] = $numAgendamento;
            $res['agen_repeticao'] = $loja['repeticoes'];
            $res['agen_usu'] = $_SESSION['usu_id'];
            $res['agen_data_ini'] = $data['data_inicio'];
            $res['agen_data_fim'] = $data['data_final'];
            $res['agen_status'] = 'ATIVO';
            $res['agen_tempo_limite'] = $data['tempo'];
            $res['agen_para1'] = $data['divergencia'];
            $res['agen_para2'] = $data['naoMovimentados'];
            $res['agen_para3'] = $data['falteiros'];
            $this->Inventario_model->insere_agendamento($res);
         
        }  

        $res = array('agendamentos' => $agendamentos, 'numAgendamento' => $numAgendamento, 'data' => $data);
        return $res;
    }

    public function geraCesta($divergencia,$naoMovimentados,$falteiros,$responseAgend){                    
                    
        $cesta = array();
        $inventario_model2 = new Inventario_model();
        $cestaiD = $inventario_model2->obtem_numero_ultimo_agendamento();
        
        array_push($cesta, $cestaiD[0]->agen_numero);
        
        //CASO A CESTA ESTEJA ENTRE O LIMITE ESTIPULADO ELE FAZ OS SELECTS PARA CADA PARAMENTRO E UTILIZA OS PRODUTOS PARA NAO
        //SEREM UTILIZADOS NOVAMENTE 

        if ($divergencia > 0) {

            $resultados_divergentes = $inventario_model2->obtem_divergentes($divergencia, $cestaiD[0]->agen_id_loja);
            foreach ($resultados_divergentes as $resultado) {
                $inventario_model2->atualiza_divergentes($resultado->inv_cod_pro);
                array_push($cesta, $resultado->inv_cod_pro);
            }
        }
        if ($naoMovimentados > 0) {

            $resultados_nao_movimentados = $inventario_model2->obtem_nao_movimentados($naoMovimentados, $cestaiD[0]->agen_id_loja);
            foreach ($resultados_nao_movimentados as $resultado) {
                $inventario_model2->atualiza_nao_movimentados($resultado->nmov_cod_pro); 
                array_push($cesta, $resultado->nmov_cod_pro);
            }
        }
        if ($falteiros > 0) {
            
            $resultados_faltantes = $inventario_model2->obtem_falteiros($falteiros, $cestaiD[0]->agen_id_loja);
            foreach ($resultados_faltantes as $resultado) {
                $inventario_model2->atualiza_falteiros($resultado->falt_cod_pro); 
                array_push($cesta, $resultado->falt_cod_pro);
            }
        }
        
        //AQUI EU VERIFICO SE EXISTEM PRODUTOS REPETIDOS DENTRO DO ARRAY DE CESTA
        $cestas = array_unique($cesta);
        
        
        // AQUI ELE ADICIONA PRODUTOS NO CARRINHO CASO NAO TENHA BATIDO O MAXIMO DE 15
        $qtd_itens_ficticios = 15 - count($cestas);
        for ($i = 0; $i <= $qtd_itens_ficticios; $i++) {
            if (isset($cestas[$i])) {
                array_push($cestas, null);
            }
        }
        
        $param['ces_id_agendamento'] = $responseAgend;
        $param['ces_pro1'] = $cestas[1];
        $param['ces_pro2'] = $cestas[2];
        $param['ces_pro3'] = $cestas[3];
        $param['ces_pro4'] = $cestas[4];
        $param['ces_pro5'] = $cestas[5];
        $param['ces_pro6'] = $cestas[6];
        $param['ces_pro7'] = $cestas[7];
        $param['ces_pro8'] = $cestas[8];
        $param['ces_pro9'] = $cestas[9];
        $param['ces_pro10'] = $cestas[10];
        $param['ces_pro11'] = $cestas[11];
        $param['ces_pro12'] = $cestas[12];
        $param['ces_pro13'] = $cestas[13];
        $param['ces_pro14'] = $cestas[14];
        $param['ces_pro15'] = $cestas[15];    

        return $inventario_model2->insere_cesta_nova($param);

    }

    public function tempoTemplate()
    {

        $params = [];

        $responseAgend = $this->saveAgendamento($_POST);

        $cestaiD = $this->Inventario_model->obtem_numero_ultimo_agendamento();

        //IMPUT DO CAMPO DE INSERCAO MANUAL
        if (isset($_POST['adicionar'])) {
            // Obtém os códigos de produto do textarea
            $produtosStr = $_POST['produtos'];

            // Separa os códigos de produto por vírgula
            $produtosArray = explode(',', $produtosStr);

            // Remove espaços em branco em cada código de produto
            foreach ($produtosArray as $key => $produto) {
                $produtosArray[$key] = trim($produto);
            }

            // Processa o array de produtos
            foreach ($produtosArray as $produto) {

                //provavelmente o erro csv está aqui - cesar

                // Insere o produto no banco de dados, ou faz o que você precisar aqui
                $params['ent_pro_id'] = $produto;
                $params['ent_id_agendamento'] = $responseAgend['numAgendamento'];
                $this->Inventario_model->insere_csv($params);
            }

            $this->saveRealizacao($responseAgend, $cestaiD);

            $data['msg_texto'] = 'Inventario cadastrado com Sucesso';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

            redirect(base_url() . 'inventario', 'refresh');
        }

        /* if (isset($_POST["Import"])) {

            $filename = $_FILES["file"]["tmp_name"];
            $file_type = $_FILES['file']['type'];
            $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            // VERIFICO SE O USUARIO ESTÁ REALMENTE SUBINDO UM ARQUIVO CSV
            if ($file_type === 'text/csv' || $file_type === 'application/vnd.ms-excel' && $file_ext === 'csv') {
                $file = fopen($filename, "r");

                //CONTADOR PRA IGNORAR A PRIMEIRA LINHA DO WHILE POR CONTA DO CSV
                $counter = 0;

                while (($getData = fgetcsv($file, 10000, ";")) !== FALSE) {

                    $counter++;

                    if ($counter != 1) {

                        //PEGA APENAS OS CODIGOS DENTRO DO CSV
                        $filtered_array = array_filter($getData, function ($i, $index) {
                            return $index % 2 == 0;
                        }, ARRAY_FILTER_USE_BOTH);

                        //MONTA O OBJETO PARA INSERIR NO BANCO
                        foreach ($filtered_array as $idProd) {
                            $params['ent_pro_id'] = $idProd;
                            $params['ent_id_agendamento'] = $responseAgend['numAgendamento'];
                            $this->Inventario_model->insere_csv($params);
                        }
                    }
                }

                $this->saveRealizacao($responseAgend, $cestaiD);

                $data['msg_texto'] = 'Inventario cadastrado com Sucesso';
                $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

                redirect(base_url() . 'Inventario', 'refresh');


            } else {

                $divergencia = $_POST["divergencia"];
                $naoMovimentados = $_POST["naoMovimentados"];
                $falteiros = $_POST["falteiros"];

                $cesta = array();
                $cestaiD = $this->Inventario_model->obtem_numero_ultimo_agendamento();

                array_push($cesta, $cestaiD[0]->agen_numero);


                if (is_numeric($divergencia)) {
                    $divergencia = (int) $divergencia;
                } else {
                    $divergencia = 0;
                }

                if (is_numeric($naoMovimentados)) {
                    $naoMovimentados = (int) $naoMovimentados;
                } else {
                    $naoMovimentados = 0;
                }

                if (is_numeric($falteiros)) {
                    $falteiros = (int) $falteiros;
                } else {
                    $falteiros = 0;
                }

                $total = $divergencia + $naoMovimentados + $falteiros;
                //VALIDO SE O USUARIO NAO ESTA PASSANDO DO LIMITE ESTIPULADO PELA CESTA

                if ($divergencia > 15 || $naoMovimentados > 15 || $falteiros > 15 || $total > 15) {

                    $data['msg_status'] = 'Quantidade Invalida';
                    $data['msg_texto'] = 'Os números escolhidos nos parâmetros ultrapassam a quantidade máxima de produtos da cesta (15), por favor, ajuste as quantidades e tente novamente.';
                    $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'error']);

                    redirect(base_url() . 'Inventario', 'refresh');
                }

                //CASO A CESTA ESTEJA ENTRE O LIMITE ESTIPULADO ELE FAZ OS SELECTS PARA CADA PARAMENTRO E UTILIZA OS PRODUTOS PARA NAO
                //SEREM UTILIZADOS NOVAMENTE 

                if ($divergencia > 0) {

                    $resultados_divergentes = $this->Inventario_model->obtem_divergentes($divergencia, $cestaiD[0]->agen_id_loja);
                    foreach ($resultados_divergentes as $resultado) {
                        $this->Inventario_model->atualiza_divergentes($resultado->inv_cod_pro);
                        array_push($cesta, $resultado->inv_cod_pro);
                    }
                }
                if ($naoMovimentados > 0) {

                    $resultados_nao_movimentados = $this->Inventario_model->obtem_nao_movimentados($naoMovimentados, $cestaiD[0]->agen_id_loja);
                    foreach ($resultados_nao_movimentados as $resultado) {
                        $this->Inventario_model->atualiza_nao_movimentados($resultado->nmov_cod_pro); 
                        array_push($cesta, $resultado->nmov_cod_pro);
                    }
                }
                if ($falteiros > 0) {

                    $resultados_faltantes = $this->Inventario_model->obtem_falteiros($falteiros, $cestaiD[0]->agen_id_loja);
                    foreach ($resultados_faltantes as $resultado) {
                        $this->Inventario_model->atualiza_falteiros($resultado->falt_cod_pro); 
                        array_push($cesta, $resultado->falt_cod_pro);
                    }
                }

                //AQUI EU VERIFICO SE EXISTEM PRODUTOS REPETIDOS DENTRO DO ARRAY DE CESTA
                $cestas = array_unique($cesta);


                // AQUI ELE ADICIONA PRODUTOS NO CARRINHO CASO NAO TENHA BATIDO O MAXIMO DE 15
                $qtd_itens_ficticios = 15 - count($cestas);
                for ($i = 0; $i <= $qtd_itens_ficticios; $i++) {
                    if (isset($cestas[$i])) {
                        array_push($cestas, null);
                    }
                }

                $param['ces_id_agendamento'] = $responseAgend['numAgendamento'];
                $param['ces_pro1'] = $cestas[1];
                $param['ces_pro2'] = $cestas[2];
                $param['ces_pro3'] = $cestas[3];
                $param['ces_pro4'] = $cestas[4];
                $param['ces_pro5'] = $cestas[5];
                $param['ces_pro6'] = $cestas[6];
                $param['ces_pro7'] = $cestas[7];
                $param['ces_pro8'] = $cestas[8];
                $param['ces_pro9'] = $cestas[9];
                $param['ces_pro10'] = $cestas[10];
                $param['ces_pro11'] = $cestas[11];
                $param['ces_pro12'] = $cestas[12];
                $param['ces_pro13'] = $cestas[13];
                $param['ces_pro14'] = $cestas[14];
                $param['ces_pro15'] = $cestas[15];


                $this->Inventario_model->insere_cesta($param);

                $this->saveRealizacao($responseAgend, $cestaiD);

                $data['msg_status'] = 'Sucesso';
                $data['msg_texto'] = 'Inventário cadastrado com sucesso';
                $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

                redirect(base_url() . 'Inventario', 'refresh');
            }
        } */

        if (isset($_POST["Import"])) {

            $filename = $_FILES["file"]["tmp_name"];
            $file_type = $_FILES['file']['type'];
            $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            // VERIFICO SE O USUARIO ESTÁ REALMENTE SUBINDO UM ARQUIVO CSV
            if ($file_type === 'text/csv' || $file_type === 'application/vnd.ms-excel' && $file_ext === 'csv') {
                $file = fopen($filename, "r");

                //CONTADOR PRA IGNORAR A PRIMEIRA LINHA DO WHILE POR CONTA DO CSV
                $counter = 0;

                while (($getData = fgetcsv($file, 10000, ";")) !== FALSE) {

                    $counter++;

                    if ($counter != 1) {

                        //PEGA APENAS OS CODIGOS DENTRO DO CSV
                        $filtered_array = array_filter($getData, function ($i, $index) {
                            return $index % 2 == 0;
                        }, ARRAY_FILTER_USE_BOTH);

                        //MONTA O OBJETO PARA INSERIR NO BANCO
                        foreach ($filtered_array as $idProd) {
                            $params['ent_pro_id'] = $idProd;
                            $params['ent_id_agendamento'] = $responseAgend['numAgendamento'];
                            $this->Inventario_model->insere_csv($params);
                        }
                    }
                }

                $this->saveRealizacao($responseAgend, $cestaiD);

                $data['msg_texto'] = 'Inventario cadastrado com Sucesso';
                $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

                redirect(base_url() . 'Inventario', 'refresh');


            } else {

                $divergencia = $_POST["divergencia"];
                $naoMovimentados = $_POST["naoMovimentados"];
                $falteiros = $_POST["falteiros"];                
                
                
                if (is_numeric($divergencia)) {
                    $divergencia = (int) $divergencia;
                } else {
                    $divergencia = 0;
                }
                
                if (is_numeric($naoMovimentados)) {
                    $naoMovimentados = (int) $naoMovimentados;
                } else {
                    $naoMovimentados = 0;
                }
                
                if (is_numeric($falteiros)) {
                    $falteiros = (int) $falteiros;
                } else {
                    $falteiros = 0;
                }
                
                $total = $divergencia + $naoMovimentados + $falteiros;
                //VALIDO SE O USUARIO NAO ESTA PASSANDO DO LIMITE ESTIPULADO PELA CESTA
                
                if ($divergencia > 15 || $naoMovimentados > 15 || $falteiros > 15 || $total > 15) {
                    
                    $data['msg_status'] = 'Quantidade Invalida';
                    $data['msg_texto'] = 'Os números escolhidos nos parâmetros ultrapassam a quantidade máxima de produtos da cesta (15), por favor, ajuste as quantidades e tente novamente.';
                    $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'error']);
                    
                    redirect(base_url() . 'Inventario', 'refresh');
                }                

                $this->saveRealizacao($responseAgend);

                $data['msg_status'] = 'Sucesso';
                $data['msg_texto'] = 'Inventário cadastrado com sucesso';
                $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

                redirect(base_url() . 'Inventario', 'refresh');
            }
        }
    }

    /* public function saveRealizacao($bodyParams, $idCesta){
        
        $agendamentos = $bodyParams['agendamentos'];
        $data = $bodyParams['data'];
        $numAgendamento = $bodyParams['numAgendamento'];
        $stores = array_column($bodyParams['agendamentos'], 'store');

        // echo json_encode($agendamentos);
        // echo json_encode($numAgendamento);
        // var_dump($stores);

        foreach ($agendamentos as $loja) {

            switch ($loja['repeticoes']) {
                case "semanal":

                    $target_date = $data['data_final'];
                    $current_date =  $data['data_inicio'];
                    $seconds_until_target = strtotime($target_date) -  strtotime($current_date);
                    $until_target = intval($seconds_until_target / 60 / 60 / 24 / 7);
                    for ($i = 0; $i <= $until_target; $i++) {
                        foreach ($loja['dias'] as $semana) {
                            if (date('l') == $semana) {
                                $dtmCurrentDate = new DateTime(date('l'));
                            } else {
                                $dtmCurrentDate = new DateTime(date('Y-m-d H:i:s', strtotime("next " . $semana)));
                            }
                            $dtmTargetDate = new DateTime(date($target_date));
                            $dateDb = $dtmCurrentDate->modify("+{$i} week")->format('Y-m-d H:i:s');
                            $dateDbTarget = $dtmTargetDate->format('Y-m-d H:i:s');

                            if ($dateDb <= $dateDbTarget) {
                                $body['rea_id_agendamento'] = $numAgendamento;
                                $body['rea_hora_ini'] =  $dateDb;
                                $body['rea_hora_fim'] = null;
                                $body['rea_usuario'] = null;
                                $body['rea_status'] = null;
                                $body['rea_id_cesta'] = $idCesta[0]->agen_numero;
                                $body['rea_agen_id'] = $loja['store'];
                                $this->Inventario_model->insere_realizacao($body);
                            }
                        }
                    }
                    break;

                case "mensal":
                    $dataInicio = $data['data_inicio'];
                    $dataFim = $data['data_final'];
                    
                    function datasDeMesEmMes($dataInicio, $dataFim, $diasSemana) {
                        $datas = array();
                        $dataAtual = $dataInicio;
                    
                        while ($dataAtual <= $dataFim) {
                            foreach ($diasSemana as $dia) {
                                $data = date('Y-m-d H:i:s', strtotime($dia . ' ' . $dataAtual));
                                if (strtotime($data) >= strtotime($dataAtual) && strtotime($data) <= strtotime($dataFim) && !in_array($data, $datas)) {
                                    $datas[] = $data;
                                }
                            }
                            $dataAtual = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($dataAtual)));
                        }
                    
                        return $datas;
                    }

                    // Chama a função para gerar as datas mensalmente
                    $datas = datasDeMesEmMes($dataInicio, $dataFim,$loja['dias']);

                    // Exibe as datas geradas
                    foreach ($datas as $data) {
                        
                        $body['rea_id_agendamento'] = $numAgendamento;
                        $body['rea_hora_ini'] =  $data;
                        $body['rea_hora_fim'] = null;
                        $body['rea_usuario'] = null;
                        $body['rea_status'] = null;
                        $body['rea_id_cesta'] = $idCesta[0]->agen_numero;
                        $body['rea_agen_id'] = $loja['store'];
                        $this->Inventario_model->insere_realizacao($body);
                    }    

                    break;

                case "quinzenal":
                    $dataInicio = $data['data_inicio'];
                    $dataFim = $data['data_final'];
                    
                    function datasDe15em15dias($dataInicio, $dataFim,$diasSemana) {
                        $datas = array();
                        $dataAtual = $dataInicio;
                        
                        while ($dataAtual <= $dataFim) {
                            foreach ($diasSemana as $dia) {
                                $data = date('Y-m-d H:i:s', strtotime($dia . ' ' . $dataAtual));
                                if (strtotime($data) >= strtotime($dataAtual) && strtotime($data) <= strtotime($dataFim) && !in_array($data, $datas)) {
                                    $datas[] = $data;
                                }
                            }
                            $dataAtual = date('Y-m-d H:i:s', strtotime("+15 days", strtotime($dataAtual)));
                        }
                        
                        return $datas;
                    }

                    // Chama a função para gerar as datas de 15 em 15 dias
                    $datas = datasDe15em15dias($dataInicio, $dataFim,$loja['dias']);

                    // Exibe as datas geradas
                    foreach ($datas as $data) {
                        
                        $body['rea_id_agendamento'] = $numAgendamento;
                        $body['rea_hora_ini'] =  $data;
                        $body['rea_hora_fim'] = null;
                        $body['rea_usuario'] = null;
                        $body['rea_status'] = null;
                        $body['rea_id_cesta'] = $idCesta[0]->agen_numero;
                        $body['rea_agen_id'] = $loja['store'];
                        $this->Inventario_model->insere_realizacao($body);
                    }                    

                    break;

                default:
                    foreach ($loja['dias'] as $semana) {
                        if (date('l') == $semana) {
                            $dtmCurrentDate = new DateTime(date('l'));
                        } else {
                            $dtmCurrentDate = new DateTime(date('Y-m-d H:i:s', strtotime("next " . $semana)));
                        }
                        $dtmTargetDate = new DateTime(date($target_date));
                        $dateDb = $dtmCurrentDate->format('Y-m-d H:i:s');
                        $dateDbTarget = $dtmTargetDate->format('Y-m-d H:i:s');

                        $body['rea_id_agendamento'] = $numAgendamento;
                        $body['rea_hora_ini'] =  $dateDb;
                        $body['rea_hora_fim'] = null;
                        $body['rea_usuario'] = null;
                        $body['rea_status'] = null;
                        $body['rea_id_cesta'] = $idCesta[0]->agen_numero;
                        $body['rea_agen_id'] = $loja['store'];
                        $this->Inventario_model->insere_realizacao($body);
                    }
                    break;
            }
        }
    } */

    public function saveRealizacao($bodyParams){

        //resolver problem no numagendamento a ser inserido na t_cestas
        //possivel bug ocorrera na hora de gerar os produtos na conferencia
        
        $agendamentos = $bodyParams['agendamentos'];
        $data = $bodyParams['data'];
        $numAgendamento = $bodyParams['numAgendamento'];
        $stores = array_column($bodyParams['agendamentos'], 'store');
        
        $divergencia = $data['divergencia'];
        $naoMovimentados = $data['naoMovimentados'];
        $falteiros = $data['falteiros'];
       
        foreach ($agendamentos as $loja) {

            switch ($loja['repeticoes']) {
                case "semanal":

                    $target_date = $data['data_final'];
                    $current_date =  $data['data_inicio'];
                    $seconds_until_target = strtotime($target_date) -  strtotime($current_date);
                    $until_target = intval($seconds_until_target / 60 / 60 / 24 / 7);
                    for ($i = 0; $i <= $until_target; $i++) {
                        foreach ($loja['dias'] as $semana) {
                            if (date('l') == $semana) {
                                $dtmCurrentDate = new DateTime(date('l'));
                            } else {
                                $dtmCurrentDate = new DateTime(date('Y-m-d H:i:s', strtotime("next " . $semana)));
                            }
                            $dtmTargetDate = new DateTime(date($target_date));
                            $dateDb = $dtmCurrentDate->modify("+{$i} week")->format('Y-m-d H:i:s');
                            $dateDbTarget = $dtmTargetDate->format('Y-m-d H:i:s');

                            if ($dateDb <= $dateDbTarget) {

                                //chamar nova funcao geraCesta
                                $id_nova_cesta = $this->geraCesta($divergencia,$naoMovimentados,$falteiros,$numAgendamento);

                                $body['rea_id_agendamento'] = $numAgendamento;
                                $body['rea_hora_ini'] =  $dateDb;
                                $body['rea_hora_fim'] = null;
                                $body['rea_usuario'] = null;
                                $body['rea_status'] = null;
                                $body['rea_id_cesta'] = $id_nova_cesta;
                                $body['rea_agen_id'] = $loja['store'];
                                $this->Inventario_model->insere_realizacao($body);
                            }
                        }
                    }
                    break;

                case "mensal":
                    $dataInicio = $data['data_inicio'];
                    $dataFim = $data['data_final'];
                    
                    function datasDeMesEmMes($dataInicio, $dataFim, $diasSemana) {
                        $datas = array();
                        $dataAtual = $dataInicio;
                    
                        while ($dataAtual <= $dataFim) {
                            foreach ($diasSemana as $dia) {
                                $data = date('Y-m-d H:i:s', strtotime($dia . ' ' . $dataAtual));
                                if (strtotime($data) >= strtotime($dataAtual) && strtotime($data) <= strtotime($dataFim) && !in_array($data, $datas)) {
                                    $datas[] = $data;
                                }
                            }
                            $dataAtual = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($dataAtual)));
                        }
                    
                        return $datas;
                    }

                    // Chama a função para gerar as datas mensalmente
                    $datas = datasDeMesEmMes($dataInicio, $dataFim,$loja['dias']);

                    // Exibe as datas geradas
                    foreach ($datas as $data) {
                        
                        //chamar nova funcao geraCesta
                        $id_nova_cesta = $this->geraCesta($divergencia,$naoMovimentados,$falteiros,$numAgendamento);

                        $body['rea_id_agendamento'] = $numAgendamento;
                        $body['rea_hora_ini'] =  $data;
                        $body['rea_hora_fim'] = null;
                        $body['rea_usuario'] = null;
                        $body['rea_status'] = null;
                        $body['rea_id_cesta'] = $id_nova_cesta;
                        $body['rea_agen_id'] = $loja['store'];
                        $this->Inventario_model->insere_realizacao($body);
                    }    

                    break;

                case "quinzenal":
                    $dataInicio = $data['data_inicio'];
                    $dataFim = $data['data_final'];
                    
                    function datasDe15em15dias($dataInicio, $dataFim,$diasSemana) {
                        $datas = array();
                        $dataAtual = $dataInicio;
                        
                        while ($dataAtual <= $dataFim) {
                            foreach ($diasSemana as $dia) {
                                $data = date('Y-m-d H:i:s', strtotime($dia . ' ' . $dataAtual));
                                if (strtotime($data) >= strtotime($dataAtual) && strtotime($data) <= strtotime($dataFim) && !in_array($data, $datas)) {
                                    $datas[] = $data;
                                }
                            }
                            $dataAtual = date('Y-m-d H:i:s', strtotime("+15 days", strtotime($dataAtual)));
                        }
                        
                        return $datas;
                    }

                    // Chama a função para gerar as datas de 15 em 15 dias
                    $datas = datasDe15em15dias($dataInicio, $dataFim,$loja['dias']);

                    // Exibe as datas geradas
                    foreach ($datas as $data) {
                        
                        //chamar nova funcao geraCesta
                        $id_nova_cesta = $this->geraCesta($divergencia,$naoMovimentados,$falteiros,$numAgendamento);

                        $body['rea_id_agendamento'] = $numAgendamento;
                        $body['rea_hora_ini'] =  $data;
                        $body['rea_hora_fim'] = null;
                        $body['rea_usuario'] = null;
                        $body['rea_status'] = null;
                        $body['rea_id_cesta'] = $id_nova_cesta;
                        $body['rea_agen_id'] = $loja['store'];
                        $this->Inventario_model->insere_realizacao($body);
                    }                    

                    break;

                default:
                    foreach ($loja['dias'] as $semana) {
                        if (date('l') == $semana) {
                            $dtmCurrentDate = new DateTime(date('l'));
                        } else {
                            $dtmCurrentDate = new DateTime(date('Y-m-d H:i:s', strtotime("next " . $semana)));
                        }
                        $dtmTargetDate = new DateTime(date($target_date));
                        $dateDb = $dtmCurrentDate->format('Y-m-d H:i:s');
                        $dateDbTarget = $dtmTargetDate->format('Y-m-d H:i:s');

                        //chamar nova funcao geraCesta
                        $id_nova_cesta = $this->geraCesta($divergencia,$naoMovimentados,$falteiros,$numAgendamento);

                        $body['rea_id_agendamento'] = $numAgendamento;
                        $body['rea_hora_ini'] =  $dateDb;
                        $body['rea_hora_fim'] = null;
                        $body['rea_usuario'] = null;
                        $body['rea_status'] = null;
                        $body['rea_id_cesta'] = $id_nova_cesta;
                        $body['rea_agen_id'] = $loja['store'];
                        $this->Inventario_model->insere_realizacao($body);
                    }
                    break;
            }
        }
    }
    
}

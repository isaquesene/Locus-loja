<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Agenda_adicionar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Agenda_model');
        $this->load->model('Coleta_model');
        $this->load->model('Filial_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 14; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}

        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        $this->load->library('Email');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_calendario');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{   
        $this->session->set_userdata('notas_coleta',[]);
        $this->session->userdata('age_origem','');
        $this->session->userdata('age_operacao','');
        $data['data_filial'] = $this->Filial_model->obtem_todas_filiais();
        $data['msg_status'] = '';
        $data['msg_texto'] = '';
        $this->load->view('agenda_add_select', $data);       
        
    }

    function verificar_notas(){
        if(!empty($this->session->userdata('age_origem'))  && !empty($this->session->userdata('age_origem'))){
            $age_origem = $this->session->userdata('age_origem');
            $age_operacao = $this->session->userdata('age_operacao');
        }
        if($_POST){
            $age_origem = $this->input->post('age_origem');
            $age_operacao = $this->input->post('age_operacao');
            $this->session->set_userdata('age_origem',$age_origem);
            $this->session->set_userdata('age_operacao',$age_operacao);
        }

        if(!$this->Coleta_model->obtem_dados_coleta_loja($age_origem)){
            $data['msg_status'] = 'ERRO';
            $data['msg_texto'] = 'A loja selecionada não possui paramêtro de coleta.';
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'agenda_adicionar', 'refresh');
            return;
        }
        $data['dados_coleta'] = $this->Coleta_model->obtem_dados_coleta_loja($age_origem);   
        $dias_semana = $this->Coleta_model->obtem_nomes_dia_semana_ingles();
        foreach($data['dados_coleta'] as $dados){
            $param_col_periodicidade	=  $dados->param_col_periodicidade;
            $param_col_dia				= $dados->param_col_dia;
        }
     
        $arrayDiasDeColeta = explode(",",preg_replace('/[\[\]]/', '', $param_col_dia));
        sort($arrayDiasDeColeta);
        $primeiroDia = $arrayDiasDeColeta[0];
        if($param_col_periodicidade == "SEMANAL"){
            $diaDaSemanaAtual = date('N');
            foreach($arrayDiasDeColeta as $dia){
                if($dia > $diaDaSemanaAtual){
                    $primeiroDia = $dia;
                }
            }
            $diaCompleto = new DateTime('now');
            $diaTraduzido = $dias_semana[$primeiroDia];
            $diaCompleto->modify('next '.$diaTraduzido);
        }else{
            $today = date('j');
            foreach($arrayDiasDeColeta as $dia){
                if($today >= $dia){
                    $diaCompleto = new DateTime();
                    $novoDiaColeta = mktime (0, 0, 0, date("m")+1, $primeiroDia,  date("Y"));
                }else{
                    $diaCompleto = new DateTime();
                    $novoDiaColeta = mktime (0, 0, 0, date("m"), $dia,  date("Y"));
                }
                $diaCompleto->setTimestamp($novoDiaColeta);
            }
        }
        if($this->session->userdata('notas_coleta') <> ""){
            $data['dados_nota'] = $this->session->userdata('notas_coleta');
        }
        $data['age_origem'] = $age_origem;
        $data['age_operacao'] = $age_operacao;
        $data['diaCompleto'] = date_format($diaCompleto, 'd/m/Y');
        $data['msg_status'] = '';
        $data['msg_texto'] = '';
        $this->load->view('agenda_adicionar', $data);    
    }

    function adicionar_notas(){
        $cbn_nota = $this->input->post('cbn_nota');
        $retorno =  $this->session->userdata('notas_coleta');
        if(strlen($cbn_nota) == 44){
            if(!$this->Agenda_model->verificaExistenciaNotaPorChave($cbn_nota)){
                $data['msg_status'] = 'ERRO';
                $data['msg_texto'] = 'A nota fiscal não existe na base de dados.';
                $this->session->set_flashdata('flash-data', $data);
                redirect(base_url(). 'agenda_adicionar/verificar_notas', 'refresh'); 
                return;
            }
            $retorno[] = $this->Agenda_model->verificaExistenciaNotaPorChave($cbn_nota);
            $this->session->set_userdata('notas_coleta',$retorno); 
            $data['msg_status'] = 'OK';
            $data['msg_texto'] = 'A nota fiscal foi vinculada a coleta.';
            $data['dados_nota'] = $this->session->userdata('notas_coleta');
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'agenda_adicionar/verificar_notas', 'refresh',$data);
            return;
        }
        if(!$this->Agenda_model->verificaExistenciaNotaPorID($cbn_nota)){
            $data['msg_status'] = 'ERRO';
            $data['msg_texto'] = 'A nota fiscal não existe na base de dados.';
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'agenda_adicionar/verificar_notas', 'refresh');
            return;
        }
            $retorno[] = $this->Agenda_model->verificaExistenciaNotaPorID($cbn_nota);
            $this->session->set_userdata('notas_coleta',$retorno); 
            $data['msg_status'] = 'OK';
            $data['msg_texto'] = 'A nota fiscal foi vinculada a coleta.';
            $data['dados_nota'] = $this->session->userdata('notas_coleta');
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'agenda_adicionar/verificar_notas', 'refresh',$data);
            return; 
        }

        function cadastrar(){
            $age_data = $this->input->post('age_data');
            $age_origem = $this->session->userdata('age_origem');
            $age_operacao = $this->session->userdata('age_operacao');

            $dadosAgenda = array
            (
                'age_data_inicio'	 => implode('-', array_reverse(explode('/', $age_data))),
                'age_data_final'	 => implode('-', array_reverse(explode('/', $age_data))),
                'age_origem' 	 => $age_origem,
                'age_operacao' => $age_operacao,
                'age_status' => 'PENDENTE'
            );

            if(!$agenf_id_agenda = $this->Agenda_model->criar_agenda_coleta($dadosAgenda)){
                $data['msg_status'] = 'ERRO';
                $data['msg_texto'] = 'Não foi possivel criar um agendamento de coleta';
                $this->session->set_flashdata('flash-data', $data);
                redirect(base_url(). 'agenda_adicionar/verificar_notas', 'refresh');
                return;
            }

            foreach($this->session->userdata('notas_coleta') as $dados){
                $dadosAgendaNf = array
                (
                    'agenf_id_agenda'	 => $agenf_id_agenda,
                    'agenf_nota_fiscal'	 => $dados[0]->cbn_num_nota,
                    'agenf_data_cadastro' 	 => date("Y-m-d H:i:s")	
                );

                if(!$this->Agenda_model->criar_agenda_coleta_nf($dadosAgendaNf)){
                    $data['msg_status'] = 'ERRO';
                    $data['msg_texto'] = 'Não foi possivel criar um agendamento de coleta';
                    $this->session->set_flashdata('flash-data', $data);
                    redirect(base_url(). 'agenda_adicionar/verificar_notas', 'refresh');
                    return;
                }
            }

            $data['msg_status'] = 'OK';
            $data['msg_texto'] = 'O agendamento foi criado com sucesso';
            $this->session->set_flashdata('flash-data', $data);
            redirect(base_url(). 'agenda_gerenciar', 'refresh');
        }
    

    }

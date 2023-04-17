<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Dashboard extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        //$this->load->model('model_1');
        //$this->load->model('Telas_model');
        $this->load->model('Chamados_model');
        $this->load->model('Coleta_model');
        $this->load->model('Conferencia_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');

		$this->_init();
	}

	private function _init()	{
		$this->output->set_template('tpl_home');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{

        $showAlertaInventarioAmanha = false;
        if (
            !empty($this->session->userdata('usu_codapo')) &&
            $this->Conferencia_model->inventarioAmanha($this->session->userdata('usu_codapo'))
        ) {
            $showAlertaInventarioAmanha = true;
        }

        /**
         * Utilizando a mesma função que lista os volumes para conferência
         * para não duplicar código e respeitar regras de negócio,
         * percorremos o resultado e comparamos com o parâmetro limite
         * informado no db. Caso algum volume tenha passado, exibirá alert no html
         */
        $showAlertaPrazoConferenciaVencido = false;
        if (!empty($this->session->userdata('usu_codapo'))) {
            $limiteConferencia = $this->Conferencia_model->parametro_conferencia_obrigatoria();
            $conferenciasInternas = $this->Conferencia_model->obtem_conferencias_filial( $this->session->userdata('usu_codapo'));
            if ($conferenciasInternas) {
                foreach ($conferenciasInternas as $item) {
                    if ($item->horas_da_entrega > $limiteConferencia) {
                        $showAlertaPrazoConferenciaVencido = true;
                    }
                }
                unset($item);
            }
            unset($conferenciasInternas);
            $conferenciasExternas = $this->Conferencia_model->buscar_conferencias( $this->session->userdata('usu_codapo'));
            if ($conferenciasExternas) {
                foreach ($conferenciasExternas as $item) {
                    if ($item->horas_da_entrega > $limiteConferencia) {
                        $showAlertaPrazoConferenciaVencido = true;
                    }
                }
                unset($item);
            }
            unset($conferenciasExternas);
        }

       $showAlertaChamadoVencido = false;
       if (
           in_array($this->session->userdata('usu_perfil'), [1, 3])
           && $this->Chamados_model->possui_chamados_pendentes_extra_limite_horas()
       ) {
           $showAlertaChamadoVencido = true;
       }
    
       $dadosParam = $this->Coleta_model->obtem_dados_coleta_loja($this->session->userdata('usu_codapo'));
       
       $showAlertaColeta = false;

		if($dadosParam)
		{
			foreach($dadosParam as $dados){

				$periodicidade = $dados->param_col_periodicidade;
				$arrayDia = explode(",",preg_replace('/[^0-9,]/', '', $dados->param_col_dia));

				if($periodicidade == 'SEMANAL'){
				if(in_array(date('N'),$arrayDia)){
				$showAlertaColeta = true;
				};
				}else{
					if(in_array(date('j'),$arrayDia)){
					$showAlertaColeta = true;
					}
				}
			}

		}

        $dados['showAlertaInventarioAmanha'] = $showAlertaInventarioAmanha;
        $dados['showAlertaPrazoConferenciaVencido'] = $showAlertaPrazoConferenciaVencido;
        $dados['showAlertaChamadoVencido'] = $showAlertaChamadoVencido;
        $dados['showAlertaColeta'] = $showAlertaColeta;
        $this->load->view('dashboard', $dados);
        
    }
	
	function logout()
    {
		$this->session->sess_destroy();
		redirect(base_url().'login', 'refresh');
    }
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Cron_prazo_recebimento extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Conferencia_model');

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');

        // Carregar HELPERS
        $this->load->helper('envioEmail');

		$this->_init();
	}

	private function _init()	{
		// $this->output->set_template('tpl_home');
		// $title       = 'LOCUS ONLINE';
        // $description = 'description';
        // $keywords    = 'keywords';
        // $this->output->set_common_meta($title, $description, $keywords);
        // //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		// //$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{

        $nfs = $this->Conferencia_model->verificar_conferencias_expiradas( $this->Conferencia_model->parametro_conferencia_obrigatoria() );

        if ($nfs) {
            foreach ($nfs as $nf) {
                $msg = 'O prazo para conferência da Nota Fiscal N.º '.$nf->cbn_num_nota.' foi excedido.';
                $destinatario = 'loja'.$nf->cbn_id_fil.'@farmaconde.com.br';
                // msgEmailPrazoRecebimento($destinatario, $msg);
                sleep(5);
            }
        }

        echo 'OK' . PHP_EOL;

	}
}
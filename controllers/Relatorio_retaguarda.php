<?php defined('BASEPATH') or exit('No direct script access allowed');


class Relatorio_retaguarda extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Inventario_model');
        $this->load->model('Regiao_model');

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
        $id = $this->input->post('id');

        $data['msg_status'] = '';
        $data['msg_texto'] = '';

        $data['agendamentos'] = $this->Inventario_model->obtem_agendamentos();

        $data['realizacoes'] = $this->Inventario_model->obtem_realizacoes_notnull();
        



        $this->load->view('relatorio_retaguarda', $data);
    
    }

}

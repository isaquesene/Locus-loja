<?php defined('BASEPATH') or exit('No direct script access allowed');


class Relatorio_loja extends CI_Controller
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
        $data['realizacoes'] = $this->Inventario_model->obtem_realizacoes_notnull();
        $data['agendamentos'] = $this->Inventario_model->obtem_agendamentos();
        //$cont = 0;
        // foreach ($arr['realizacoes'] as &$realizacao) {
        //     $realizacao[0]['reg_nome'] = 1127;
        // }
        // echo json_encode($data);
        //$array = $data['realizacoes'][0];
       // echo $array[0];
//        $data['relatorio_filtros'] = null;
/*
        if ($_POST) {
            $form_inventario     = $this->input->post('form_inventario');
            $form_data      = $this->input->post('form_data');
            $form_loja      = $this->input->post('form_loja');
            $form_status          = $this->input->post('form_status');
            $form_regional      = $this->input->post('form_regional');
            $this->session->set_userdata('relatorio_filtros', [
                'form_inventario' => $form_inventario,
                'form_data' => $form_data,
                'form_loja' => $form_loja,
                'form_status' => $form_status,
                'form_regional' => $form_regional,
            ]);
            $data['relatorio_filtros'] = $this->session->userdata('relatorio_filtros');
        } elseif ( null !== $relatorio_filtros = $this->session->userdata('relatorio_filtros') ) {
            $form_inventario = $relatorio_filtros['form_inventario'];
            $form_data = $relatorio_filtros['form_data'];
            $form_loja = $relatorio_filtros['form_loja'];
            $form_status = $relatorio_filtros['form_status'];
            $form_regional = $relatorio_filtros['form_regional'];
            $data['relatorio_filtros'] = $relatorio_filtros;

        } else {
            $form_inventario = '';
            $form_data = '';
            $form_loja = '';
            $form_status = '';
            $form_regional = '';
            
        }

        //$form_data   = implode('-', array_reverse(explode('/', $form_data)));

       // $data['filtro'] = $this->Inventario_model->obtem_inventario_filtro($form_inventario, $form_data, $form_loja, $form_status, $form_regional);
        */
        $this->load->view('relatorio_loja', $data);
        
    }

}

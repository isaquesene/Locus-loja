<?php defined('BASEPATH') or exit('No direct script access allowed');


class Inventario extends CI_Controller
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
        $title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
        //$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
    }

    public function index()
    {

        $data['agendamentos'] = $this->Inventario_model->obtem_agendamentos();

        // echo json_encode($data);

        $identificador = $this->Inventario_model->obtem_id();
        
        $id = array_column($identificador, 'agen_id');        
        
        foreach ($id as $ids) {

            $tipo_template = $this->Inventario_model->obtem_template($ids);

            $data['produtos'] = $this->Inventario_model->obtem_produto($ids);

            foreach ($data['agendamentos'] as &$agendamento) {
                if ($agendamento->agen_id == $ids) {
                    $agendamento->tipo_template = $tipo_template;
                    break;
                }
            }
        }
        $this->load->view('inventario', $data);
    }

    public function delete()
    {

        $id = $this->input->post('id');

        $this->Inventario_model->delete_agendamento($id);

        $data['msg_status'] = 'Registro Excluido com sucesso';
        $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

        redirect(base_url() . 'Inventario', 'refresh');
    }


}

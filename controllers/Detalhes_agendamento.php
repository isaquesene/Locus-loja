<?php defined('BASEPATH') or exit('No direct script access allowed');


class Detalhes_agendamento extends CI_Controller
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
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];;

        $path = parse_url($url, PHP_URL_PATH);

        $id = basename($path);

        $data['agendamentos'] = $this->Inventario_model->obtem_agendamentos_id($id);

        $loja = $data['agendamentos'][0]->agen_id_loja;

        $data['template'] = $this->Inventario_model->obtem_template($id);

        $data['produtos'] = $this->Inventario_model->obtem_produto($id);

        foreach ($data['produtos'] as $produto) {

            // Obter o tipo de produto
            $tipo_produto = $this->Inventario_model->obtem_produto_tipo($produto->pro_cod_pro_cli,$loja);
            // Adicionar o tipo de produto ao objeto produto
            $produto->tipo_produto = ($tipo_produto) ? $tipo_produto[0]->ORIGEM : 'Manual';
        }
        $this->load->view('detalhes_agendamento', $data);
    }

    public function insere_produto()
    {

        $id = $_POST['id'];

        $idProduto = $_POST['produto'];

        if ($this->Inventario_model->verifica_produto($idProduto) == false) {

            redirect(base_url() . 'detalhes_agendamento/' . $id . '', 'refresh');
        } elseif ($this->Inventario_model->verificarCamposPreenchidos() == true) {

            $data['msg_status'] = 'Limite de 15 Produtos Atingidos';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'error']);

            redirect(base_url() . 'detalhes_agendamento/' . $id . '', 'refresh');
        } else {
            $this->Inventario_model->insere_produto_detalhes($id, $idProduto);
            $data['msg_status'] = 'Registro incluído com sucesso';
            $data['msg_texto'] = '';

            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

            redirect(base_url() . 'detalhes_agendamento/' . $id . '', 'refresh');
        }
    }
    public function delProdutos()
    {
        $agen_id = $_POST['id'];

        if (isset($_POST['scales']) && !empty($_POST['scales'])) {
            foreach ($_POST['scales'] as $id) {
                if ($this->Inventario_model->remove_produto($agen_id, $id)) {

                    $data['msg_status'] = 'Registro Excluido com sucesso';
                    $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);
                }
            }
        }

        $data['msg_status'] = 'Selecione um produto para excluir';
        $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'error']);

        redirect(base_url() . 'detalhes_agendamento/' . $agen_id . '', 'refresh');
    }
}

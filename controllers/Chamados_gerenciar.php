<?php defined('BASEPATH') or exit('No direct script access allowed');


class Chamados_gerenciar extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Chamados_model');
        $this->load->model('Telas_model');
        $this->load->model('Filial_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 20; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');
        //$this->load->library('library_2');
        //$this->load->library('library_3');

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
        // if(!$_POST){
        //     $data['msg_status']   = '';
        //     $data['msg_texto']    = '';  

        //     $this->load->view('chamados_gerenciar', $data);
        // }else{
           $this->buscar_chamados();
        // }
    }

    private function buscar_chamados()
    {
        $data['msg_status'] = '';
        $data['msg_texto'] = '';
        $data['msg_texto'] = '';
        $data['chamados_filtros'] = null;

        if ($_POST) {
            $form_data_inicio     = $this->input->post('form_data_inicio');
            $form_data_final      = $this->input->post('form_data_final');
            $form_status          = $this->input->post('form_status');
            $form_supervisor      = $this->input->post('form_supervisor');
            $this->session->set_userdata('chamados-filtros', [
                'form_data_inicio' => $form_data_inicio,
                'form_data_final' => $form_data_final,
                'form_status' => $form_status,
                'form_supervisor' => $form_supervisor,
            ]);
            $data['chamados_filtros'] = $this->session->userdata('chamados-filtros');
        } elseif ( null !== $chamadosFiltros = $this->session->userdata('chamados-filtros') ) {
            $form_data_inicio = $chamadosFiltros['form_data_inicio'];
            $form_data_final = $chamadosFiltros['form_data_final'];
            $form_status = $chamadosFiltros['form_status'];
            $form_supervisor = $chamadosFiltros['form_supervisor'];
            $data['chamados_filtros'] = $chamadosFiltros;
        } else {
            $form_data_inicio = '';
            $form_data_final = '';
            $form_status = '';
            $form_supervisor = '';
        }

        // CONVERTANDO A DATA DE FORMATO BRASILEIRO PARA AMERICANO
        $form_data_inicio   = implode('-', array_reverse(explode('/', $form_data_inicio)));
        $form_data_final    = implode('-', array_reverse(explode('/', $form_data_final)));
        
        if($this->session->userdata('usu_perfil') == 4){
            $loja  = $this->session->userdata('usu_codapo');
            $data['dados_chamados'] = $this->Chamados_model->obtem_todos_chamados_loja($form_data_inicio, $form_data_final, $form_status, $loja);
        }else{
            $data['dados_chamados'] = $this->Chamados_model->obtem_todos_chamados($form_data_inicio, $form_data_final, $form_status, $form_supervisor);
        }

        // Supervisores
        $data['supervisores'] = $this->Filial_model->obtem_lista_regionais();
        
        $this->load->view('chamados_gerenciar', $data);
    }
}

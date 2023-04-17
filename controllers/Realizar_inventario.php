<?php defined('BASEPATH') or exit('No direct script access allowed');


class Realizar_inventario extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Configurar fuso horário
        setlocale(LC_ALL, 'pt_BR', 'ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
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
        $id = ($_SESSION['usu_id']);

        $perfil = ($_SESSION['usu_perfil']);

        if (!in_array($perfil, array(1, 2))) {
            $lojaUser = $this->Inventario_model->obtem_loja_usu($id);
            $data['agendamentos'] = $this->Inventario_model->obtem_inv($lojaUser[0]->usu_cod_apontamento);

            
            $data['realizacoes'] = array();
            
            if ($data['agendamentos']) {
                
                $realizacoes = $this->Inventario_model->obtem_cinco_proximas_realizacoes_por_agendamento($lojaUser[0]->usu_cod_apontamento, $data['agendamentos'][0]->agen_id);
                
                foreach ($realizacoes as $rea) {
                    $total_items = $this->Inventario_model->conta_items($rea->rea_id)[0]->total_items;
                    $rea->total_items = $total_items;
                    array_push($data['realizacoes'], $rea);
                }
                
            }
        }


        $this->load->view('realizar_inventario', $data);
    }




    public function realizarInventario()
    {
        $data = $_POST;
        $param['con_id_agendamento'] = $data['agen_num'];
        $param['con_id_realizacao'] = $data['rea_num'];
        unset($data['agen_num']);
        unset($data['rea_num']);

        if ($dtmAtual > $dtmLimite) {
            $realizacao[0]->rea_status = "INCOMPLETO";

            $this->Inventario_model->update_realizacao($realizacao[0], $param['con_id_realizacao']);
            $data['msg_status'] = 'Horário Limite ultrapassado';
            $data['msg_texto'] = 'O horário limite para o inventário foi ultrapassado!';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'error']);
        } else {
            foreach ($data as $k => $prod) {
                if ($prod != '') {
                    $this->Inventario_model->atualiza_contagem($param['con_id_realizacao'], $k, 'con_pro_contagem', $prod);
                } else {
                    $this->Inventario_model->atualiza_contagem($param['con_id_realizacao'], $k, 'con_pro_contagem', null);
                }
            }
            $realizacao = $this->Inventario_model->obtem_realizacao_por_id($param['con_id_realizacao']);
            $newTime = new DateTime();
            $dateDb = $newTime->format('Y-m-d H:i:s');
            $realizacao[0]->rea_hora_fim = $dateDb;

            $agendamento = $this->Inventario_model->obtem_agendamento_por_realizacao($param['con_id_agendamento']);
            unset($realizacao[0]->rea_id);
            $dtmAtual = date("H:i:s");
            $dtmLimite = date("H:i:s", strtotime($agendamento[0]->agen_tempo_limite));
            $realizacao[0]->rea_status = "REALIZADO";
            $this->Inventario_model->update_realizacao($realizacao[0], $param['con_id_realizacao']);

            $data['msg_status'] = 'Sucesso';
            $data['msg_texto'] = 'Inventário cadastrado com sucesso';
            $this->session->set_flashdata('flash-alert', [$data['msg_status'], $data['msg_texto'], 'success']);

            redirect(base_url() . 'realizar_inventario', 'refresh');
        }
    }
}

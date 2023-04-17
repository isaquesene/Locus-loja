<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Nf_consultar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Nf_model');
        $this->load->model('Usuarios_model');
        $this->load->model('Telas_model');
        $this->load->model('Filial_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 11; //Cod. da tela - Tabela t_telas
        $nivel = $this->session->userdata('usu_perfil');

        if($this->Telas_model->permissao_acesso_tela($tela, $nivel) == FALSE)
            {redirect(base_url().'acesso_negado', 'refresh');}
        
        //-----------------------------------------------------------------------------------------------

        // Carregar BIBLIOTECAS utilizadas
        //$this->load->library('library_1');
        //$this->load->library('library_2');
        //$this->load->library('library_3');

        // Carregar HELPERS
        $this->load->helper('montaxml');

		$this->_init();
	}

	private function _init()	{
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
        // if(!$_POST)
        // {
        //     $data['msg_status']   = '';
        //     $data['msg_texto']    = '';

        //     $data['lista_regionais'] = $this->Filial_model->obtem_lista_regionais();
        //     $data['lista_lojas']     = $this->Filial_model->obtem_todas_filiais();
        //     $this->load->view('nf_consultar', $data);
        // }
        // else
        // {
            $this->consultar_nfs();
        // }
    }

    private function isAdm() {
        return ( $this->session->userdata('usu_perfil') <= 2 );
    }

    private function consultar_nfs()
    {

        $page = $_SERVER['HTTP_REFERER'];
        $data['isAdm'] = $isAdm = $this->isAdm();
        
        $data['msg_status']         = '';
        $data['msg_texto']          = '';
        $data['lista_regionais']    = $this->Filial_model->obtem_lista_regionais();
        $data['lista_lojas']        = $this->Filial_model->obtem_todas_filiais();
        $data['notas_filtros']      = null;
        $data['loja_usu']           = $this->session->userdata()["usu_codapo"];


        if ($_POST) {
            $form_data_inicio   = $this->input->post('form_data_inicio');
            $form_data_final    = $this->input->post('form_data_final');
            $form_data_entrega_inicio   = $this->input->post('form_data_entrega_inicio');
            $form_data_entrega_final   = $this->input->post('form_data_entrega_final');
            $form_cod_prod   = $this->input->post('form_cod_prod');
            $form_ean_prod   = $this->input->post('form_ean_prod');
            $form_desc_prod   = $this->input->post('form_desc_prod');
            $form_num_nf   = $this->input->post('form_num_nf');
            $form_emitente   = $this->input->post('form_emitente');
            $form_status_nf     = $this->input->post('form_status_nf');
            $form_tipo_filtro_nf   = (!$isAdm) ? 'LOJA' : $this->input->post('form_tipo_filtro_nf');
            $form_regional_nf      = $this->input->post('form_regional_nf');
            $form_loja_nf      = (!$isAdm && $form_tipo_filtro_nf == 'LOJA') ? $this->session->userdata('usu_codapo') : $this->input->post('form_loja_nf');
            $this->session->set_userdata('notas-filtros', [
                'form_data_inicio' => $form_data_inicio,
                'form_data_final' => $form_data_final,
                'form_data_entrega_inicio' => $form_data_entrega_inicio,
                'form_data_entrega_final' => $form_data_entrega_final,
                'form_cod_prod' => $form_cod_prod,
                'form_ean_prod' => $form_ean_prod,
                'form_desc_prod' => $form_desc_prod,
                'form_num_nf' => $form_num_nf,
                'form_emitente' => $form_emitente,
                'form_status_nf' => $form_status_nf,
                'form_tipo_filtro_nf' => $form_tipo_filtro_nf,
                'form_regional_nf' => $form_regional_nf,
                'form_loja_nf' => $form_loja_nf,
            ]);
            $data['notas_filtros'] = $this->session->userdata('notas-filtros');
        } 
        // elseif(($notasFiltros = $this->session->userdata('notas-filtros')) !== null and strpos($page,'nf_consultar') !== false ){  
        //         $form_data_inicio   = $notasFiltros['form_data_inicio'];
        //         $form_data_final    = $notasFiltros['form_data_final'];
        //         $form_data_entrega_inicio    = $notasFiltros['form_data_entrega_inicio'];
        //         $form_data_entrega_final    = $notasFiltros['form_data_entrega_final'];
        //         $form_cod_prod    = $notasFiltros['form_cod_prod'];
        //         $form_ean_prod    = $notasFiltros['form_ean_prod'];
        //         $form_desc_prod    = $notasFiltros['form_desc_prod'];
        //         $form_num_nf    = $notasFiltros['form_num_nf'];
        //         $form_emitente    = $notasFiltros['form_emitente'];
        //         $form_status_nf     = $notasFiltros['form_status_nf'];
        //         $form_tipo_filtro_nf   = (!$isAdm) ? 'LOJA' : $notasFiltros['form_tipo_filtro_nf'];
        //         $form_regional_nf      = $notasFiltros['form_regional_nf'];
        //         $form_loja_nf      = (!$isAdm && $form_tipo_filtro_nf == 'LOJA') ? $this->session->userdata('usu_codapo') : $notasFiltros['form_loja_nf'];
        //         $data['notas_filtros'] = $notasFiltros;
                
        //     }
            
        
        else {
            $form_data_inicio   = '';
            $form_data_final    = '';
            $form_data_entrega_inicio    = '';
            $form_data_entrega_final    = '';
            $form_cod_prod    = '';
            $form_ean_prod    = '';
            $form_desc_prod    = '';
            $form_num_nf    = '';
            $form_emitente    = '';
            $form_status_nf     = '';
            $form_tipo_filtro_nf   = (!$isAdm) ? 'LOJA' : '';
            $form_regional_nf      = '';
            $form_loja_nf      = '';
            if (!$isAdm) {
                $data['notas_filtros']['form_tipo_filtro_nf'] = 'LOJA';
                $data['notas_filtros']['form_loja_nf'] = $this->session->userdata('usu_codapo');
           }
        }
		
		// CONVERTANDO A DATA DE FORMATO BRASILEIRO PARA AMERICANO
        $form_data_inicio   = implode('-', array_reverse(explode('/', $form_data_inicio)));
        $form_data_final    = implode('-', array_reverse(explode('/', $form_data_final)));
        $data['dados_nf']           = $this->Nf_model->obtem_nf_filtro($form_data_inicio, $form_data_final, $form_status_nf, $form_tipo_filtro_nf, $form_regional_nf, $form_loja_nf, $form_data_entrega_inicio, $form_data_entrega_final, $form_cod_prod, $form_ean_prod, $form_desc_prod, $form_num_nf, $form_emitente);
        $data['total_faturada']     = $this->Nf_model->obtem_soma_filtro($form_data_inicio, $form_data_final, 'FATURADA');
        $data['total_em_transito']  = $this->Nf_model->obtem_soma_filtro($form_data_inicio, $form_data_final, 'EM TRANSITO');
        $data['total_em_embarque']  = $this->Nf_model->obtem_soma_filtro($form_data_inicio, $form_data_final, 'EM EMBARQUE');        
        $data['total_entregue']  = $this->Nf_model->obtem_soma_filtro_entregue($form_data_entrega_inicio, $form_data_entrega_final, 'ENTREGUE');

        $usuario = $this->Usuarios_model->obtem_dados_usuario_unico($_SESSION['usu_id']);
        $data['perfil_usuario'] = $usuario[0]->usu_id_per;
        $this->load->view('nf_consultar', $data);



    }

    /**
     * Exibe detalhes de uma NF
     */


    public function detalhes($id_cbn)
    {
        $valida = ($_SERVER["REQUEST_URI"]) ;
        $data['detalhes_nf'] = $this->Nf_model->obtem_detalhes_nota($id_cbn);
        $data['itens_nf'] = $this->Nf_model->obtem_detalhes_itens_nota($id_cbn);
        $data['id_cbn'] = $id_cbn;
        
        
        $this->load->view('nf_consultar_detalhes', $data);

    


    }

    /**
     * Exibe a NF 
     */
    public function verNota($id_cbn){
        $current_url = "https://locus.net.br";
        //$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        header('Content-Type: application/pdf');
        readfile($current_url.'/danfe/?id='.$id_cbn.'&tk=f2da5d3220600fc2feabebee816ac130a03ae719cda916db01042ac9ca60eb04588bdb6b4e835983102ae39eb87d01006a8b4ed4cadf32f58095f0e3c1af27f6');
        exit;
    }
	

}

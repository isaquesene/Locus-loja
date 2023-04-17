<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Telas_adicionar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Usuarios_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 32; //Cod. da tela - Tabela t_telas
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

	private function _init()	{
		$this->output->set_template('tpl_formulario');
		$title       = 'LOCUS ONLINE';
        $description = 'description';
        $keywords    = 'keywords';
        $this->output->set_common_meta($title, $description, $keywords);
        //$this->load->css(base_url().'assets/themes/default/css/arquivo.css');
		//$this->load->js(base_url().'assets/themes/default/js/arquivo.js');
	}    
        
	public function index()
	{
        $data['msg_status']   = '';
        $data['msg_texto']    = '';  

        $data['lista_perfis']  = $this->Usuarios_model->obtem_todos_perfis();
        $data['lista_modulos'] = $this->Telas_model->obtem_todos_modulos();
        $data['lista_menus']   = $this->Telas_model->obtem_todos_menus();
		$this->load->view('telas_adicionar', $data);
    }

    public function adicionar()
	{
        $dados = array
		(
            'tel_descricao'  => $this->input->post('tel_descricao'),
            'tel_link'       => $this->input->post('tel_link'),
            'tel_modulo'     => $this->input->post('tel_modulo'),
            'tel_menu'       => $this->input->post('tel_menu'),
		);

        if($this->Telas_model->adicionar_tela($dados) == TRUE)
        {
            $tel_id = $this->Telas_model->obtem_ultimo_id_tela();
            $erro   = FALSE;
            $array_perfis = $this->input->post('tel_array_perfil');

            for ($i=0;$i<count($array_perfis);$i++)
            {
                $erro = $this->Telas_model->adicionar_tela_perfil($tel_id, $array_perfis[$i]);

            }

            if($erro == FALSE)
            {
                $data['msg_status']   = 'OK';
                $data['msg_texto']    = 'Tela adicionada com sucesso. Clique <a href="' . base_url() . 'telas_gerenciar"><strong>AQUI</strong></a> para voltar a tela anterior.';  
        
                $data['lista_perfis']  = $this->Usuarios_model->obtem_todos_perfis();
                $data['lista_modulos'] = $this->Telas_model->obtem_todos_modulos();
                $data['lista_menus']   = $this->Telas_model->obtem_todos_menus();
                $this->load->view('telas_adicionar', $data);
            }
            else
            {
                $data['msg_status']   = 'ERRO';
                $data['msg_texto']    = 'Erro ao vincular tela a perfis selecionados.';  
        
                $data['lista_perfis']  = $this->Usuarios_model->obtem_todos_perfis();
                $data['lista_modulos'] = $this->Telas_model->obtem_todos_modulos();
                $data['lista_menus']   = $this->Telas_model->obtem_todos_menus();
                $this->load->view('telas_adicionar', $data);
            }


        }
        else
        {
            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Erro ao adicionar tela.';  
    
            $data['lista_perfis']  = $this->Usuarios_model->obtem_todos_perfis();
            $data['lista_modulos'] = $this->Telas_model->obtem_todos_modulos();
            $data['lista_menus']   = $this->Telas_model->obtem_todos_menus();
            $this->load->view('telas_adicionar', $data);
        }
    }

}

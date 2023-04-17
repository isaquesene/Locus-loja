<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Semc_exportar extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();

        // Configurar fuso horário
        setlocale (LC_ALL, 'pt_BR','ptb');
        date_default_timezone_set('America/Sao_Paulo');

        // Carregar MODELOS utilizados
        $this->load->model('Nf_model');
        $this->load->model('Telas_model');

        //-----------------------------------------------------------------------------------------------
        // Se usuário não estiver logado ou sessão expirou, redireciona para pagina principal 
        if(!$this->session->userdata('loggedin'))
            {redirect(base_url().'login', 'refresh');}

        // Se usuário não tiver permissão para acesso a pagina, redireciona para pagina principal
        $tela  = 19; //Cod. da tela - Tabela t_telas
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
        if(!$_POST){

            $data['msg_status']   = '';
            $data['msg_texto']    = '';  

            //$data['dados_grid'] = $this->Nf_model->exportar_arquivo_semc();
            $this->load->view('semc_exportar', $data);

        } else {

            $this->exportar_semc();
        
        }
        
    }
    

    private function exportar_semc(){

        $data['msg_status']   = '';
        $data['msg_texto']    = '';  
        
        $form_data    = $this->input->post('form_data');
				
		// CONVERTANDO A DATA DE FORMATO BRASILEIRO PARA AMERICANO
        $form_data  = implode('-', array_reverse(explode('/', $form_data)));       
        
        $listar_dados = $this->Nf_model->exportar_arquivo_semc($form_data);
    
		if($listar_dados){

            // /*Função fopen usada para abrir arquivo, ou seja, joga-lo na memória do servidor, neste caso o arquivo ainda não existe.
			// o “w” quer dizer write, que o arquivo pode ser escrito */

			$caminho = dirname(__FILE__) . "\\arquivo\\exportar\\";
            $form_data = date("dmY_Hms");
            $nome_arquivo = "arquivo_".$form_data.".txt";

            echo $caminho;

            $arquivo = fopen($caminho.$nome_arquivo, 'w+');

			foreach($listar_dados as $dados){

                
                $tipo_registro      = $dados->semc_flag;
                $data_movimento     = $dados->semc_data_movimentacao;
                $nota_fiscal        = $dados->semc_num_nf;
                $fornecedor         = str_replace("." , "" , $dados->semc_num_cnpj);
                $fornecedor         = str_replace("/" , "" , $fornecedor);
                $fornecedor         = str_replace("-" , "" , $fornecedor);
                $produto_id         = $dados->semc_cod_pro_cli;
                $produto_qtd        = $dados->semc_qtd_produto;
                $produto_desc       = $dados->semc_desc_produto;
                $dt_emissao_nf      = date("d/m/Y", strtotime($dados->semc_data_nf));
                $numero_lote        = $dados->semc_num_lote;
                $data_lote          = date("m/Y", strtotime($dados->semc_validade_lote));
                $cod_interno        = '1';
                $cod_local          = '1';
                $tipo_pessoa        = 'PJ';

			
                $codTipoRegistro    = (string) str_pad($tipo_registro, 3, '"', STR_PAD_BOTH);
                $codDataMovimento   = (string) str_pad($data_movimento, 10, '0', STR_PAD_LEFT);
                $codNotaFiscal      = (string) str_pad($nota_fiscal, 10, '0', STR_PAD_LEFT);
                $codFornecedor      = (string) str_pad($fornecedor, 14, ' ', STR_PAD_LEFT);
                $codProdutoId       = (string) str_pad($produto_id, 10, '0', STR_PAD_LEFT);
                $codPodutoQtd       = (string) str_pad($produto_qtd, 6, '0', STR_PAD_LEFT);
                $codPodutoDesc      = (string) str_pad($produto_desc, 30, '"', STR_PAD_BOTH);
                $codDtEmissaoNf     = (string) str_pad($dt_emissao_nf, 10, ' ', STR_PAD_RIGHT);
                $codNumeroLote      = (string) str_pad($numero_lote, 30, ' ', STR_PAD_LEFT);
                $codDataLote        = (string) str_pad($data_lote, 30, ' ', STR_PAD_LEFT);
                $codCodInterno      = (string) str_pad($cod_interno, 25, ' ', STR_PAD_RIGHT);
                $codCodLocal        = (string) str_pad($cod_local, 10, ' ', STR_PAD_RIGHT);
                $codTipoPessoa      = (string) str_pad($tipo_pessoa, 2, ' ', STR_PAD_RIGHT);
                
                
                $texto = $codTipoRegistro.',' . $codDataMovimento.',' . $codNotaFiscal.',' . $codFornecedor.',' . $codProdutoId.',' . $codPodutoQtd.',' 
                . $codPodutoDesc.',' . $codDtEmissaoNf.',' . $codNumeroLote.',' . $codDataLote.',' . $codCodInterno.',' . $codCodLocal.',' . $codTipoPessoa. "\r\n";
                
                /*a função fwrite escreve o valor da variável $texto no arquivo.txt se o arquivo não existe o php cria o arquivo*/
                fwrite($arquivo, $texto);
			}	
            
            fwrite($arquivo, '999,99/99/9999.');
			/*a função fclose retira o arquivo.txt da memória o servidor*/
			fclose($arquivo);

			// Configuramos os headers que serão enviados para o browser
            //header('Content-Disposition: attachment; filename="'.$nome_arquivo.'"');            
            //header('Content-Type: application/octet-stream');            
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($nome_arquivo));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($nome_arquivo));
            ob_clean();
            //flush(); -- 
            readfile($caminho.$nome_arquivo);
            //
            unlink($caminho.$nome_arquivo);
            exit;
        
			
			// Envia o arquivo para o cliente
            //readfile($caminho.$nome_arquivo);

                        
            $data['msg_status']   = 'OK';
            $data['msg_texto']    = 'Arquivo gerado com sucesso!'; 
            
            $this->load->view('semc_exportar', $data);
			
		} else {

            $data['msg_status']   = 'ERRO';
            $data['msg_texto']    = 'Não há notas de conferências no período informado!'; 
            
            $this->load->view('semc_exportar', $data);

        }

    }
	

}

<?php
class Nf_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: vw_buscar_notas_fiscais 
     */
    function obtem_nf_filtro($form_data_inicio, $form_data_final, $form_status_nf, $form_tipo_filtro, $form_regional, $form_num_loja, $form_data_entrega_inicio, $form_data_entrega_final, $form_cod_prod, $form_ean_prod, $form_desc_prod, $form_num_nf, $form_emitente)
    {
       
        $this->db->from('vw_buscar_notas_fiscais');
        $this->db->where("data_emissao BETWEEN '" . $form_data_inicio . "' AND '" . $form_data_final . "'");
        $this->db->limit(1);

        // Filtro Data Entrega
        if($form_data_entrega_inicio != '' and $form_data_entrega_final != ''){
            $this->db->where("data_entrega BETWEEN '" . $form_data_entrega_inicio . "' AND '" . $form_data_entrega_final . "'");
        }

        // Filtro status
        if ($form_status_nf != "") {
            if($form_status_nf != 'FATURADA'){
                $this->db->where('vw_buscar_notas_fiscais.status', $form_status_nf);
            }
            else{
                $nota_status = array('FATURADA', 'PENDENTE AUDITORIA', 'AUDITADA');

                $this->db->where_in('vw_buscar_notas_fiscais.status', $nota_status);
            }
        }

        // Filtro Supervisor e Loja
        if($form_num_loja == 'Geral'){

        } else if ($form_tipo_filtro == 'REGIONAL' and $form_regional != '') {
            $this->db->where('vw_buscar_notas_fiscais.Supervisor', $form_regional);
        } else if ($form_tipo_filtro == 'LOJA' and $form_num_loja != '') {
            $this->db->where('vw_buscar_notas_fiscais.destinatario', $form_num_loja);
        } 

        // Filtro Código Produto
        if($form_cod_prod != ''){
            $this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = vw_buscar_notas_fiscais.id_nota', 'INNER');
            $this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
            $this->db->where('t_produtos.pro_cod_pro_cli', $form_cod_prod);
        }

        // Filtro EAN Produto
        if($form_ean_prod != ''){
            $this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = vw_buscar_notas_fiscais.id_nota', 'INNER');
            $this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
            $this->db->join('t_eans', 't_eans.ean_id_pro = t_produtos.pro_id', 'INNER');
            $this->db->where('t_eans.ean_cod', $form_ean_prod);
        }

        // Filtro Descrição Produto
        if($form_desc_prod != ''){
            $this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = vw_buscar_notas_fiscais.id_nota', 'INNER');
            $this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
            $this->db->like('t_produtos.pro_descricao', $form_desc_prod);
        }


        // Filtro Número NF
        if($form_num_nf != ''){
            $this->db->where('vw_buscar_notas_fiscais.nota_fiscal', $form_num_nf);
        }

        // Filtro Emitente
        if($form_emitente != ''){
            $this->db->like(' vw_buscar_notas_fiscais.emitente', $form_emitente);
        }

        $query = $this->db->get();
        

        echo $this->db->last_query();

        if ($query->num_rows() > 0) {

            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_cabecalho_notas
     */
    function obtem_soma_filtro($form_data_inicio, $form_data_final, $form_status_nf)
    {
        $sql = "SELECT CAB.Total AS total,
                        COALESCE(CAB.Valor, 0) AS valor,
                        COALESCE(CAST(CAB.Total * 100 / NULLIF((SELECT CAST(COUNT(*) AS NUMERIC(18,5))
                        FROM dbo.t_cabecalho_notas
                        WHERE cbn_data_emissao_emissao BETWEEN '" . $form_data_inicio . "' AND '" . $form_data_final . "'),0) AS NUMERIC(15,3)), 0) AS porcentagem
                FROM
                (SELECT COUNT(*) AS total, 
                        SUM(cbn_vlr_nota) AS valor
                FROM dbo.t_cabecalho_notas
                WHERE cbn_data_emissao BETWEEN '" . $form_data_inicio . "' AND '" . $form_data_final . "' 
                AND cbn_status = '" . $form_status_nf . "') AS CAB ";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    function obtem_soma_filtro_entregue($form_data_entrega_inicio, $form_data_entrega_final, $form_status_nf)
    {
        $sql = "SELECT CAB.Total AS total,
                        COALESCE(CAB.Valor, 0) AS valor,
                        COALESCE(CAST(CAB.Total * 100 / NULLIF((SELECT CAST(COUNT(*) AS NUMERIC(18,5))
                        FROM dbo.t_cabecalho_notas
                        LEFT OUTER JOIN dbo.t_transportes AS T ON T.tra_id_nota_cab = cbn_id
                        WHERE T.tra_chegada BETWEEN '" . $form_data_entrega_inicio . "' AND '" . $form_data_entrega_final . "'),0) AS NUMERIC(15,3)), 0) AS porcentagem
                FROM
                (SELECT COUNT(*) AS total, 
                        SUM(cbn_vlr_nota) AS valor
                FROM dbo.t_cabecalho_notas
                LEFT OUTER JOIN dbo.t_transportes AS T ON T.tra_id_nota_cab = cbn_id
                WHERE T.tra_chegada BETWEEN '" . $form_data_entrega_inicio . "' AND '" . $form_data_entrega_final . "' 
                AND cbn_status = '" . $form_status_nf . "') AS CAB ";

        $query = $this->db->query($sql);

        //echo($this->db->last_query() . '<br />');

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| Exportar Arquivo SEMC - XML
     *| Tabela: t_recebimento_semc
     */
    function exportar_arquivo_semc($form_data)
    {
        $this->db->where('semc_data_movimentacao', $form_data);

        $query = $this->db->get('t_recebimento_semc');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: vw_buscar_notas_fiscais
     */
    function obtem_nf_recebimento($form_data_inicio, $form_data_final, $tipo_nf, $form_nota)
    {
        $this->db->from('vw_buscar_notas_fiscais');

        if ($tipo_nf == 'CHAVE') {
            $this->db->where('chave_nota', $form_nota);
            //$this->db->order_by('t_cabecalho_notas.cbn_chave_nota', 'ASC');
        } elseif ($tipo_nf == 'NUMERO') {
            $this->db->where('nota_fiscal', $form_nota);
            //$this->db->order_by('t_cabecalho_notas.cbn_num_nota', 'ASC');
        }

        $this->db->where('status', 'ENTREGUE');
        $this->db->where("data_emissao BETWEEN '" . $form_data_inicio . "' AND '" . $form_data_final . "'");

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_cabecalho_notas, t_volumes
     */
    function obtem_dados_divergencia($cbn_id, $vol_cod_barras)
    {

        $this->db->select('t_cabecalho_notas.cbn_id, t_cabecalho_notas.cbn_num_nota,t_volumes.vol_cod_barras');
        $this->db->from('t_cabecalho_notas');
        $this->db->join('t_volumes', 't_volumes.vol_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
        $this->db->where('t_cabecalho_notas.cbn_id', $cbn_id);
        $this->db->where('t_volumes.vol_cod_barras', $vol_cod_barras);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_cabecalho_notas, t_volumes
     */
    function obtem_dados_nota_fiscal($cbn_num_nota, $vol_cod_barras)
    {
        $this->db->select('t_cabecalho_notas.cbn_id, t_cabecalho_notas.cbn_id_fil, 
                            t_cabecalho_notas.cbn_num_nota,t_volumes.vol_cod_barras,
                            t_cabecalho_notas.cbn_chave_nota');
        $this->db->from('t_cabecalho_notas');
        $this->db->join('t_volumes', 't_volumes.vol_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
        $this->db->where('t_cabecalho_notas.cbn_num_nota', $cbn_num_nota);
        $this->db->where('t_volumes.vol_cod_barras', $vol_cod_barras);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    function obtem_dados_nota_fiscal_externo($cbn_num_nota)
    {
        $this->db->select('t_cabecalho_notas.cbn_id, t_cabecalho_notas.cbn_id_fil, 
                            t_cabecalho_notas.cbn_num_nota,
                            t_cabecalho_notas.cbn_chave_nota');
        $this->db->from('t_cabecalho_notas');
        $this->db->where('t_cabecalho_notas.cbn_num_nota', $cbn_num_nota);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_item_notas, t_cabecalho_notas, t_produtos, t_eans
     */
    function RecebimentoDivergenteExiste($cbn_num_nota, $ean_cod)
    {
        $this->db->select('t_produtos.pro_id AS pro_id, t_produtos.pro_cod_pro_cli AS codigo, t_produtos.pro_descricao AS descricao, t_item_notas.itn_qtd_ven AS quantidade, t_eans.ean_cod AS ean');
        $this->db->from('t_item_notas');
        $this->db->join('t_cabecalho_notas', 't_item_notas.itn_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
        $this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
        $this->db->join('t_eans', 't_eans.ean_id_pro = t_produtos.pro_id', 'INNER');
        $this->db->where('t_cabecalho_notas.cbn_num_nota', $cbn_num_nota);

        if ($ean_cod != '') {
            $this->db->where('t_eans.ean_cod', $ean_cod);
        }

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_itens_divergencia
     */
    function gravar_item_divergencia($num_volume, $num_nota, $pro_id, $qtd, $divergencia, $obs)
    {
        $this->db->where('itd_volume', $num_volume);
        $this->db->where('itd_num_nota', $num_nota);
        $query = $this->db->get('t_itens_divergencia');

        if ($query->num_rows() > 0) {
            $this->db->where('itd_volume', $num_volume);
            $this->db->where('itd_num_nota', $num_nota);
            $this->db->set('itd_id_pro', $pro_id);
            $this->db->set('itd_qtd_vendida', $qtd);
            $this->db->set('itd_id_tipo_divergencia', $divergencia);
            $this->db->set('itd_obs', $obs);

            if ($this->db->update('t_itens_divergencia')) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            $this->db->set('itd_volume', $num_volume);
            $this->db->set('itd_num_nota', $num_nota);
            $this->db->set('itd_id_pro', $pro_id);
            $this->db->set('itd_qtd_vendida', $qtd);
            $this->db->set('itd_id_tipo_divergencia', $divergencia);
            $this->db->set('itd_obs', $obs);

            if ($this->db->insert('t_itens_divergencia')) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_cabecalho_notas, t_movimentacoes_estoque 
     */
    function gravar_movimentacao_estoque($data_volume, $filial, $pro_id, $qtd, $divergencia, $id_nota)
    {

        // Obter código destino
        switch ($divergencia) {
            case 1: // AVARIA
                $destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '04';
                break;

            case 2: // VENCIDO
                $destino = '1' . (string) str_pad($filial, 4, '0', STR_PAD_LEFT) . '05';
                break;
        }

        // Obter chave NF
        $this->db->select('cbn_chave_nota');
        $this->db->from('t_cabecalho_notas');
        $this->db->where('cbn_id', $id_nota);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $cbn_chave_nota = $row->cbn_chave_nota;
        }

        $this->db->set('mov_data', $data_volume);
        $this->db->set('mov_id_filial', $filial);
        $this->db->set('mov_id_produto', $pro_id);
        $this->db->set('mov_qtd_movimentada', $qtd);
        $this->db->set('mov_estoque_destino', $destino);
        $this->db->set('mov_controle_integracao', 'L');
        $this->db->set('mov_chave_nfe', $cbn_chave_nota);

        if ($this->db->insert('t_movimentacoes_estoque')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_itens_divergencia
     */
    function buscar_itens_divergencias($num_nota, $num_volume)
    {
        $this->db->from('t_itens_divergencia');
        $this->db->where('itd_num_nota', $num_nota);
        $this->db->where('itd_volume', $num_volume);
        $this->db->order_by('itd_id_tipo_divergencia', 'ASC');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: t_cabecalho_notas, t_item_notas, t_produtos, t_eans
     */
    function obtem_produto_volume_nf($num_nota, $vol_cod_barras, $ean)
    {
        $sql = "select * from t_cabecalho_notas
                    join t_item_notas on itn_id_cbn = cbn_id
                    join t_produtos on pro_id = itn_id_pro
                    join t_eans on ean_id_pro = pro_id
                    where 
                        cbn_num_nota = " . $num_nota . " and itn_cod_barras = '" . $vol_cod_barras . "' 
                        and ean_cod = '" . $ean . "'";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| 
     */
    function obtem_itens_nota($chave)
    {

        $sql = "select * from t_cabecalho_notas
                join t_item_notas on itn_id_cbn = cbn_id
                join t_produtos on pro_id = itn_id_pro
                join t_filial on fil_id = cbn_id_fil
                where 
                    cbn_chave_nota = '" . $chave . "'";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| Retorna os detalhes da NF para página de detalhes usando a view existente
     */
    function obtem_detalhes_nota($id_nota)
    {

        $campos = [
            'nota_fiscal',
            'emitente',
            'destinatario',
            'data_emissao',
            'hora_emissao',
            'valor_nota',
            'status',
            'data_carregamento',
            'data_entrega',
            'transportador',
        ];
        
        // $this->db->select_sum('qtd_item');
        $this->db->select($campos);
        $this->db->from('vw_buscar_notas_fiscais');
        $this->db->where('id_nota', $id_nota);
        $this->db->group_by($campos);
        $this->db->limit(1);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    /*============================================================================================*/
    /**
     *| Retorna os itens da NF para página de detalhes
     */
    function obtem_detalhes_itens_nota($cbn_id)
    {

        $sql = "SELECT
                    *
                FROM
                    t_item_notas
                    -- JOIN t_cabecalho_notas ON cbn_id = itn_id_cbn
                    JOIN t_produtos ON pro_id = itn_id_pro
                WHERE
                    itn_id_cbn = '${cbn_id}'
                ";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    function gravar_nota_finalizada($chave, $filial, $data, $qtd, $usuario)
    {
        $this->db->set('chave_nfe', $chave);
        $this->db->set('data_nfe', $data);
        $this->db->set('loja_destino', $filial);
        $this->db->set('cnpj_destino', '');
        $this->db->set('qtd_itens', $qtd);
        $this->db->set('usuario', $usuario);
        $this->db->set('integrado', 'L');

        if ($this->db->insert('t_notas_finalizadas')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getNotaDanfe($id_cbn){
        
        $this->db->select('*');
        $this->db->from('t_cabecalho_notas');
        $this->db->join('t_filial', 't_filial.fil_cpf_cnpj = t_cabecalho_notas.cbn_cnpj_destinatario', 'INNER');
        $this->db->where('cbn_id', $id_cbn);
        $this->db->limit(1);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    function getItensNota($id_cbn){

        $this->db->select('*');
        $this->db->from('t_item_notas');
        $this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
        $this->db->where('itn_id_cbn', $id_cbn);
        
        $query = $this->db->get();

        if($query->num_rows() > 0){
            return $query->result();
        } else {
            return false;
        }

    }

    /*============================================================================================*/
    /**
     *| 
     *| Tabela: vw_buscar_notas_fiscais
     */
    function obtem_nf_nao_entregue($num_nota)
    {

        $this->db->from('vw_buscar_notas_fiscais');

        $this->db->where('status', 'NÃO ENTREGUE');        
        $this->db->where('nota_fiscal', $num_nota);        

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    function maracar_como_entregue($num_nota,$id_nota){
        
        $this->db->set('cbn_status', 'ENTREGUE');
        
        $this->db->where('cbn_num_nota', $num_nota);
        $this->db->where('cbn_id', $id_nota);
        
        if($this->db->update('t_cabecalho_notas'))
        {
            return true;
        }
        else
        {
            return false;
        }	
        
    }
    
    function set_parametro_tempo_nf_nao_entregue($tempo){      
        
        $this->db->set('param_nf_nao_recebidas', $tempo);
        
        if($this->db->update('t_parametros'))
        {
            return true;
        }
        else
        {
            return false;
        }	
        
    }
    
    function get_tempo_nf_nao_entregue()
    {
    
        $this->db->from('t_parametros');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
 
    function atualiza_nf_para_nao_entregue($tempo) {
        
        // Calcula a data limite com base no tempo recebido via parâmetro
        $data_limite = date('Y-m-d H:i:s', strtotime("-$tempo days"));

        $this->db->select('count(*) as total');
        $this->db->from('t_cabecalho_notas');
        $query = $this->db->get();
        $result = $query->row();
        $total = $result->total;

        if($total > 0){

            $this->db->select('cbn_id');
            $this->db->from('t_cabecalho_notas');
            $this->db->where("CONCAT(cbn_data_emissao, ' ', cbn_hora_emissao) < '$data_limite'");
            $this->db->where('cbn_status', 'FATURADA');
            $this->db->or_where('cbn_statuss', 'EM TRANSITO');
            $query = $this->db->get();
         
            foreach ($query->result() as $row) {
                $cbn_id = $row->cbn_id;             
                
                // atualizar cada registro individualmente usando o ID recuperado
                $data = array('cbn_status' => 'NÃO ENTREGUE');
                $this->db->where('cbn_id', $cbn_id);
                $this->db->update('t_cabecalho_notas', $data);
            }
        }

                        
    }
}
<?php
class Inventario_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	/*============================================================================================*/
	/**
	 *| Obtem todos os dados cadastrados
	 *| Tabela: t_par_inventario
	 */
	function obtem_dados_inventario()
	{
		$this->db->select('*');
		$this->db->from('t_par_inventario');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Adiciona um novo item
	 *| Tabela: t_par_inventario
	 */

	function adicionar_inventario($dados)
	{
		if ($this->db->insert('t_par_inventario', $dados)) {
			return true;
		}
		return false;
	}

	/*============================================================================================*/
	/**
	 *| Verifica se a loja já foi cadastrada
	 *| Tabela: t_par_inventario
	 */

	function verifica_cadastro_inventario($parinv_loja)
	{
		$this->db->where(array('parinv_loja' => $parinv_loja));
		$query = $this->db->get('t_par_inventario');
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Apaga os dados 
	 *| Tabela: t_par_inventario
	 */

	function deletar_inventario($parinv_id)
	{
		$this->db->where('parinv_id', $parinv_id);
		if ($this->db->delete('t_par_inventario')) {
			return true;
		}
		return false;
	}

	/*============================================================================================*/
	/**
	 *| Retorna um item
	 *| Tabela: t_par_inventario
	 */

	function obtem_dados_inventario_unico($parinv_id)
	{

		// $this->db->select('t_confere_perc.*,t_filial.fil_nome,t_filial.fil_id,t_classificacoes_produtos.*');
		$this->db->select('*');
		$this->db->from('t_par_inventario');
		// $this->db->join('t_filial','t_filial.fil_id = t_confere_perc.conf_fil_id');
		// $this->db->join('t_classificacoes_produtos','t_classificacoes_produtos.cpp_id = t_confere_perc.conf_cpp_id');
		$this->db->where('parinv_id', $parinv_id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	/*============================================================================================*/
	/**
	 *| Atualiza os dados de um item
	 *| Tabela: t_par_inventario
	 */

	function atualizar_inventario($parinv_id, $dados)
	{
		$this->db->where('parinv_id', $parinv_id);
		if ($this->db->update('t_par_inventario', $dados)) {
			return true;
		}
		return false;
	}

	/*============================================================================================*/
	/**
	 *| Insere CSV no banco
	 *| Tabela: t_par_inventario
	 */

	function insere_csv($params)
	{
		if ($this->db->insert('t_entrada_manual', $params)) {
			return true;
		}
		return false;
	}

	function insere_agendamento($data)
	{
		if ($this->db->insert('t_agendamentos', $data)) {
			return true;
		}
		return false;
	}

	function obtem_numero_ultimo_agendamento()
	{
		$this->db->from('t_agendamentos');
		$this->db->order_by('agen_numero', 'desc');
		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	function obtem_id_agendammento()
	{
		$this->db->from('t_agendamentos');
		$this->db->order_by('agen_numero', 'desc');
		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	function obtem_id()
	{
		$this->db->select('agen_id');
		$this->db->from('t_agendamentos');

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	function obtem_numero_ultimo_agendamento_loja($lojaInv)

	{
		$this->db->select('agen_numero');
		$this->db->from('t_agendamentos');
		$this->db->order_by('agen_numero', 'desc');
		$this->db->where('agen_id_loja', $lojaInv);
		$this->db->limit(1);

		$query = $this->db->get();



		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	function obtem_falteiros($falteiros, $lojaInv)
	{
		$this->db->select('falt_cod_pro');
		$this->db->from('t_produtos_falteiros f');
		$this->db->join('t_produtos p', 'f.falt_cod_pro = p.pro_cod_pro_cli');
		$this->db->where('falt_filial', $lojaInv);
		$this->db->where('falt_utilizado', 0);
		$this->db->order_by('p.pro_curva', 'desc');
		$this->db->order_by('f.falt_valor_estoque', 'desc');
		$this->db->order_by('f.falt_data_geracao', 'desc');
		$this->db->limit($falteiros);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_divergentes($divergencia, $lojaInv)
	{
		$this->db->select('inv_cod_pro');
		$this->db->from('t_produtos_divergenciainventario d');
		$this->db->join('t_produtos p', 'd.inv_cod_pro = p.pro_cod_pro_cli');
		$this->db->where('inv_filial', $lojaInv);
		$this->db->where('inv_utilizado', 0);
		$this->db->order_by('p.pro_curva', 'desc');
		$this->db->order_by('d.inv_valor_estoque', 'desc');
		$this->db->order_by('d.inv_data_geracao', 'desc');
		$this->db->limit($divergencia);


		$this->db->limit($divergencia);
		$query = $this->db->get();

		// echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_nao_movimentados($naoMovimentados, $lojaInv)
	{
		$this->db->from('t_produtos_naomovimentados m');
		$this->db->join('t_produtos p', 'm.nmov_cod_pro = p.pro_cod_pro_cli');
		$this->db->where('nmov_filial', $lojaInv);
		$this->db->where('nmov_utilizado', 0);
		$this->db->order_by('p.pro_curva', 'desc');
		$this->db->order_by('m.nmov_valor_estoque', 'desc');
		$this->db->order_by('m.nmov_data_geracao', 'desc');
		$this->db->limit($naoMovimentados);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_loja_usu($id)
	{
		$this->db->select('usu_cod_apontamento');
		$this->db->from('t_usuarios');
		$this->db->where('usu_id', $id);
		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// function obtem_loja_inv($lojaUser)
	// {

	// 	$this->db->select('agen_id_loja');
	// 	$this->db->from('t_agendamentos');
	// 	$this->db->where('agen_id_loja', $lojaUser);

	// 	$query = $this->db->get();
	// 	if ($query->num_rows() > 0) {
	// 		return $query->result();
	// 	} else {
	// 		return false;
	// 	}
	// }

	function obtem_inv($loja)
	{
		$sql = "SELECT * FROM t_agendamentos WHERE agen_id_loja = " . $loja . "";
		//"and agen_id  = " . $agenid .
		//$this->db->limit(1);

		$query = $this->db->query($sql);
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_template($id)
	{
		$this->db->from('t_entrada_manual');
		$this->db->where('ent_id_agendamento', $id);

		$query = $this->db->get();

		// echo $this->db->last_query();

		if ($query->num_rows() > 0) {

			return 'MANUAL';
		} else {

			return 'MVP-1';
		}
	}

	function obtem_cinco_proximas_realizacoes_por_agendamento($lojaId, $agenid)
	{
		$sql = "SELECT TOP(5) tr.rea_id, tr.rea_id_agendamento, tr.rea_hora_ini, tr.rea_hora_fim, tr.rea_status, tr.rea_usuario, tr.rea_id_cesta, ta.agen_id, ta.agen_id_loja, ta.agen_tempo_limite 
        FROM t_realizacoes tr 
        JOIN t_agendamentos ta on ta.agen_id_loja = tr.rea_agen_id 
        WHERE ta.agen_id_loja = " . $lojaId . " AND agen_id = " . $agenid . " AND (tr.rea_status IS NULL OR tr.rea_status <> 'REALIZADO') AND rea_hora_ini >= CAST( GETDATE() AS Date ) 
        ORDER BY tr.rea_hora_ini ASC";

		$query = $this->db->query($sql);
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_contagem($id)
	{
		$sql = "SELECT DISTINCT p.pro_cod_pro_cli, p.pro_descricao, c.con_pro_estoque, c.con_pro_contagem, c.con_pro_divergencia, c.con_transferencia, c.con_cupom_fiscal, c.con_dev_cliente, c.con_nf_entrada, c.con_dev_fornecedor 
		FROM VW_PRODUTO_CESTA p 
		JOIN t_contagem c ON p.pro_cod_pro_cli = con_id_pro
		WHERE c.con_id_agendamento = ".$id."";

		$query = $this->db->query($sql);
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_contagem_produtos($id)
	{
		$sql = "SELECT DISTINCT p.pro_cod_pro_cli, p.pro_descricao, c.con_pro_contagem
		FROM VW_PRODUTO_CESTA p 
		JOIN t_contagem c ON p.pro_cod_pro_cli = con_id_pro
		WHERE c.con_id_realizacao = ".$id."";

		$query = $this->db->query($sql);
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	

	function obtem_agendamentos()
	{
		$this->db->from('t_agendamentos');
		$this->db->where('agen_status','ATIVO');

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_realizacoes()
	{
		$this->db->from('t_realizacoes');
		// $this->db->where('agen_numero',$id);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_agendamentos_id($id)
	{
		$this->db->from('t_agendamentos');
		$this->db->where('agen_id', $id);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_realizacoes_notnull()
	{


		$sql = 'SELECT * FROM t_realizacoes WHERE rea_usuario is not null';

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_detalhes()
	{
		$this->db->from('t_agendamentos');
		// $this->db->where('agen_id',$id);

		$query = $this->db->get();

		// echo $this->db->last_query();


		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_agendamento_por_realizacao($idRealizacao)
	{
		$sql = 'SELECT TOP(1) * FROM t_agendamentos WHERE agen_numero = ' . $idRealizacao;

		$query = $this->db->query($sql);


		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_realizacao_por_id($idRealizacao)
	{
		$sql = 'SELECT TOP(1) * FROM t_realizacoes WHERE rea_id = ' . $idRealizacao;

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_cesta()
	{

		$this->db->from('t_cestas');
		$this->db->order_by('ces_id_agendamento', 'desc');
		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
		}
		return false;
	}

	function insere_cesta($param)
	{
		if ($this->db->insert('t_cestas', $param)) {
			return true;
		}
		return false;
	}
	function insere_cesta_nova($param)
	{
		if ($this->db->insert('t_cestas', $param)) {			
			return $this->db->insert_id();
		}
		return false;
	}

	function realizar_inv($lastID, $lastCesta, $lojaUser)
	{
		print_r($lojaUser);
		$sql = " 
	INSERT INTO [dbo].[t_realizacoes]
           ([rea_id_agendamento]
           ,[rea_id_cesta]
           ,[rea_hora_ini]
           ,[rea_hora_fim]
           ,[rea_usuario]
           ,[rea_status])
     VALUES
           ('" . $lastID . "',
           	'" . $lastCesta . "',
           	CURRENT_TIMESTAMP,
          	0,
            " . $lojaUser[0]->usu_cod_apontamento . ",
            'EM ANDAMENTO')";

		$query = $this->db->query($sql);


		if ($query->num_rows() > 0) {
			$row = $query->row();
			if ($row->N != 0) {
				return true;
			}
		}

		return false;
	}

	function obtem_produto_id($id)
	{
		$this->db->select('pro_id, pro_descricao');
		$this->db->where('pro_id', $id);
		// $this->db->limit(1);

		$query = $this->db->get('t_produtos');

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_produtos_cesta($numAgendamento)
	{
		$this->db->from('t_cestas');
		$this->db->where('ces_id_agendamento', $numAgendamento);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function conta_items($rea_numero)
	{

		$param['rea_id'] = $rea_numero;
		
		$this->db->select('*');
		$this->db->from('t_realizacoes');		
		$this->db->where('rea_id', $rea_numero);
		$query = $this->db->get();
		
		$res = $query->result();
		$ces_id = $res[0]->rea_id_cesta;	
		
		$sql = "select count(*) as total_items from VIEW_PRODUTO_CESTA_NOVA where ces_id = " . $ces_id;
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_realizacoes_em_andamento($id)
	{
		$this->db->select('*');
		$this->db->from('t_realizacoes');
		$this->db->where('rea_id_agendamento', $id);
		$this->db->where('rea_status', 'EM ANDAMENTO');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function insere_realizacao($data)
	{
		if ($this->db->insert('t_realizacoes', $data)) {
			// echo $this->db->last_query();
			return true;
		}
		return false;
	}

	function insere_contagem($param)
	{
		if ($this->db->insert('t_contagem', $param)) {
			return true;
		}

		return false;
	}

	function inserir_contagem($id)
	{
		$sql = "SELECT tp.pro_id, tp.pro_descricao FROM t_produtos tp inner join t_entrada_manual tem ON tp.pro_id = tem.ent_pro_id WHERE tem.ent_id_agendamento = " . $id;

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_produtos_2($agen_numero,$rea_numero)
	{
		
		$param['rea_id'] = $rea_numero;
		
		$this->db->select('*');
		$this->db->from('t_realizacoes');		
		$this->db->where('rea_id', $rea_numero);
		$query = $this->db->get();
		
		$res = $query->result();
		$ces_id = $res[0]->rea_id_cesta;	
		
		//pegar id da cesta utilizando o id da realizacao
		$sql = "select distinct * from VIEW_PRODUTO_CESTA_NOVA where ces_id = " . $ces_id;

		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_produtos($agen_numero)
	{

		//pegar id da cesta utilizando o id da realizacao
		$sql = "select distinct * from VW_PRODUTO_CESTA where ces_id_agendamento = " . $agen_numero;

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function update_realizacao($param, $rea_id)
	{
		$this->db->where('rea_id', $rea_id);

		if ($this->db->update('t_realizacoes', $param)) {
			return true;
		}
		return false;
	}

	function delete_contagem_atual_realizacao($rea_id)
	{
		$this->db->where('con_id_realizacao', $rea_id);

		if ($this->db->delete('t_contagem')) {
			return true;
		}
		return false;
	}

	function delete_agendamento($id)
	{
		$param['agen_status'] = 'INATIVO';

		$this->db->select('*');
		$this->db->from('t_agendamentos');		
		$this->db->where('agen_id', $id);
		$query = $this->db->get();

		$res = $query->result();
		$agen_numero = $res[0]->agen_numero;		
		
		$this->db->where('agen_id', $id);
		$this->db->update('t_agendamentos', $param);
		
		$this->db->where('rea_id_agendamento', $agen_numero);
		$this->db->where('rea_status !=', 'REALIZADO');
		$this->db->or_where('rea_status', NULL);		

		if ($this->db->delete('t_realizacoes')) {
			return true;
		}
		return false;
	}

	function finalizar_inv($lojaUser)
	{
		$this->db->select('*');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function hora_limite_loja()
	{
		$this->db->select('agen_tempo_limite');
		$this->db->from('t_agendamentos');
		$this->db->where('agen_id_loja',);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_produto($agen_numero)
	{

		$sql = "SELECT distinct pro_cod_pro_cli, pro_descricao FROM VIEW_PRODUTO_CESTA_NOVA p join t_agendamentos c on c.agen_numero = p.ces_id_agendamento
		where c.agen_numero = $agen_numero  AND pro_cod_pro_cli is not null";

		/* $sql = "SELECT distinct pro_cod_pro_cli, pro_descricao FROM VW_PRODUTO_CESTA p join t_agendamentos c on c.agen_id = p.ces_id_agendamento
		where c.agen_id = " . $id . " AND pro_cod_pro_cli is not null"; */


		$query = $this->db->query($sql);

		// echo $this->db->last_query();


		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function obtem_produto_realizacoes($ids)
	{

		$sql = "SELECT DISTINCT p.pro_cod_pro_cli, p.pro_descricao, c.con_pro_estoque, c.con_pro_contagem, c.con_pro_divergencia, c.con_transferencia, c.con_cupom_fiscal, c.con_dev_cliente, c.con_nf_entrada, c.con_dev_fornecedor, c.con_estoque_ajustado 
		FROM VW_PRODUTO_CESTA p 
		JOIN t_contagem c ON p.pro_cod_pro_cli = con_id_pro
		WHERE c.con_id_realizacao = " . $ids . "";

		$query = $this->db->query($sql);

		// echo $this->db->last_query();


		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function verifica_contagem_aprovada($ids)
	{

		$sql = "select con_inv_finalizado from t_contagem where con_id_realizacao = ".$ids . " group by  con_inv_finalizado";

		$query = $this->db->query($sql);

		// echo $this->db->last_query();


		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_produto_tipo($id,$loja)
	{

		$sql = "
		SELECT TOP (1) ORIGEM, PRODUTO 
        FROM (
          SELECT top (1) 'Divergente' AS ORIGEM, inv_cod_pro AS Produto 
          FROM t_produtos_divergenciainventario  
          WHERE inv_cod_pro = " . $id . " and inv_filial = " . $loja . "
        UNION 
          SELECT top (1) 'Falteiro' AS ORIGEM, falt_cod_pro AS Produto 
          FROM t_produtos_falteiros 
          WHERE falt_cod_pro = " . $id . " and falt_filial = " . $loja . "
        UNION 
          SELECT top (1) 'Não Movimentado' AS ORIGEM, nmov_cod_pro AS Produto 
          FROM t_produtos_naomovimentados 
          WHERE nmov_cod_pro= " . $id . " and nmov_filial = " . $loja . " 
        ) subconsulta";

		$query = $this->db->query($sql);


		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function atualiza_contagem($id, $produto, $campo, $valor)
	{
		$this->db->where('con_id_realizacao = ' . $id . ' and con_id_pro = ' . $produto . '');
		$this->db->set('' . $campo . '', $valor);

		if ($this->db->update('t_contagem')) {
			return true;
		}
		return false;
	}
	function obtem_inventario_filtro($id, $data, $loja, $status, $regional)
	{
		$controle = 0;
		$sql = "select * from vw_realizacoes_agendamentos";

		if ($id != "") {
			$sql = $sql . "where rea_id = '$id'";
			$controle++;
		}

		if ($data != "") {
			if ($controle > 0) {
				$sql = $sql . "and rea_hora_fim = '$data'";
			} else {
				$sql = $sql . "where rea_hora_fim = '$data'";
				$controle++;
			}
		}

		if ($loja != "") {
			if ($controle > 0) {
				$sql = $sql . "and agen_id_loja = '$loja'";
			} else {
				$sql = $sql . "where agen_id_loja = '$loja'";
				$controle++;
			}
		}

		if ($status != "") {
			if ($controle > 0) {
				$sql = $sql . "and agen_id_loja = '$status'";
			} else {
				$sql = $sql . "where agen_id_loja = '$status'";
				$controle++;
			}
		}

		if ($regional != "") {
			if ($controle > 0) {
				$sql = $sql . "and agen_id_loja = '$regional'";
			} else {
				$sql = $sql . "where agen_id_loja = '$regional'";
				$controle++;
			}
		}

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_todos_chamados_loja($dataini, $datafim, $status, $loja)
	{
		$sql = "select * from t_chamados
					join t_filial on cha_solicitante = fil_num_loja 
					join t_chamados_itens on chi_id_chamado = cha_id
					join t_produtos on pro_id = chi_id_pro
					left join t_usuarios on cha_atendente = usu_id
				where 
					cast(cha_data_abertura as date) between '$dataini' and '$datafim'
					and fil_num_loja = $loja";
		if ($status != "") {
			$sql = $sql . "and cha_status = '$status'";
		}
		if ($status != "") {
			$sql = $sql . "and cha_status = '$status'";
		}

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function delete_produto($id)
	{
		$sql = "DELETE FROM VW_PRODUTO_CESTA p
		join t_agendamentos c on c.agen_id = p.ces_id_agendamento where c.agen_id = " . $id . "";

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function atualiza_divergentes($resultado)
	{
		$this->db->where('inv_cod_pro', $resultado);
		$this->db->set('inv_utilizado', 1);

		if ($this->db->update('t_produtos_divergenciainventario')) {
			return true;
		}

		return false;
	}


	function atualiza_nao_movimentados($resultado)
	{
		$this->db->where('nmov_cod_pro', $resultado);
		$this->db->set('nmov_utilizado', 1);

		if ($this->db->update('t_produtos_naomovimentados')) {
			return true;
		}
		return false;
	}

	function atualiza_falteiros($resultado)
	{
		$this->db->where('falt_cod_pro', $resultado);
		$this->db->set('falt_utilizado', 1);

		if ($this->db->update('t_produtos_falteiros')) {
			return true;
		}
		return false;
	}

	function pega_contagem($id)
	{
		$sql = "select con_pro_divergencia,con_transferencia,con_cupom_fiscal,con_dev_cliente,con_nf_entrada,con_dev_fornecedor from t_contagem where con_id_agendamento = " . $id . "";

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function obtem_produto_realizacoes_unico($id, $prod)
	{

		$sql = "SELECT DISTINCT p.pro_cod_pro_cli, p.pro_descricao, c.con_pro_estoque,c.con_pro_divergencia, c.con_transferencia, c.con_cupom_fiscal, c.con_dev_cliente, c.con_nf_entrada, c.con_dev_fornecedor 
		FROM VW_PRODUTO_CESTA p 
		JOIN t_contagem c ON p.pro_cod_pro_cli = con_id_pro
		WHERE c.con_id_realizacao = " . $id . " AND p.pro_cod_pro_cli = " . $prod . "";


		$query = $this->db->query($sql);



		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	public function insere_produto_detalhes($id, $idProduto)
	{
		$sql = "DECLARE @ces_id_agendamento int = " . $id . "
		DECLARE @novo_produto varchar(255) = " . $idProduto . "
		UPDATE t_cestas
		SET ces_pro1 = COALESCE(ces_pro1, @novo_produto),
			ces_pro2 = CASE WHEN ces_pro1 IS NOT NULL THEN COALESCE(ces_pro2, @novo_produto) ELSE ces_pro2 END,
			ces_pro3 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL THEN COALESCE(ces_pro3, @novo_produto) ELSE ces_pro3 END,
			ces_pro4 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL THEN COALESCE(ces_pro4, @novo_produto) ELSE ces_pro4 END,
			ces_pro5 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL THEN COALESCE(ces_pro5, @novo_produto) ELSE ces_pro5 END,
			ces_pro6 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL THEN COALESCE(ces_pro6, @novo_produto) ELSE ces_pro6 END,
			ces_pro7 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL THEN COALESCE(ces_pro7, @novo_produto) ELSE ces_pro7 END,
			ces_pro8 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL THEN COALESCE(ces_pro8, @novo_produto) ELSE ces_pro8 END,
			ces_pro9 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL THEN COALESCE(ces_pro9, @novo_produto) ELSE ces_pro9 END,
			ces_pro10 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL AND ces_pro9 IS NOT NULL THEN COALESCE(ces_pro10, @novo_produto) ELSE ces_pro10 END,
			ces_pro11 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL AND ces_pro9 IS NOT NULL AND ces_pro10 IS NOT NULL THEN COALESCE(ces_pro11, @novo_produto) ELSE ces_pro11 END,
			ces_pro12 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL AND ces_pro9 IS NOT NULL AND ces_pro10 IS NOT NULL AND ces_pro11 IS NOT NULL THEN COALESCE(ces_pro12, @novo_produto) ELSE ces_pro12 END,
		ces_pro13 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL AND ces_pro9 IS NOT NULL AND ces_pro10 IS NOT NULL AND ces_pro11 IS NOT NULL AND ces_pro12 IS NOT NULL THEN COALESCE(ces_pro13, @novo_produto) ELSE ces_pro13 END,
		ces_pro14 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL AND ces_pro9 IS NOT NULL AND ces_pro10 IS NOT NULL AND ces_pro11 IS NOT NULL AND ces_pro12 IS NOT NULL AND ces_pro13 IS NOT NULL THEN COALESCE(ces_pro14, @novo_produto) ELSE ces_pro14 END,
		ces_pro15 = CASE WHEN ces_pro1 IS NOT NULL AND ces_pro2 IS NOT NULL AND ces_pro3 IS NOT NULL AND ces_pro4 IS NOT NULL AND ces_pro5 IS NOT NULL AND ces_pro6 IS NOT NULL AND ces_pro7 IS NOT NULL AND ces_pro8 IS NOT NULL AND ces_pro9 IS NOT NULL AND ces_pro10 IS NOT NULL AND ces_pro11 IS NOT NULL AND ces_pro12 IS NOT NULL AND ces_pro13 IS NOT NULL AND ces_pro14 IS NOT NULL THEN COALESCE(ces_pro15, @novo_produto) ELSE ces_pro15 END
		WHERE ces_id_agendamento = @ces_id_agendamento;";

		$query = $this->db->query($sql, array($id));

		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function verifica_produto($idProduto)
	{
		$sql = "select top(1) * from t_produtos where pro_cod_pro_cli = " . $idProduto . "";

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function verificarCamposPreenchidos()
	{
		$query = $this->db->query("SELECT [ces_id], [ces_id_agendamento], [ces_pro1], [ces_pro2], [ces_pro3], [ces_pro4], [ces_pro5], [ces_pro6], [ces_pro7], [ces_pro8], [ces_pro9], [ces_pro10], [ces_pro11], [ces_pro12], [ces_pro13], [ces_pro14], [ces_pro15] FROM [dbo].[t_cestas]");

		// verificar se todos os campos estão preenchidos
		foreach ($query->result_array() as $row) {
			foreach ($row as $campo) {
				if ($campo == null) {
					return false;
				}
			}
		}

		return true;
	}

	public function remove_produto($id, $id_agendamento)
	{
		//Verifica em qual campo o produto está e atualiza com null
		$sql =
			"DECLARE @ces_id_agendamento int = " . $id . ";
        	DECLARE @novo_produto varchar(255) = " . $id_agendamento . ";
			UPDATE t_cestas 
			SET 
			  ces_pro1 = NULLIF(ces_pro1, @novo_produto),
			  ces_pro2 = CASE WHEN ces_pro1 IS NOT NULL THEN NULLIF(ces_pro2, @novo_produto) ELSE ces_pro2 END,
			  ces_pro3 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL THEN NULLIF(ces_pro3, @novo_produto) ELSE ces_pro3 END,
			  ces_pro4 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL THEN NULLIF(ces_pro4, @novo_produto) ELSE ces_pro4 END,
			  ces_pro5 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL THEN NULLIF(ces_pro5, @novo_produto) ELSE ces_pro5 END,
			  ces_pro6 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL THEN NULLIF(ces_pro6, @novo_produto) ELSE ces_pro6 END,
			  ces_pro7 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL THEN NULLIF(ces_pro7, @novo_produto) ELSE ces_pro7 END,
			  ces_pro8 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL THEN NULLIF(ces_pro8, @novo_produto) ELSE ces_pro8 END,
			  ces_pro9 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL THEN NULLIF(ces_pro9, @novo_produto) ELSE ces_pro9 END,
			  ces_pro10 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL OR ces_pro9 IS NOT NULL THEN NULLIF(ces_pro10, @novo_produto) ELSE ces_pro10 END,
			  ces_pro11 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL OR ces_pro9 IS NOT NULL OR ces_pro10 IS NOT NULL THEN NULLIF(ces_pro11, @novo_produto) ELSE ces_pro11 END,
			  ces_pro12 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL OR ces_pro9 IS NOT NULL OR ces_pro10 IS NOT NULL OR ces_pro11 IS NOT NULL THEN NULLIF(ces_pro12, @novo_produto) ELSE ces_pro12 END,
			  ces_pro13 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL OR ces_pro9 IS NOT NULL OR ces_pro10 IS NOT NULL OR ces_pro11 IS NOT NULL OR ces_pro12 IS NOT NULL THEN NULLIF(ces_pro13, @novo_produto) ELSE ces_pro13 END,
			  ces_pro14 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL OR ces_pro9 IS NOT NULL OR ces_pro10 IS NOT NULL OR ces_pro11 IS NOT NULL OR ces_pro12 IS NOT NULL OR ces_pro13 IS NOT NULL THEN NULLIF(ces_pro14, @novo_produto) ELSE ces_pro14 END,
			  ces_pro15 = CASE WHEN ces_pro1 IS NOT NULL OR ces_pro2 IS NOT NULL OR ces_pro3 IS NOT NULL OR ces_pro4 IS NOT NULL OR ces_pro5 IS NOT NULL OR ces_pro6 IS NOT NULL OR ces_pro7 IS NOT NULL OR ces_pro8 IS NOT NULL OR ces_pro9 IS NOT NULL OR ces_pro10 IS NOT NULL OR ces_pro11 IS NOT NULL OR ces_pro12 IS NOT NULL OR ces_pro13 IS NOT NULL OR ces_pro14 IS NOT NULL THEN NULLIF(ces_pro15, @novo_produto) ELSE ces_pro15 END
			  WHERE ces_id_agendamento = @ces_id_agendamento;";

		$query = $this->db->query($sql);

		// echo $this->db->last_query();

		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
}

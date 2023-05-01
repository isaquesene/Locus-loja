<?php
class conferencia_model extends CI_Model
{

	// $dt = date("Y-m-d H:m:i.v");     

	function __construct()
	{
		parent::__construct();
	}

	/*============================================================================================*/
	/**
	 *| Obtem todos os volumes para conferencia para a NF informada
	 *| Tabela: t_volumes, t_cabecalho_notas
	 */
	function obtem_lista_nf_conferencia($data_inicial, $data_final, $nota_fiscal)
	{
		if($nota_fiscal != 0 || $nota_fiscal != ''){
			$sql = "
				SELECT 
					t_item_notas.itn_id_cbn, 
					t_item_notas.itn_cod_barras,
					t_cabecalho_notas.cbn_num_nota,
					t_cabecalho_notas.cbn_data_emissao, 
					t_cabecalho_notas.cbn_nome_emitente, 
					t_cabecalho_notas.cbn_nome_destinatario, 
					t_cabecalho_notas.cbn_qtd_item, 
					t_cabecalho_notas.cbn_vlr_nota, 
					t_volumes.vol_conferencia,
					SUM(t_item_notas.itn_vlr_liq) AS total_volume,
					SUM(t_item_notas.itn_qtd_ven) AS qtd_volume	
				FROM t_item_notas
				LEFT JOIN t_cabecalho_notas ON t_cabecalho_notas.cbn_id = t_item_notas.itn_id_cbn
				LEFT JOIN t_volumes ON t_volumes.vol_cod_barras = t_item_notas.itn_cod_barras
				WHERE t_cabecalho_notas.cbn_data_emissao BETWEEN '" . $data_inicial . "' AND '" . $data_final . "'
					AND t_cabecalho_notas.cbn_num_nota = '" .  $nota_fiscal . "'
				GROUP BY t_item_notas.itn_id_cbn, 
					t_item_notas.itn_cod_barras,
					t_cabecalho_notas.cbn_num_nota,
					t_cabecalho_notas.cbn_data_emissao, 
					t_cabecalho_notas.cbn_nome_emitente, 
					t_cabecalho_notas.cbn_nome_destinatario, 
					t_cabecalho_notas.cbn_qtd_item, 
					t_cabecalho_notas.cbn_vlr_nota,
					t_volumes.vol_conferencia
				ORDER BY t_item_notas.itn_id_cbn, t_item_notas.itn_cod_barras
				";
		}else{
			$sql = "
			SELECT 
				t_item_notas.itn_id_cbn, 
				t_item_notas.itn_cod_barras,
				t_cabecalho_notas.cbn_num_nota,
				t_cabecalho_notas.cbn_data_emissao, 
				t_cabecalho_notas.cbn_nome_emitente, 
				t_cabecalho_notas.cbn_nome_destinatario, 
				t_cabecalho_notas.cbn_qtd_item, 
				t_cabecalho_notas.cbn_vlr_nota, 
				t_volumes.vol_conferencia,
				SUM(t_item_notas.itn_vlr_liq) AS total_volume,
				SUM(t_item_notas.itn_qtd_ven) AS qtd_volume	
			FROM t_item_notas
			LEFT JOIN t_cabecalho_notas ON t_cabecalho_notas.cbn_id = t_item_notas.itn_id_cbn
			LEFT JOIN t_volumes ON t_volumes.vol_cod_barras = t_item_notas.itn_cod_barras
			WHERE t_cabecalho_notas.cbn_data_emissao BETWEEN '" . $data_inicial . "' AND '" . $data_final . "' 
			GROUP BY t_item_notas.itn_id_cbn, 
				t_item_notas.itn_cod_barras,
				t_cabecalho_notas.cbn_num_nota,
				t_cabecalho_notas.cbn_data_emissao, 
				t_cabecalho_notas.cbn_nome_emitente, 
				t_cabecalho_notas.cbn_nome_destinatario, 
				t_cabecalho_notas.cbn_qtd_item, 
				t_cabecalho_notas.cbn_vlr_nota,
				t_volumes.vol_conferencia
			ORDER BY t_item_notas.itn_id_cbn, t_item_notas.itn_cod_barras
			";
		}
		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else 
		{
			return false;
		}
	}
	
	/*============================================================================================*/
	/**
	 *| Lista todos os volumes que contem Medicamentos controlados para a NF informada
	 *| Tabela: t_volumes
	 */
	function obtem_volumes_controlados($nota_fiscal)
	{
		// Monta lista com controlados
		$id_controlados = array('6', '7', '8', '20');

		$this->db->select("t_item_notas.itn_id_cbn, t_item_notas.itn_cod_barras");
		$this->db->from("t_item_notas");
		$this->db->join("t_produtos", "t_produtos.pro_id = t_item_notas.itn_id_pro", "LEFT");
		$this->db->join("t_cabecalho_notas", "t_cabecalho_notas.cbn_id = t_item_notas.itn_id_cbn", "LEFT");
		$this->db->where("t_cabecalho_notas.cbn_num_nota", $nota_fiscal);
		$this->db->where_in("t_produtos.pro_cla_proc", $id_controlados);
		$this->db->group_by(array("t_item_notas.itn_id_cbn", "t_item_notas.itn_cod_barras"));
		$this->db->order_by("t_item_notas.itn_id_cbn ASC, t_item_notas.itn_cod_barras ASC");

		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Muda o tipo de status da conferencia para o volume e nota informados
	 *| Tabela: t_volumes
	 */
	function mudar_status_conferencia($vol_cod_barras, $vol_id_cbn, $vol_conferencia)
	{
		$this->db->set('vol_conferencia', $vol_conferencia);
		$this->db->where('vol_cod_barras', $vol_cod_barras);
		$this->db->where('vol_id_cbn', $vol_id_cbn);

		if($this->db->update('t_volumes'))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}

	/*============================================================================================*/
	/**
	 *| Obtem os dados de conferencias a serem realizadas por filial e "EM TRÂNSITO"
	 *| Tabela: t_volumes, t_cabecalho_notas
	 */
	function obtem_conferencias_filial($filial)
	{
		/*$sql = "select cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras
				,DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
				from (
					select cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras, 
						(select count(*) from t_recebimentos 
						where cbn_id = rcb_cbn_id and vol_cod_barras = rcb_volume) cont
						,tra_entrega
					from t_volumes
					join t_cabecalho_notas on cbn_id = vol_id_cbn
					LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
					where cbn_status = 'ENTREGUE'
					and vol_cod_barras != 'CROSS'
					and cbn_id_fil = ".$filial."
					and vol_conferencia = 'SIM'
					and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 3
								 
					union
								
					select cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras,
						(select count(*) from t_recebimentos 
						where cbn_id = rcb_cbn_id and vol_cod_barras = rcb_volume) cont
						,tra_entrega
					from t_volumes
					join t_cabecalho_notas on cbn_id = vol_id_cbn
					join t_item_notas on itn_id_cbn = cbn_id
					join t_produtos on pro_id = itn_id_pro
					LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
					where cbn_status = 'ENTREGUE'
					and vol_cod_barras != 'CROSS'
					and cbn_id_fil = ".$filial."
					and vol_controlado = 'S'
					and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 3
				) r
				where cont <= 0
				group by cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras, tra_entrega";*/
		$sql = "
		select cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras
						,DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
						from (
							select cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras, 
								(select count(*) from t_recebimentos 
								where cbn_id = rcb_cbn_id and vol_cod_barras = rcb_volume) cont
								,tra_entrega
							from t_volumes
							join t_cabecalho_notas on cbn_id = vol_id_cbn
							join t_filial on fil_id = cbn_id_fil
							LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
							where cbn_status = 'ENTREGUE'
							and vol_cod_barras != 'CROSS'
							and fil_num_loja = ".$filial."
							and vol_conferencia = 'SIM'
							and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 30
										 
							union
										
							select cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras,
								(select count(*) from t_recebimentos 
								where cbn_id = rcb_cbn_id and vol_cod_barras = rcb_volume) cont
								,tra_entrega
							from t_volumes
							join t_cabecalho_notas on cbn_id = vol_id_cbn
							join t_item_notas on itn_id_cbn = cbn_id
							join t_produtos on pro_id = itn_id_pro
							join t_filial on fil_id = cbn_id_fil
							LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
							where cbn_status = 'ENTREGUE'
							and vol_cod_barras != 'CROSS'
							and fil_num_loja = ".$filial."
							and vol_controlado = 'S'
							and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 30
						) r
						where cont <= 0
						group by cbn_data_emissao, cbn_hora_emissao, cbn_num_nota, cbn_nome_emitente, vol_cod_barras, tra_entrega";
		
		$query = $this->db->query($sql);

		//echo $this->db->last_query();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Obtem as informações de volume para os dados informados
	 *| Tabela: t_volumes, t_cabecalho_notas
	 */
	function obtem_dados_volume($filial, $volume)
	{
		$this->db->select('t_cabecalho_notas.cbn_data_emissao, t_cabecalho_notas.cbn_hora_emissao, t_cabecalho_notas.cbn_num_nota, t_cabecalho_notas.cbn_nome_emitente, 
			t_volumes.vol_cod_barras, t_volumes.vol_conferencia');
		$this->db->from('t_volumes');
		$this->db->join('t_cabecalho_notas', 't_cabecalho_notas.cbn_id = t_volumes.vol_id_cbn', 'LEFT');
		$this->db->join('t_filial', 't_filial.fil_id = t_cabecalho_notas.cbn_id_fil', 'INNER');
		$this->db->where('t_cabecalho_notas.cbn_status', 'ENTREGUE');
		//$this->db->where('t_cabecalho_notas.cbn_id_fil', $filial);
		$this->db->where('t_filial.fil_num_loja', $filial);
		$this->db->where('t_volumes.vol_cod_barras', $volume);
		$this->db->order_by("t_cabecalho_notas.cbn_id_fil ASC, t_cabecalho_notas.cbn_data_emissao ASC, t_cabecalho_notas.cbn_num_nota ASC");

		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}

	/*============================================================================================*/
	/**
	 *| Obtem dados do grid de recebimentos
	 *| Tabela: aux.t_recebimentos, aux.t_recebimentos_avaria, aux.t_recebimentos_vencidos, t_produtos
	 */
	function obtem_dados_grid($volume)
	{
		$sql = "
			SELECT	
				r.rec_id, 
				r.rec_nota_fiscal,
				r.rec_volume,
				r.rec_data_recebimento,
				r.rec_produto,
				r.rec_produto_nome,
				p.pro_cod_pro_cli,
				r.rec_quantidade,
				(SELECT ISNULL(SUM(recava_quantidade), 0) AS TOTAL FROM aux.t_recebimentos_avaria WHERE recava_id_recebimento = r.rec_id) AS 'total_avarias',
				(SELECT ISNULL(SUM(recven_quantidade), 0) AS TOTAL FROM aux.t_recebimentos_vencidos WHERE recven_id_recebimento = r.rec_id) AS 'total_vencidos',
				/*pf.pro_id_sessao,*/
				(SELECT coalesce(count(reccon_id_recebimento), 0) AS TOTAL_LOTE FROM aux.t_recebimentos_controlados WHERE reccon_id_recebimento = r.rec_id) AS 'total_lote'
			FROM aux.t_recebimentos r
			LEFT JOIN t_produtos p ON p.pro_id = r.rec_produto
			/*JOIN pfarma.far.t_produto pf on pf.pro_cod_pro_cli = p.pro_cod_pro_cli*/
			WHERE r.rec_volume = '" . $volume . "'
			ORDER BY r.rec_data_recebimento DESC";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}

	/*============================================================================================*/
	/**
	 *| Obtem dados do grid de recebimentos
	 *| Tabela: aux.t_recebimentos, aux.t_recebimentos_avaria, aux.t_recebimentos_vencidos, t_produtos
	 */
	function obtem_dados_grid_chave($chave)
	{
		$sql = "
			SELECT	
				r.rec_id, 
				r.rec_nota_fiscal,
				r.rec_volume,
				r.rec_chave_nota,
				r.rec_data_recebimento,
				r.rec_produto,
				r.rec_produto_nome,
				p.pro_cod_pro_cli,
				p.pro_venda_controlada,
				r.rec_quantidade,
				(SELECT ISNULL(SUM(recava_quantidade), 0) AS TOTAL FROM aux.t_recebimentos_avaria WHERE recava_id_recebimento = r.rec_id) AS 'total_avarias',
				(SELECT ISNULL(SUM(recven_quantidade), 0) AS TOTAL FROM aux.t_recebimentos_vencidos WHERE recven_id_recebimento = r.rec_id) AS 'total_vencidos',
				/*pf.pro_id_sessao,*/
				(SELECT coalesce(count(reccon_id_recebimento), 0) AS TOTAL_LOTE FROM aux.t_recebimentos_controlados WHERE reccon_id_recebimento = r.rec_id) AS 'total_lote'
			FROM aux.t_recebimentos r
			LEFT JOIN t_produtos p ON p.pro_id = r.rec_produto
			/*JOIN pfarma.far.t_produto pf on pf.pro_cod_pro_cli = p.pro_cod_pro_cli*/
			WHERE r.rec_chave_nota = '" . $chave . "'
			ORDER BY r.rec_data_recebimento DESC";

		$query = $this->db->query($sql);

		//echo $this->db->last_query();

		

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}

		/*============================================================================================*/
	/**
	 *| Obtem dados do grid de recebimentos
	 */

	function grid_chave($chave)
	{


		$sql = "SELECT  pro_cod_pro_cli,pro_descricao,itn_qtd_ven FROM t_cabecalho_notas as c
			JOIN t_item_notas as i on c.cbn_id = i.itn_id_cbn
			JOIN t_produtos as p on p.pro_id = i.itn_id_pro
			where c.cbn_chave_nota = '" . $chave . "'";

		$query = $this->db->query($sql);
		

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}

	/*============================================================================================*/
	/**
	 *| Obtem dados da conferencia por EAN
	 *| Tabela: 
	 */
	function obtem_conferencia_ean($nota, $volume, $ean = '')
	{
		$this->db->select('t_cabecalho_notas.cbn_id,
				t_cabecalho_notas.cbn_num_nota,
				t_item_notas.itn_id, 		
				t_item_notas.itn_cod_barras, 
				t_item_notas.itn_id_pro,
				t_produtos.pro_descricao');
		$this->db->from('t_cabecalho_notas');
		$this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
		$this->db->join('t_eans', 't_eans.ean_id_pro = t_item_notas.itn_id_pro', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
		$this->db->where('t_cabecalho_notas.cbn_num_nota', $nota);
		$this->db->where('t_item_notas.itn_cod_barras', $volume);
		//$this->db->where('t_eans.ean_cod', $ean);

		if (!empty($ean)) { // considera EAN na busca apenas se for enviado
			$this->db->where('t_eans.ean_cod', $ean);
		}

		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Obtem dados da conferencia por EAN em fornecedores externos
	 *| Tabela: 
	 */
	function obtem_conferencia_ean_externo($nota, $chave, $ean = '')
	{
		$this->db->select('t_cabecalho_notas.cbn_id,
				t_cabecalho_notas.cbn_num_nota,
				t_cabecalho_notas.cbn_chave_nota,
				t_item_notas.itn_id, 		
				t_item_notas.itn_cod_barras, 
				t_item_notas.itn_id_pro,
				t_produtos.pro_descricao');
		$this->db->from('t_cabecalho_notas');
		$this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
		$this->db->join('t_eans', 't_eans.ean_id_pro = t_item_notas.itn_id_pro', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
		$this->db->where('t_cabecalho_notas.cbn_num_nota', $nota);
		$this->db->where('t_cabecalho_notas.cbn_chave_nota', $chave);
		//$this->db->where('t_eans.ean_cod', $ean); // isto estava comentado, mas faz sentido aplicar



		if (!empty($ean)) { // considera EAN na busca apenas se for enviado
			$this->db->where('t_eans.ean_cod', $ean);
		}

		$query = $this->db->get();



		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			

			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Obtem dados da conferencia por produto
	 *| Tabela: 
	 */
	function obtem_conferencia_produto($nota, $volume, $produto = '')
	{
		$this->db->select('t_cabecalho_notas.cbn_id,
				t_cabecalho_notas.cbn_num_nota,
				t_item_notas.itn_id, 
				t_item_notas.itn_cod_barras, 
				t_item_notas.itn_id_pro,
				t_produtos.pro_descricao');
		$this->db->from('t_cabecalho_notas');
		$this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
		$this->db->where('t_cabecalho_notas.cbn_num_nota', $nota);
		$this->db->where('t_item_notas.itn_cod_barras', $volume);
		//$this->db->where('t_produtos.pro_cod_pro_cli', $produto);

		if (!empty($produto)) { // considera EAN na busca apenas se for enviado
			$this->db->where('t_produtos.pro_cod_pro_cli', $produto);
		}

		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Obtem dados da conferencia por produto
	 *| Tabela: 
	 */
	function obtem_conferencia_produto_externo($nota, $chave, $produto = '')
	{
		$this->db->select('t_cabecalho_notas.cbn_id,
				t_cabecalho_notas.cbn_num_nota,
				t_item_notas.itn_id, 
				t_cabecalho_notas.cbn_chave_nota, 
				t_item_notas.itn_id_pro,
				t_produtos.pro_descricao');
		$this->db->from('t_cabecalho_notas');
		$this->db->join('t_item_notas', 't_item_notas.itn_id_cbn = t_cabecalho_notas.cbn_id', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = t_item_notas.itn_id_pro', 'INNER');
		$this->db->where('t_cabecalho_notas.cbn_num_nota', $nota);
		$this->db->where('t_cabecalho_notas.cbn_chave_nota', $chave);
		//$this->db->where('t_produtos.pro_cod_pro_cli', $produto);  // isto estava comentado, mas faz sentido aplicar

		if (!empty($produto)) { // considera EAN na busca apenas se for enviado
			$this->db->where('t_produtos.pro_cod_pro_cli', $produto);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Grava dados da conferencia realizada em tabela temporária
	 *| Tabela: aux.t_recebimentos
	 */
	function inserir_conferencia_temporaria($cnf_nota_fiscal, $cnf_volume, $cnf_produto_id, $cnf_produto_dsc, $cnf_cbn_id, $cnf_itn_id)
	{

		$dt = date("Y-m-d H:m:i.v");     

		// Verifica se registro já existe
		$this->db->where('rec_nota_fiscal', $cnf_nota_fiscal);
		$this->db->where('rec_volume', $cnf_volume);
		$this->db->where('rec_produto', $cnf_produto_id);
		$query = $this->db->get('aux.t_recebimentos');
		
		if($query->num_rows()>0)
		{
			$row = $query->row();
			$qtd = $row->rec_quantidade;

			// Atualiza registro existente
			$this->db->set('rec_quantidade', $qtd + 1);
			$this->db->set('rec_cbn_id', $cnf_cbn_id);
			$this->db->set('rec_itn_id', $cnf_itn_id);
			$this->db->set('rec_data_recebimento', str_replace(" ", "T", $dt));
			$this->db->where('rec_nota_fiscal', $cnf_nota_fiscal);
			$this->db->where('rec_volume', $cnf_volume);
			$this->db->where('rec_produto', $cnf_produto_id);

			if($this->db->update('aux.t_recebimentos'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			// Insere novo registro
			$this->db->set('rec_nota_fiscal', $cnf_nota_fiscal);
			$this->db->set('rec_volume', $cnf_volume);
			$this->db->set('rec_data_recebimento', str_replace(" ", "T", $dt));
			$this->db->set('rec_produto', $cnf_produto_id);
			$this->db->set('rec_produto_nome', $cnf_produto_dsc);
			$this->db->set('rec_quantidade', 1);
			$this->db->set('rec_cbn_id', $cnf_cbn_id);
			$this->db->set('rec_itn_id', $cnf_itn_id); 	

			if($this->db->insert('aux.t_recebimentos'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/*============================================================================================*/
	/**
	 *| Grava dados da conferencia realizada em tabela temporária
	 *| Tabela: aux.t_recebimentos
	 */
	function inserir_conferencia_externa_temporaria($cnf_nota_fiscal, $cbn_chave_nota, $cnf_produto_id, $cnf_produto_dsc, $cnf_cbn_id, $cnf_itn_id)
	{

		$dt = date("Y-m-d H:m:i.v");     

		// Verifica se registro já existe
		$this->db->where('rec_nota_fiscal', $cnf_nota_fiscal);
		$this->db->where('rec_chave_nota', $cbn_chave_nota);
		$this->db->where('rec_produto', $cnf_produto_id);
		$query = $this->db->get('aux.t_recebimentos');

		//echo $this->db->last_query();

		
		if($query->num_rows()>0)
		{
			$row = $query->row();
			$qtd = $row->rec_quantidade;
			$date =  new DateTime();
			$n = $date->format("Y-m-d H:i:s.v");

			// Atualiza registro existente
			$this->db->set('rec_quantidade', $qtd + 1);
			$this->db->set('rec_cbn_id', $cnf_cbn_id);
			$this->db->set('rec_itn_id', $cnf_itn_id);
			$this->db->set('rec_data_recebimento', str_replace(" ", "T", $n));
			$this->db->where('rec_nota_fiscal', $cnf_nota_fiscal);
			$this->db->where('rec_chave_nota', $cbn_chave_nota);
			$this->db->where('rec_produto', $cnf_produto_id);

			if($this->db->update('aux.t_recebimentos'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$date =  new DateTime();
			$n = $date->format("Y-m-d H:i:s.v");

			// Insere novo registro
			$this->db->set('rec_nota_fiscal', $cnf_nota_fiscal);
			$this->db->set('rec_chave_nota', $cbn_chave_nota);
			$this->db->set('rec_volume', '');
			$this->db->set('rec_data_recebimento', str_replace(" ", "T", $n));
			$this->db->set('rec_produto', $cnf_produto_id);
			$this->db->set('rec_produto_nome', $cnf_produto_dsc);
			$this->db->set('rec_quantidade', 1);
			$this->db->set('rec_cbn_id', $cnf_cbn_id);
			$this->db->set('rec_itn_id', $cnf_itn_id); 	

			if($this->db->insert('aux.t_recebimentos'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	

	/*============================================================================================*/
	/**
	 *| Obtem os daods de avarias para o registro informado
	 *| Tabela: aux.t_recebimentos, t_produtos
	 */
	function obtem_dados_avarias($recebimento_id)
	{	
		$sql = "
		SELECT	
			r.rec_id, 
			r.rec_nota_fiscal,
			r.rec_volume,
			r.rec_data_recebimento,
			r.rec_produto,
			r.rec_produto_nome,
			r.rec_quantidade, 
			p.pro_cod_pro_cli,
			(SELECT ISNULL(SUM(recava_quantidade), 0) as TOTAL 
				FROM aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento = r.rec_id
				AND recava_tipo = 'MANCHADO' ) AS 'total_manchado',
			(SELECT ISNULL(SUM(recava_quantidade), 0) as TOTAL 
				FROM aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento = r.rec_id
				AND recava_tipo = 'AMASSADO' ) AS 'total_amassado',
			(SELECT ISNULL(SUM(recava_quantidade), 0) as TOTAL 
				FROM aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento = r.rec_id
				AND recava_tipo = 'RASGADO' ) AS 'total_rasgado',
			(SELECT ISNULL(SUM(recava_quantidade), 0) as TOTAL 
				FROM aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento = r.rec_id
				AND recava_tipo = 'QUEBRADO' ) AS 'total_quebrado',
			(SELECT ISNULL(SUM(recava_quantidade), 0) as TOTAL 
				FROM aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento = r.rec_id
				AND recava_tipo = 'EMBALAGEM VIOLADA' ) AS 'total_violado',
			(SELECT ISNULL(SUM(recven_quantidade), 0) as TOTAL 
				FROM aux.t_recebimentos_vencidos
				WHERE recven_id_recebimento = r.rec_id) AS 'total_vencidos'
		FROM aux.t_recebimentos r
		LEFT JOIN t_produtos p ON p.pro_id = r.rec_produto
		WHERE r.rec_id = " . $recebimento_id . ";
		";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Grava dados informados no modal de avarias
	 *| - Insere dados se não existir
	 *| - Atualiza dados se existir, e quantidade for maior que zero
	 *| - Apaga dados se quantidade for igual a 0
	 *| Tabela: aux.t_recebimentos_avaria
	 */
	function gravar_modal_avarias($recava_id_recebimento, $recava_tipo, $recava_quantidade)
	{
		// Verifica se registro já existe
		$this->db->where('recava_id_recebimento', $recava_id_recebimento);
		$this->db->where('recava_tipo', $recava_tipo);
		$query = $this->db->get('aux.t_recebimentos_avaria');

		if($query->num_rows()>0)
		{
			if($recava_quantidade > 0)
			{
				// Atualiza registro existente
				$this->db->set('recava_quantidade', $recava_quantidade);
				$this->db->where('recava_id_recebimento', $recava_id_recebimento);
				$this->db->where('recava_tipo', $recava_tipo);

				if($this->db->update('aux.t_recebimentos_avaria'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				// Apaga registro existente
				$this->db->where('recava_id_recebimento', $recava_id_recebimento);
				$this->db->where('recava_tipo', $recava_tipo);

				if($this->db->delete('aux.t_recebimentos_avaria'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}

		}
		else
		{
			if($recava_quantidade > 0)
			{
				// Insere novo registro
				$this->db->set('recava_quantidade', $recava_quantidade);
				$this->db->set('recava_id_recebimento', $recava_id_recebimento);
				$this->db->set('recava_tipo', $recava_tipo);

				if($this->db->insert('aux.t_recebimentos_avaria'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}

	/*============================================================================================*/
	/**
	 *| Obtem dados de produtos vencidos para o registro informado
	 *| Tabela: aux.t_recebimentos, t_produtos, aux.t_recebimentos_vencidos
	 */
	function obtem_dados_vencidos($recebimento_id)
	{
		$sql = "
		SELECT	t_recebimentos.rec_id, 
				t_recebimentos.rec_nota_fiscal, 
				t_recebimentos.rec_volume, 
				t_recebimentos.rec_data_recebimento, 
				t_recebimentos.rec_produto, 
				t_recebimentos.rec_produto_nome, 
				t_recebimentos.rec_quantidade,
				t_produtos.pro_cod_pro_cli, 
				aux.t_recebimentos_vencidos.recven_data, 
				ISNULL(aux.t_recebimentos_vencidos.recven_quantidade, 0) as recven_quantidade,
				(SELECT ISNULL(SUM(recava_quantidade), 0) as TOTAL FROM aux.t_recebimentos_avaria WHERE recava_id_recebimento = t_recebimentos.rec_id) AS 'total_avarias'
		FROM aux.t_recebimentos
		LEFT JOIN t_produtos ON t_produtos.pro_id = aux.t_recebimentos.rec_produto
		LEFT JOIN aux.t_recebimentos_vencidos ON aux.t_recebimentos_vencidos.recven_id_recebimento = aux.t_recebimentos.rec_id
		WHERE aux.t_recebimentos.rec_id = " . $recebimento_id . "
		";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	/*============================================================================================*/
	/**
	 *| Grava dados informados no modal de vencidos
	 *| Tabela: aux.t_recebimentos_vencidos
	 */
	function gravar_modal_vencimento($recven_id_recebimento, $recven_data, $recven_quantidade)
	{
		// Verifica se registro já existe
		$this->db->where('recven_id_recebimento', $recven_id_recebimento);
		//$this->db->where('recven_data', $recven_data);
		$query = $this->db->get('aux.t_recebimentos_vencidos');

		if($query->num_rows()>0)
		{
			if($recven_quantidade > 0)
			{
				// Atualiza registro existente
				$this->db->set('recven_quantidade', $recven_quantidade);
				$this->db->set('recven_data', $recven_data);
				$this->db->where('recven_id_recebimento', $recven_id_recebimento);			

				if($this->db->update('aux.t_recebimentos_vencidos'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				// Apaga registro existente
				$this->db->where('recven_id_recebimento', $recven_id_recebimento);			

				if($this->db->delete('aux.t_recebimentos_vencidos'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			if($recven_quantidade > 0)
			{
				// Insere novo registro
				$this->db->set('recven_id_recebimento', $recven_id_recebimento);
				$this->db->set('recven_data', $recven_data);
				$this->db->set('recven_quantidade', $recven_quantidade);

				if($this->db->insert('aux.t_recebimentos_vencidos'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}


	/*============================================================================================*/
	/**
	 *| Atualiza quantidade de produtos recebidos no grid de recebimentos
	 *| Tabela: aux.t_recebimentos
	 */
	function atualizar_recebimento_qtd($recebimento_id, $quantidade)
	{
		$this->db->set('rec_quantidade', $quantidade);
		$this->db->where('rec_id', $recebimento_id);
		if($this->db->update('aux.t_recebimentos'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function valida_nota_interna($chave)
	{
		
		$this->db->select('cbn_qtd_item');
		$this->db->from('t_cabecalho_notas');
		$this->db->where('cbn_chave_nota', $chave);
		$this->db->where('cbn_qtd_item', NULL);
		$query = $this->db->get();

		if($query->num_rows()>0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos, t_produtos, aux.t_recebimentos_controlados
	 */
	function obtem_dados_controlados($recebimento_id)
	{
		$this->db->select('t_recebimentos.rec_id, t_recebimentos.rec_nota_fiscal, t_recebimentos.rec_volume, 
			t_recebimentos.rec_data_recebimento, t_recebimentos.rec_produto, t_recebimentos.rec_produto_nome,
			t_recebimentos.rec_quantidade, t_produtos.pro_cod_pro_cli, t_item_notas.itn_fabricacao, t_item_notas.itn_validade');
		$this->db->from('aux.t_recebimentos');
		$this->db->join('t_produtos', 't_produtos.pro_id = aux.t_recebimentos.rec_produto', 'LEFT');
		$this->db->join('t_item_notas', 't_item_notas.itn_id = aux.t_recebimentos.rec_itn_id', 'LEFT');
		$this->db->where('aux.t_recebimentos.rec_id', $recebimento_id);
		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados
	 */
	function obtem_dados_grid_controlados($recebimento_id)
	{
		$this->db->where('reccon_id_recebimento', $recebimento_id);
		$query = $this->db->get('aux.t_recebimentos_controlados');
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados
	 */
	function inserir_conferencia_controlados($recebimento, $lote, $fabricacao, $validade, $quantidade)
	{
		// Verifica se registro já existe
		$this->db->where('reccon_id_recebimento', $recebimento);
		$this->db->where('reccon_lote', $lote);
		$query = $this->db->get('aux.t_recebimentos_controlados');

		if($query->num_rows()>0)
		{
			// Atualiza registro existente
			$this->db->set('reccon_data_fabricacao', $fabricacao);
			$this->db->set('reccon_data_validade', $validade);
			$this->db->set('reccon_quantidade', $quantidade);
			$this->db->where('reccon_id_recebimento', $recebimento);
			$this->db->where('reccon_lote', $lote);

			if($this->db->update('aux.t_recebimentos_controlados'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			// Insere novo registro
			$this->db->set('reccon_id_recebimento', $recebimento);
			$this->db->set('reccon_lote', $lote);
			$this->db->set('reccon_data_fabricacao', $fabricacao);
			$this->db->set('reccon_data_validade', $validade);
			$this->db->set('reccon_quantidade', $quantidade);			

			if($this->db->insert('aux.t_recebimentos_controlados'))
			{
				return true;
			}
			else
			{
				return false;
			}


		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados
	 */
	function obtem_qtd_controlado($reccon_id)
	{

		$this->db->select('aux.t_recebimentos_controlados.reccon_id, aux.t_recebimentos_controlados.reccon_quantidade, aux.t_recebimentos.rec_quantidade');
		$this->db->from('aux.t_recebimentos_controlados');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_controlados.reccon_id_recebimento', 'INNER');
		$this->db->where('reccon_id', $reccon_id);
		$query = $this->db->get();

		if($query->num_rows() > 0)
		{
			$row = $query->row();
			//$qtd = $row->rec_quantidade - $row->reccon_quantidade;
			//$qtd = $row->rec_quantidade - $qtd;
			return $row->reccon_quantidade;
		}
		else
		{
			//return $sql;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados
	 */
	function excluir_controlados_grid($reccon_id)
	{
		$this->db->where('reccon_id', $reccon_id);
		if($this->db->delete('aux.t_recebimentos_controlados'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados, aux.t_recebimentos_vencidos, aux.t_recebimentos_avaria, aux.t_recebimentos 
	 */
	function reiniciar_conferencia_volume($volume)
	{
		$sql = "DELETE aux.t_recebimentos_controlados 
				WHERE reccon_id_recebimento IN (SELECT rec_id FROM aux.t_recebimentos WHERE rec_volume = '" . $volume . "')";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos_vencidos 
				WHERE recven_id_recebimento	IN (SELECT rec_id FROM aux.t_recebimentos WHERE rec_volume = '" . $volume . "')";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento IN (SELECT rec_id FROM aux.t_recebimentos WHERE rec_volume = '" . $volume . "')";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos
				WHERE rec_volume = '" . $volume . "'";
		$this->db->query($sql);
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados, aux.t_recebimentos_vencidos, aux.t_recebimentos_avaria, aux.t_recebimentos 
	 */
	function reiniciar_conferencia_chave($chave)
	{
		$sql = "DELETE aux.t_recebimentos_controlados 
				WHERE reccon_id_recebimento IN (SELECT rec_id FROM aux.t_recebimentos WHERE rec_chave_nota = '" . $chave . "')";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos_vencidos 
				WHERE recven_id_recebimento	IN (SELECT rec_id FROM aux.t_recebimentos WHERE rec_chave_nota = '" . $chave . "')";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento IN (SELECT rec_id FROM aux.t_recebimentos WHERE rec_chave_nota = '" . $chave . "')";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos
				WHERE rec_chave_nota = '" . $chave . "'";
		$this->db->query($sql);


	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_controlados, aux.t_recebimentos_vencidos, aux.t_recebimentos_avaria, aux.t_recebimentos 
	 */
	function apagar_registro_conferencia($id_recebimento)
	{
		$sql = "DELETE aux.t_recebimentos_controlados 
				WHERE reccon_id_recebimento = '" . $id_recebimento . "'";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos_vencidos 
				WHERE recven_id_recebimento = '" . $id_recebimento . "'";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos_avaria 
				WHERE recava_id_recebimento = '" . $id_recebimento . "'";
		$this->db->query($sql);

		$sql = "DELETE aux.t_recebimentos
				WHERE rec_id = '" . $id_recebimento . "'";
		$this->db->query($sql);
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_avaria, aux.t_recebimentos
	 */
	function obtem_avarias_volume($volume)
	{
		$this->db->select('t_produtos.pro_cod_pro_cli, aux.t_recebimentos.rec_id, aux.t_recebimentos.rec_volume, aux.t_recebimentos.rec_produto, aux.t_recebimentos.rec_produto_nome, 
			aux.t_recebimentos_avaria.recava_quantidade, aux.t_recebimentos_avaria.recava_tipo');
		$this->db->from('aux.t_recebimentos_avaria');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_avaria.recava_id_recebimento', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = aux.t_recebimentos.rec_produto', 'LEFT');
		$this->db->where('aux.t_recebimentos.rec_volume', $volume);
		$this->db->order_by('aux.t_recebimentos_avaria.recava_tipo');
		$this->db->order_by('aux.t_recebimentos.rec_produto');
		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_avaria, aux.t_recebimentos
	 */
	function obtem_avarias_nota($chave)
	{
		$this->db->select('t_produtos.pro_cod_pro_cli, aux.t_recebimentos.rec_id, aux.t_recebimentos.rec_chave_nota, aux.t_recebimentos.rec_produto, aux.t_recebimentos.rec_produto_nome, 
			aux.t_recebimentos_avaria.recava_quantidade, aux.t_recebimentos_avaria.recava_tipo');
		$this->db->from('aux.t_recebimentos_avaria');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_avaria.recava_id_recebimento', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = aux.t_recebimentos.rec_produto', 'LEFT');
		$this->db->where('aux.t_recebimentos.rec_chave_nota', $chave);
		$this->db->order_by('aux.t_recebimentos_avaria.recava_tipo');
		$this->db->order_by('aux.t_recebimentos.rec_produto');
		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_vencidos, aux.t_recebimento
	 */
	function obtem_vencidos_volume($volume)
	{
		$this->db->select('t_produtos.pro_cod_pro_cli, aux.t_recebimentos.rec_id, aux.t_recebimentos.rec_volume, aux.t_recebimentos.rec_produto, aux.t_recebimentos.rec_produto_nome, 
		aux.t_recebimentos_vencidos.recven_data, aux.t_recebimentos_vencidos.recven_quantidade');
		$this->db->from('aux.t_recebimentos_vencidos');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_vencidos.recven_id_recebimento', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = aux.t_recebimentos.rec_produto', 'LEFT');
		$this->db->where('aux.t_recebimentos.rec_volume', $volume);
		$this->db->order_by('aux.t_recebimentos.rec_produto');
		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_vencidos, aux.t_recebimento
	 */
	function obtem_vencidos_nota($chave)
	{
		$this->db->select('t_produtos.pro_cod_pro_cli, aux.t_recebimentos.rec_id, aux.t_recebimentos.rec_chave_nota, aux.t_recebimentos.rec_produto, aux.t_recebimentos.rec_produto_nome, 
		aux.t_recebimentos_vencidos.recven_data, aux.t_recebimentos_vencidos.recven_quantidade');
		$this->db->from('aux.t_recebimentos_vencidos');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_vencidos.recven_id_recebimento', 'INNER');
		$this->db->join('t_produtos', 't_produtos.pro_id = aux.t_recebimentos.rec_produto', 'LEFT');
		$this->db->where('aux.t_recebimentos.rec_chave_nota', $chave);
		$this->db->order_by('aux.t_recebimentos.rec_produto');
		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: t_item_notas, t_produtos
	 */
	function obtem_sobra_falta_volume($volume)
	{
		$sql = "
		SELECT	i.itn_id_pro,
				p.pro_cod_pro_cli,
				p.pro_descricao,
				(SUM(i.itn_qtd_ven) * COALESCE(f.fat_quantidade, 1)) AS itn_qtd_ven,
				(
					SELECT	ISNULL(SUM(r.rec_quantidade), 0) AS rec_quantidade
					FROM	aux.t_recebimentos r
					WHERE	i.itn_cod_barras = r.rec_volume AND r.rec_produto = i.itn_id_pro
				) AS rec_quantidade
		FROM t_item_notas i
		JOIN t_cabecalho_notas c ON c.cbn_id = i.itn_id_cbn
		LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
		LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
		WHERE i.itn_cod_barras = '$volume' 
		GROUP BY i.itn_id_pro,
				p.pro_cod_pro_cli,
				p.pro_descricao,
				fat_quantidade,
				itn_cod_barras
		UNION
		select 
				p.pro_id as itn_id_pro,
				p.pro_cod_pro_cli,
				p.pro_descricao,
				0 as itn_qtd_ven,
				rec_quantidade
		from aux.t_recebimentos
		JOIN t_cabecalho_notas c on cbn_id = rec_cbn_id
		LEFT JOIN t_fator_conversao f ON f.fat_id_pro = rec_produto AND f.fat_cnpj = c.cbn_cnpj_emitente
		LEFT JOIN t_produtos p ON p.pro_id = rec_produto
		where not exists(
			SELECT	
					p.pro_cod_pro_cli
			FROM t_item_notas i
			JOIN t_cabecalho_notas c on cbn_id = itn_id_cbn
			LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
			LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
			WHERE i.itn_cod_barras = rec_volume and rec_produto = p.pro_id
		) and rec_volume = '$volume'";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: 
	 */
	function obtem_controlados_volume($volume)
	{
		$sql = "
			SELECT
				aux.t_recebimentos.rec_cbn_id,
				aux.t_recebimentos.rec_itn_id,
				aux.t_recebimentos.rec_nota_fiscal,
				t_cabecalho_notas.cbn_data_emissao,
				t_cabecalho_notas.cbn_cnpj_emitente,
				t_produtos.pro_cod_pro_cli,
				aux.t_recebimentos_controlados.reccon_quantidade,
				aux.t_recebimentos.rec_produto_nome,
				aux.t_recebimentos_controlados.reccon_lote,
				aux.t_recebimentos_controlados.reccon_data_validade
			FROM aux.t_recebimentos_controlados
			JOIN aux.t_recebimentos ON aux.t_recebimentos.rec_id = aux.t_recebimentos_controlados.reccon_id_recebimento
			JOIN t_cabecalho_notas ON t_cabecalho_notas.cbn_id = aux.t_recebimentos.rec_cbn_id
			JOIN t_produtos ON t_produtos.pro_id = aux.t_recebimentos.rec_produto
			WHERE aux.t_recebimentos.rec_volume = '". $volume ."'
			ORDER BY aux.t_recebimentos.rec_produto, aux.t_recebimentos_controlados.reccon_lote";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: 
	 */
	function obtem_controlados_nota($chave)
	{
		$sql = "
			SELECT
				aux.t_recebimentos.rec_cbn_id,
				aux.t_recebimentos.rec_itn_id,
				aux.t_recebimentos.rec_nota_fiscal,
				t_cabecalho_notas.cbn_data_emissao,
				t_cabecalho_notas.cbn_cnpj_emitente,
				t_produtos.pro_cod_pro_cli,
				aux.t_recebimentos_controlados.reccon_quantidade,
				aux.t_recebimentos.rec_produto_nome,
				aux.t_recebimentos_controlados.reccon_lote,
				aux.t_recebimentos_controlados.reccon_data_validade
			FROM aux.t_recebimentos_controlados
			JOIN aux.t_recebimentos ON aux.t_recebimentos.rec_id = aux.t_recebimentos_controlados.reccon_id_recebimento
			JOIN t_cabecalho_notas ON t_cabecalho_notas.cbn_id = aux.t_recebimentos.rec_cbn_id
			JOIN t_produtos ON t_produtos.pro_id = aux.t_recebimentos.rec_produto
			WHERE aux.t_recebimentos.rec_chave_nota = '". $chave ."'
			ORDER BY aux.t_recebimentos.rec_produto, aux.t_recebimentos_controlados.reccon_lote";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Insere dados dos controlados encontrados na tabela de exportação para o semc
	 *| Tabela: t_recebimento_semc
	 */
	function gravar_controlado_semc_produto($dados)
	{
		if($this->db->insert('t_recebimento_semc', $dados))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos
	 */
	function obtem_recebimentos_volume($volume)
	{
		$sql = "select rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota, rec_volume, rec_quantidade-coalesce(sum(rec_avarias), 0) quantidade_recebida 
				from(
				select rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota, rec_volume, coalesce(sum(recven_quantidade), 0) rec_avarias
				from aux.t_recebimentos
				left join aux.t_recebimentos_vencidos on rec_id = recven_id_recebimento
				join t_cabecalho_notas on cbn_id = rec_cbn_id
				join t_item_notas on itn_id_cbn = cbn_id and itn_id_pro = rec_produto
				where rec_volume = '$volume'
				group by rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota, rec_volume
				union
				select rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota, rec_volume, coalesce(sum(recava_quantidade), 0) rec_avarias
				from aux.t_recebimentos
				left join aux.t_recebimentos_avaria on rec_id = recava_id_recebimento
				join t_cabecalho_notas on cbn_id = rec_cbn_id
				join t_item_notas on itn_id_cbn = cbn_id and itn_id_pro = rec_produto
				where rec_volume = '$volume'
				group by rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota, rec_volume
			) r
			group by rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota, rec_volume";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos
	 */
	function obtem_recebimentos_nota($chave)
	{
		$sql = "select rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota,
					case when((rec_quantidade-coalesce(sum(recava_quantidade), 0)-coalesce(sum(recven_quantidade), 0)) > 0)
					then (rec_quantidade-coalesce(sum(recava_quantidade), 0)-coalesce(sum(recven_quantidade), 0))
					else (rec_quantidade-coalesce(sum(recava_quantidade), 0)-coalesce(sum(recven_quantidade), 0)) * (-1)
					end
					as quantidade_recebida 
				from aux.t_recebimentos
				left join aux.t_recebimentos_avaria on rec_id = recava_id_recebimento
				left join aux.t_recebimentos_vencidos on rec_id = recven_id_recebimento
				join t_cabecalho_notas on cbn_id = rec_cbn_id
				where rec_chave_nota = '$chave'
				group by rec_id, rec_nota_fiscal, rec_data_recebimento,
					rec_produto, rec_produto_nome, rec_cbn_id, 
					rec_itn_id, rec_chave_nota, rec_quantidade,
					cbn_chave_nota";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_avaria, aux.t_recebimentos
	 */
	function obtem_recebimentos_avarias_volume($volume)
	{
		$this->db->select('
			aux.t_recebimentos_avaria.recava_id,
			aux.t_recebimentos_avaria.recava_quantidade,
			aux.t_recebimentos_avaria.recava_tipo,
			aux.t_recebimentos_avaria.recava_id_recebimento,
			aux.t_recebimentos.rec_id,
			aux.t_recebimentos.rec_nota_fiscal,
			aux.t_recebimentos.rec_volume, 
			aux.t_recebimentos.rec_data_recebimento, 
			aux.t_recebimentos.rec_produto,
			aux.t_recebimentos.rec_produto_nome,
			aux.t_recebimentos.rec_quantidade,
			aux.t_recebimentos.rec_cbn_id,
			aux.t_recebimentos.rec_itn_id
			');
		$this->db->from('aux.t_recebimentos_avaria');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_avaria.recava_id_recebimento','INNER');
		$this->db->where('aux.t_recebimentos.rec_volume' ,$volume);
		$this->db->order_by('aux.t_recebimentos_avaria.recava_tipo', 'ASC');
		$this->db->order_by('aux.t_recebimentos.rec_produto', 'ASC');

		$query = $this->db->get();
	
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_avaria, aux.t_recebimentos
	 */
	function obtem_recebimentos_avarias_nota($chave)
	{
		$this->db->select('
			aux.t_recebimentos_avaria.recava_id,
			aux.t_recebimentos_avaria.recava_quantidade,
			aux.t_recebimentos_avaria.recava_tipo,
			aux.t_recebimentos_avaria.recava_id_recebimento,
			aux.t_recebimentos.rec_id,
			aux.t_recebimentos.rec_nota_fiscal,
			aux.t_recebimentos.rec_chave_nota, 
			aux.t_recebimentos.rec_data_recebimento, 
			aux.t_recebimentos.rec_produto,
			aux.t_recebimentos.rec_produto_nome,
			aux.t_recebimentos.rec_quantidade,
			aux.t_recebimentos.rec_cbn_id,
			aux.t_recebimentos.rec_itn_id
			');
		$this->db->from('aux.t_recebimentos_avaria');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_avaria.recava_id_recebimento','INNER');
		$this->db->where('aux.t_recebimentos.rec_chave_nota' ,$chave);
		$this->db->order_by('aux.t_recebimentos_avaria.recava_tipo', 'ASC');
		$this->db->order_by('aux.t_recebimentos.rec_produto', 'ASC');

		$query = $this->db->get();
	
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_vencido, aux.t_recebimento
	 */
	function obtem_recebimentos_vencidos_volume($volume)
	{
		$this->db->select('
			aux.t_recebimentos_vencidos.recven_data,
			aux.t_recebimentos_vencidos.recven_quantidade,
			aux.t_recebimentos_vencidos.recven_id_recebimento,
			aux.t_recebimentos.rec_id,
			aux.t_recebimentos.rec_nota_fiscal,
			aux.t_recebimentos.rec_volume, 
			aux.t_recebimentos.rec_data_recebimento, 
			aux.t_recebimentos.rec_produto,
			aux.t_recebimentos.rec_produto_nome,
			aux.t_recebimentos.rec_quantidade,
			aux.t_recebimentos.rec_cbn_id,
			aux.t_recebimentos.rec_itn_id
			');
		$this->db->from('aux.t_recebimentos_vencidos');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_vencidos.recven_id_recebimento','INNER');
		$this->db->where('aux.t_recebimentos.rec_volume' ,$volume);
		$this->db->order_by('aux.t_recebimentos.rec_produto', 'ASC');

		$query = $this->db->get();
	
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: aux.t_recebimentos_vencido, aux.t_recebimento
	 */
	function obtem_recebimentos_vencidos_nota($chave)
	{
		$this->db->select('
			aux.t_recebimentos_vencidos.recven_data,
			aux.t_recebimentos_vencidos.recven_quantidade,
			aux.t_recebimentos_vencidos.recven_id_recebimento,
			aux.t_recebimentos.rec_id,
			aux.t_recebimentos.rec_nota_fiscal,
			aux.t_recebimentos.rec_chave_nota, 
			aux.t_recebimentos.rec_data_recebimento, 
			aux.t_recebimentos.rec_produto,
			aux.t_recebimentos.rec_produto_nome,
			aux.t_recebimentos.rec_quantidade,
			aux.t_recebimentos.rec_cbn_id,
			aux.t_recebimentos.rec_itn_id
			');
		$this->db->from('aux.t_recebimentos_vencidos');
		$this->db->join('aux.t_recebimentos', 'aux.t_recebimentos.rec_id = aux.t_recebimentos_vencidos.recven_id_recebimento','INNER');
		$this->db->where('aux.t_recebimentos.rec_chave_nota' ,$chave);
		$this->db->order_by('aux.t_recebimentos.rec_produto', 'ASC');

		$query = $this->db->get();
	
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	
	/*============================================================================================*/
	/**
	 *| grava os dados informados na tabela de recebimentos
	 *| Tabela: t_recebimentos
	 */
	function gravar_recebimento_produto($dados)
	{

		// var_dump($dados);
		$dados['rcb_data_recebimento'] = str_replace(" ", "T", $dados['rcb_data_recebimento']);
		if($this->db->insert('t_recebimentos', $dados))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*============================================================================================*/
	/**
	 *| grava os dados informados na tabela de recebimentos
	 *| Tabela: t_recebimentos_avaria
	 */
	function gravar_recebimento_avarias_produto($dados)
	{
		if($this->db->insert('t_recebimentos_avaria', $dados))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| grava os dados informados na tabela de recebimentos
	 *| Tabela: t_recebimentos_vencido
	 */
	function gravar_recebimento_vencidos_produto($dados)
	{
		if($this->db->insert('t_recebimentos_vencidos', $dados))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: t_movimentacoes_estoque
	 */
	function gravar_movimentacao_estoque($dados)
	{
		$dados['mov_data'] = str_replace(" ", "T", $dados['mov_data']);

		if($this->db->insert('t_movimentacoes_estoque', $dados))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: t_movimentacoes_estoque
	 */
	function mudar_status_nota($volume)
	{

	}

	/*============================================================================================*/
	/**
	 *| Verificar se volume é controlado.
	 *| Tabela: verificar_volume_controlado
	 */
	function verificar_volume_controlado($volume, $filial)
	{
		$sql = "
				select count(*) cont
				from t_volumes
				join t_cabecalho_notas on cbn_id = vol_id_cbn
				join t_item_notas on itn_id_cbn = cbn_id
				join t_produtos on pro_id = itn_id_pro
				join t_filial on fil_id = cbn_id_fil
				where fil_num_loja = ".$filial."
				and vol_cod_barras = '".$volume."'
				and pro_venda_controlada = 'S'";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	/*============================================================================================*/
	/**
	 *| Lista os produtos controlados de uma nota
	 */
	function get_codigos_produtos_controlados_nota($chave, $filial)
	{
		$sql = "
				select t_produtos.*
				from t_cabecalho_notas
				join t_item_notas on itn_id_cbn = cbn_id
				join t_produtos on pro_id = itn_id_pro
				join t_filial on fil_id = cbn_id_fil
				where fil_num_loja = ".$filial."
				and cbn_chave_nota = '".$chave."'
				and pro_venda_controlada = 'S'";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			$arr = [];
			foreach ($query->result() as $item) {
				$arr[] = $item->pro_id;
			}
			return $arr;
		}
		else
		{
			return [];
		}
	}

	/*============================================================================================*/
	/**
	 *| Verificar se volume é controlado.
	 *| Tabela: verificar_nota_controlada
	 */
	function verificar_nota_controlada($chave, $filial)
	{
		$sql = "
				select count(*) cont
				from t_cabecalho_notas
				join t_item_notas on itn_id_cbn = cbn_id
				join t_produtos on pro_id = itn_id_pro
				join t_filial on fil_id = cbn_id_fil
				where fil_num_loja = ".$filial."
				and cbn_chave_nota = '".$chave."'
				and pro_venda_controlada = 'S'";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Busca volumes controlados
	 *| Tabela:validacao_volume_controlados
	 */
	function validacao_volume_controlados($volume)
	{
		$sql = "
				select * from (
						select 
						pro_cod_pro_cli,
						pro_descricao,
						reccon_lote,
						reccon_id_recebimento,
						reccon_data_validade,
						reccon_data_fabricacao,
						itn_lote,
						itn_validade,
						itn_fabricacao,
						CASE WHEN(DATEADD(month, DATEDIFF(month, 0, reccon_data_validade), 0) = DATEADD(month, DATEDIFF(month, 0, itn_validade), 0))
						THEN 'V'
						ELSE 'F'
						END validacao_validade,
						CASE WHEN(DATEADD(month, DATEDIFF(month, 0, reccon_data_fabricacao), 0) = DATEADD(month, DATEDIFF(month, 0, itn_fabricacao), 0))
						THEN 'V'
						ELSE 'F'
						END validacao_fabricacao,
						CASE WHEN(reccon_lote = itn_lote)
						THEN 'V'
						ELSE 'F'
						END validacao_lote,
						vol_cod_barras
					from t_item_notas 
					join t_cabecalho_notas on cbn_id = itn_id_cbn
					left join aux.t_recebimentos on rec_produto = itn_id_pro and rec_cbn_id = cbn_id
					left join aux.t_recebimentos_controlados on reccon_id_recebimento = rec_id
					join t_produtos on pro_id = rec_produto
					join t_volumes on vol_id_cbn = rec_cbn_id
					where vol_cod_barras = '$volume' 
				) r
				where r.validacao_lote = 'F' OR r.validacao_validade = 'F'";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Busca volumes controlados
	 *| Tabela:validacao_nota_controlada
	 */
	function validacao_nota_controlada($chave)
	{
		// $sql = "	
		// 		select * from (
		// 			select 
		// 				pro_cod_pro_cli,
		// 				pro_descricao,
		// 				pro_venda_controlada,
		// 				reccon_lote,
		// 				reccon_id_recebimento,
		// 				reccon_data_validade,
		// 				reccon_data_fabricacao,
		// 				itn_lote,
		// 				itn_validade,
		// 				itn_fabricacao,
		// 				CASE WHEN(reccon_data_validade = DATEADD(month, DATEDIFF(month, 0, itn_validade), 0))
		// 				THEN 'V'
		// 				ELSE 'F'
		// 				END validacao_validade,
		// 				CASE WHEN(reccon_data_fabricacao = DATEADD(month, DATEDIFF(month, 0, itn_fabricacao), 0))
		// 				THEN 'V'
		// 				ELSE 'F'
		// 				END validacao_fabricacao,
		// 				CASE WHEN(reccon_lote = itn_lote)
		// 				THEN 'V'
		// 				ELSE 'F'
		// 				END validacao_lote
		// 			from t_item_notas 
		// 			join t_cabecalho_notas on cbn_id = itn_id_cbn
		// 			left join aux.t_recebimentos on rec_produto = itn_id_pro and rec_cbn_id = cbn_id
		// 			left join aux.t_recebimentos_controlados on reccon_id_recebimento = rec_id
		// 			join t_produtos on pro_id = rec_produto
		// 			where cbn_chave_nota = '".$chave."' 
		// 		) r
		// 		where (r.validacao_fabricacao = 'F' OR r.validacao_lote = 'F' OR r.validacao_validade = 'F') AND r.pro_venda_controlada = 'S'";

		// trocando a query para funcionar o CASE WHEN com datas
		// mantive a anterior comentada acima apenas como referencia
		// da mudança preventivamente
		$sql = "select * from (
			select
			pro_cod_pro_cli,
			pro_descricao,
			pro_venda_controlada,
			reccon_lote,
			reccon_id_recebimento,
			reccon_data_validade,
			reccon_data_fabricacao,
			itn_lote,
			itn_validade,
			itn_fabricacao,
			CASE WHEN(DATEADD(month, DATEDIFF(month, 0, reccon_data_validade), 0) = DATEADD(month, DATEDIFF(month, 0, itn_validade), 0))
			THEN 'V'
			ELSE 'F'
			END validacao_validade,
			CASE WHEN(DATEADD(month, DATEDIFF(month, 0, reccon_data_fabricacao), 0) = DATEADD(month, DATEDIFF(month, 0, itn_fabricacao), 0))
			THEN 'V'
			ELSE 'F'
			END validacao_fabricacao,
			CASE WHEN(reccon_lote = itn_lote)
			THEN 'V'
			ELSE 'F'
			END validacao_lote,
			vol_cod_barras
			from t_item_notas
			join t_cabecalho_notas on cbn_id = itn_id_cbn
			left join aux.t_recebimentos on rec_produto = itn_id_pro and rec_cbn_id = cbn_id
			left join aux.t_recebimentos_controlados on reccon_id_recebimento = rec_id
			join t_produtos on pro_id = rec_produto
			left join t_volumes on vol_id_cbn = rec_cbn_id
			where cbn_chave_nota = '$chave'
			) r
			where (r.validacao_lote = 'F' OR r.validacao_validade = 'F') AND r.pro_venda_controlada = 'S'";
		
		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Atualiza dados de controlados da conferencia
	 *| Tabela: update_conferencia_controlados
	 */
	function update_conferencia_controlados($rec_id, $lote, $validade, $fabricacao)
	{
		$validade_bd	= implode('-', array_reverse(explode('/', $validade)));
		$fabricacao_bd	= implode('-', array_reverse(explode('/', $fabricacao)));

		$sql = "update aux.t_recebimentos_controlados 
				set reccon_lote = '".$lote."', 
				reccon_data_fabricacao = '".$fabricacao_bd."', reccon_data_validade = '".$validade_bd."'
				where reccon_id_recebimento = '".$rec_id."'";
		
		$query = $this->db->query($sql);
		return true;
	}

	/*============================================================================================*/
	/**
	 *| Obtem sobra ou faltas de um volume
	 *| Tabela: obtem_sobra_falta_volume_abertura_chamado
	 */
	function obtem_sobra_falta_volume_abertura_chamado($volume)
	{
		$sql = "
				SELECT	
						cbn_num_nota,
						cbn_chave_nota,
						i.itn_cod_barras,
						p.pro_id as itn_id_pro,
						p.pro_cod_pro_cli,
						p.pro_descricao,
						(i.itn_qtd_ven * COALESCE(f.fat_quantidade, 1)) AS itn_qtd_ven,
						(
							SELECT	ISNULL(SUM(r.rec_quantidade), 0) AS rec_quantidade
							FROM	aux.t_recebimentos r
							WHERE	i.itn_cod_barras = r.rec_volume AND r.rec_produto = i.itn_id_pro
						) AS rec_quantidade
				FROM t_item_notas i
				JOIN t_cabecalho_notas c on cbn_id = itn_id_cbn
				LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
				LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
				WHERE i.itn_cod_barras = '$volume'
				UNION
				select cbn_num_nota,
						cbn_chave_nota,
						rec_volume,
						p.pro_id as itn_id_pro,
						p.pro_cod_pro_cli,
						p.pro_descricao,
						0 as itn_qtd_ven,
						rec_quantidade
				from aux.t_recebimentos
				JOIN t_cabecalho_notas c on cbn_id = rec_cbn_id
				LEFT JOIN t_fator_conversao f ON f.fat_id_pro = rec_produto AND f.fat_cnpj = c.cbn_cnpj_emitente
				LEFT JOIN t_produtos p ON p.pro_id = rec_produto
				where not exists(
					SELECT	
							p.pro_cod_pro_cli
					FROM t_item_notas i
					JOIN t_cabecalho_notas c on cbn_id = itn_id_cbn
					LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
					LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
					WHERE i.itn_cod_barras = rec_volume and rec_produto = p.pro_id
				) and rec_volume = '$volume'
			";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	/*============================================================================================*/
	/**
	 *| Obtem sobra ou faltas de um volume
	 *| Tabela: obtem_sobra_falta_nota_abertura_chamado
	 */
	function obtem_sobra_falta_nota_abertura_chamado($chave)
	{
		$sql = "
			SELECT	
					cbn_num_nota,
					cbn_chave_nota,
					i.itn_cod_barras,
					i.itn_id_pro,
					p.pro_cod_pro_cli,
					p.pro_descricao,
					(SUM(i.itn_qtd_ven) * COALESCE(f.fat_quantidade, 1)) AS itn_qtd_ven,
					(
						SELECT	ISNULL(SUM(r.rec_quantidade), 0) AS rec_quantidade
						FROM	aux.t_recebimentos r
						WHERE	c.cbn_chave_nota = r.rec_chave_nota AND r.rec_produto = i.itn_id_pro
					) AS rec_quantidade
			FROM t_item_notas i
			JOIN t_cabecalho_notas c on cbn_id = itn_id_cbn
			LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
			LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
			WHERE cbn_chave_nota = '". $chave ."' 
			GROUP BY 
				cbn_num_nota,
				cbn_chave_nota,
				i.itn_cod_barras,
				i.itn_id_pro,
				p.pro_cod_pro_cli,
				p.pro_descricao,
				f.fat_quantidade 	
			union
			select cbn_num_nota,
					cbn_chave_nota,
					rec_volume,
					p.pro_id as itn_id_pro,
					p.pro_cod_pro_cli,
					p.pro_descricao,
					0 as itn_qtd_ven,
					rec_quantidade
			from aux.t_recebimentos
			JOIN t_cabecalho_notas c on cbn_id = rec_cbn_id
			LEFT JOIN t_fator_conversao f ON f.fat_id_pro = rec_produto AND f.fat_cnpj = c.cbn_cnpj_emitente
			LEFT JOIN t_produtos p ON p.pro_id = rec_produto
			where not exists(
				SELECT	
					p.pro_cod_pro_cli
				FROM t_item_notas i
				JOIN t_cabecalho_notas c on cbn_id = itn_id_cbn
				LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
				LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
				WHERE c.cbn_chave_nota = rec_chave_nota and rec_produto = p.pro_id
			) and rec_chave_nota = '". $chave ."'";

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	/*============================================================================================*/
	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: t_item_notas, t_produtos
	 */
	function obtem_sobra_falta_nota($chave)
	{
		$sql = "			
			SELECT	i.itn_id_pro,
				p.pro_cod_pro_cli,
				p.pro_descricao,
				(SUM(i.itn_qtd_ven) * COALESCE(f.fat_quantidade, 1)) AS itn_qtd_ven,
				(
					SELECT	ISNULL(SUM(r.rec_quantidade), 0) AS rec_quantidade
					FROM	aux.t_recebimentos r
					WHERE	c.cbn_chave_nota = r.rec_chave_nota AND r.rec_produto = i.itn_id_pro
				) AS rec_quantidade
			FROM t_item_notas i
			JOIN t_cabecalho_notas c ON c.cbn_id = i.itn_id_cbn
			LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
			LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
			WHERE cbn_chave_nota = '". $chave ."' 
			GROUP BY i.itn_id_pro,
				p.pro_cod_pro_cli,
				p.pro_descricao,
				f.fat_quantidade,
				c.cbn_chave_nota
			UNION
			select 
					p.pro_id as itn_id_pro,
					p.pro_cod_pro_cli,
					p.pro_descricao,
					0 as itn_qtd_ven,
					rec_quantidade
			from aux.t_recebimentos
			JOIN t_cabecalho_notas c on cbn_id = rec_cbn_id
			LEFT JOIN t_fator_conversao f ON f.fat_id_pro = rec_produto AND f.fat_cnpj = c.cbn_cnpj_emitente
			LEFT JOIN t_produtos p ON p.pro_id = rec_produto
			where not exists(
				SELECT	
						p.pro_cod_pro_cli
				FROM t_item_notas i
				JOIN t_cabecalho_notas c on cbn_id = itn_id_cbn
				LEFT JOIN t_fator_conversao f ON f.fat_id_pro = i.itn_id_pro AND f.fat_cnpj = c.cbn_cnpj_emitente
				LEFT JOIN t_produtos p ON p.pro_id = i.itn_id_pro
				WHERE c.cbn_chave_nota = rec_chave_nota and rec_produto = p.pro_id
				) and cbn_chave_nota = '". $chave ."'" ;

		$query = $this->db->query($sql);

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	/*============================================================================================*/
	/**
	 *| Busca nota fiscal de conferencia externa
	 *| Tabela: buscar_nota_externa
	 */
	public function buscar_nota_externa($chave, $filial){



		$sql = "select * from t_cabecalho_notas
				join t_filial on fil_id = cbn_id_fil
				left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
				where
					(
						cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
						OR
						( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%' ) > 0
					)
					and tce_chave_nota is null
					and cbn_chave_nota = '$chave'
					and fil_num_loja = $filial
					and cbn_status = 'FATURADA'";
	
		$query = $this->db->query($sql);				
		if($query->num_rows()>0){
			return $query->result();
		}else{		
			return false;		
		}	
			
	}

	/*============================================================================================*/
	/**
	 *| Verifica se a nota fiscal de conferencia externa é de uso e consumo
	 *| Tabela: buscar_nota_externa
	 */
	public function buscar_nota_uso($chave){

		$sql = "select distinct cn.*
		from t_cabecalho_notas cn
				inner join t_item_notas n on cn.cbn_id = n.itn_id_cbn
				inner join t_produtos p on n.itn_id_pro = p.pro_id
				where len(p.pro_cod_pro_cli) > 10 and cn.cbn_chave_nota = '$chave'";

	
		$query = $this->db->query($sql);			
		// echo $this->db->last_query();

		if($query->num_rows()>0){
			return $query->result();
		}else{		
			return false;		
		}	
			
	}


		/*============================================================================================*/
	/**
	 *| Verifica se a nota fiscal de conferencia externa é de uso e consumo
	 *| Tabela: buscar_nota_externa
	 */
	public function valida_nota($chave){

		$sql = "select distinct cn.* from t_cabecalho_notas cn where cbn_status = 'ENTREGUE' AND cn.cbn_chave_nota = '$chave'";
	
		$query = $this->db->query($sql);	

		// echo $this->db->last_query();

		if($query->num_rows() == 0){
			return true;
		}else{		
			return false;		
		}	
			
	}

	

	/*============================================================================================*/
	/**
	 *| Faz a consulta de matching para nota fiscal de conferencia externa
	 *| Tabela: ?
	 */
	public function consultar_matching_nota_externa($chave){

		$sql = "select * from t_matchings where mat_chave_nfe = '${chave}'";
		$query = $this->db->query($sql);

		if ( $query->num_rows() > 0 ) {
			return $query->row();
		} else {
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| Busca por produto termolabil para nota fiscal de conferencia externa
	 *| Tabela: ?
	 */
	public function conta_termolabil_nota_externa($cbn_id){
		$sql = "SELECT COUNT(0) AS 'N' FROM t_item_notas
			JOIN t_produtos ON pro_id = itn_id_pro
			WHERE itn_id_cbn = ${cbn_id}
			AND pro_termolabil = 'S'";
		$query = $this->db->query($sql);

		if ( $query->num_rows() > 0 ) {
			$res = $query->result();
			return $res[0]->N;
		} else {
			return 0;
		}
	}

	/*============================================================================================*/
	/**
	 *| Buscar conferencias pendentes da loja
	 *| Tabela: buscar_conferencias
	 */
	public function buscar_conferencias($filial){	


		$sql = "select
					*,
					DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
					from t_cabecalho_notas
					join t_filial on fil_id = cbn_id_fil
					left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
					LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
					where
						(
							cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
							OR
							( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%' ) > 0
						)
						and tce_chave_nota is null
						and fil_num_loja = $filial
						and cbn_status = 'ENTREGUE'
		 				and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 30";

		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}



	

		// $sql = "select * from t_cabecalho_notas
		// 		join t_filial on fil_id = cbn_id_fil
		// 		left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
		// 		where cbn_cnpj_emitente != '27849963000110' 
		// 			and tce_chave_nota is null
		// 			and cbn_status = 'ENTREGUE'";

		// considerando crossdocking

		// $sql = "select
		// 			*,
		// 			DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
		// 			from t_cabecalho_notas
		// 			join t_filial on fil_id = cbn_id_fil
		// 			left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
		// 			LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
		// 			where
		// 				(
		// 					cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
		// 					OR
		// 					( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%' ) > 0
		// 				)
		// 				and tce_chave_nota is null
		// 				and fil_num_loja = $filial
		// 				and cbn_status = 'ENTREGUE'
		//  				and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 30";

		// $query = $this->db->query($sql);

		// echo $this->db->last_query();
					

		// if($query->num_rows()>0)
		// {
		// 	return $query->result();
		// }
		// else
		// {
		// 	return false;
		// }
	}
		/*============================================================================================*/
	/**
	 *| Verifica conferencia externa de uso e consumo
	 *| Tabela: buscar_nota_externa
	 */
	/*============================================================================================*/
	/**
	 *| Buscar conferencias pendentes da loja
	 *| Tabela: buscar_conferencias
	 */ 
	public function buscar_conferencias_recusadas($filial,$numero_nota='',$dataRecusa=''){	
		
		$sql_parcial = '';

		if(isset($filial) and $filial <> ''){ $sql_parcial .= " and cbn_id_fil = $filial"; }
		if(isset($numero_nota) and $numero_nota <> ''){ $sql_parcial .= " and cbn_num_nota = $numero_nota"; }
		if(isset($dataRecusa) and $dataRecusa <> ''){ $sql_parcial .= " and cbn_status_recusa_data_hora = '$dataRecusa'"; }

		$sql = "select
					*,
					DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
					from t_cabecalho_notas
					join t_filial on fil_id = cbn_id_fil
					left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
					LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
					where
						(
							cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
							OR
							( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%' ) > 0
						)
						and tce_chave_nota is null						
						and cbn_status = 'RECUSADA'
						".$sql_parcial;

		 				

		/* $sql = "select
					*,
					DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
					from t_cabecalho_notas
					join t_filial on fil_id = cbn_id_fil
					left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
					LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
					where
						(
							cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
							OR
							( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%' ) > 0
						)
						and tce_chave_nota is null
						and fil_num_loja = $filial
						and cbn_status = 'RECUSADA'
		 				and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 30"; */

		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}



	

		// $sql = "select * from t_cabecalho_notas
		// 		join t_filial on fil_id = cbn_id_fil
		// 		left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
		// 		where cbn_cnpj_emitente != '27849963000110' 
		// 			and tce_chave_nota is null
		// 			and cbn_status = 'ENTREGUE'";

		// considerando crossdocking

		// $sql = "select
		// 			*,
		// 			DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) AS horas_da_entrega
		// 			from t_cabecalho_notas
		// 			join t_filial on fil_id = cbn_id_fil
		// 			left join aux.t_conferencia_externa on tce_chave_nota = cbn_chave_nota
		// 			LEFT JOIN t_transportes ON tra_id_nota_cab = cbn_id
		// 			where
		// 				(
		// 					cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
		// 					OR
		// 					( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%' ) > 0
		// 				)
		// 				and tce_chave_nota is null
		// 				and fil_num_loja = $filial
		// 				and cbn_status = 'ENTREGUE'
		//  				and DATEDIFF(day, cbn_data_emissao, CURRENT_TIMESTAMP) <= 30";

		// $query = $this->db->query($sql);

		// echo $this->db->last_query();
					

		// if($query->num_rows()>0)
		// {
		// 	return $query->result();
		// }
		// else
		// {
		// 	return false;
		// }
	}
		/*============================================================================================*/
	/**
	 *| Verifica conferencia externa de uso e consumo
	 *| Tabela: buscar_nota_externa
	 */
	public function buscar_conferencias_uso($filial){

		$sql = "select cn.*
		from t_cabecalho_notas cn
				inner join t_item_notas n on cn.cbn_id = n.itn_id_cbn
				inner join t_produtos p on n.itn_id_pro = p.pro_id
		where len(p.pro_cod_pro_cli) = 11 and cn.cbn_id_fil = '$filial'";
	
		$query = $this->db->query($sql);				

		// echo $this->db->last_query();


		if($query->num_rows()>0){

			
			// echo '1';
			return $query->result();

		}else{		
			return false;		
		}	
			
	}

	/*============================================================================================*/
	/**
	 *| 
	 *| Tabela: obtem_conferencias_filial
	 */
	function gravar_conferencia_iniciada($dados)
	{
		if($this->db->insert('aux.t_conferencia_externa', $dados))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 *| Muda o status da nota fiscal de FATARADA para ENTREGUE
	 *| Tabela: alterar_status_nota_externa
	 */
	function alterar_status_nota_externa($chave,$recusa='',$recusa_justificativa='',$status_recusa='',$status_recusa_usuario='',$usuario_recusa_incial='')
	{
		
		if(isset($recusa) && $recusa == 'recusada'){
			
			$this->db->set('cbn_status', 'RECUSADA');
			

			if(isset($recusa_justificativa) && $recusa_justificativa <>''){

				$this->db->set('cbn_justificativa_recusa', $recusa_justificativa);
			}

			if(isset($usuario_recusa_incial) && $usuario_recusa_incial <>''){
				
				$this->db->set('cbn_justificativa_recusa_usuario', $usuario_recusa_incial);
			}

		} else {
			$this->db->set('cbn_status', 'ENTREGUE');
		}
		
		if(isset($status_recusa) && $status_recusa <> ''){
			
			$this->db->set('cbn_status_recusa', $status_recusa);
			$this->db->set('cbn_status_recusa_data_hora', 'GETDATE()', false);
			$this->db->set('cbn_status_recusa_usuario', $status_recusa_usuario);
			
		}

		$this->db->where('cbn_chave_nota', $chave);

		if($this->db->update('t_cabecalho_notas'))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}

	/*============================================================================================*/
	/**
	 *| Obtem as informações da nota fiscal
	 *| Tabela: obtem_dados_nota
	 */
	function obtem_dados_nota($filial, $chave, $somenteEntregue = true)
	{
		$this->db->select('t_cabecalho_notas.cbn_data_emissao, t_cabecalho_notas.cbn_hora_emissao, 
							t_cabecalho_notas.cbn_num_nota, t_cabecalho_notas.cbn_nome_emitente,
							t_cabecalho_notas.cbn_chave_nota, t_cabecalho_notas.cbn_id');
		$this->db->from('t_cabecalho_notas');
		$this->db->JOIN('t_filial', 't_filial.fil_id = t_cabecalho_notas.cbn_id_fil', 'INNER');

		
		if($somenteEntregue) {
			$this->db->where('t_cabecalho_notas.cbn_status', 'ENTREGUE');
		}
		
		//$this->db->where('t_cabecalho_notas.cbn_id_fil', $filial);
		$this->db->where('t_filial.fil_num_loja', $filial);
		$this->db->where('t_cabecalho_notas.cbn_chave_nota', $chave);

		$query = $this->db->get();
				
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}

		/*============================================================================================*/
	/**
	 *| Obtem as informações da nota fiscal
	 *| Tabela: obtem_dados_nota
	 */
	function obtem_dados_nota_uso($filial, $chave)
	{
		$this->db->select('t_cabecalho_notas.cbn_data_emissao, t_cabecalho_notas.cbn_hora_emissao, 
							t_cabecalho_notas.cbn_num_nota, t_cabecalho_notas.cbn_nome_emitente,
							t_cabecalho_notas.cbn_chave_nota, t_cabecalho_notas.cbn_id');
		$this->db->from('t_cabecalho_notas');
		
		//$this->db->where('t_cabecalho_notas.cbn_id_fil', $filial);
		$this->db->where('t_cabecalho_notas.cbn_chave_nota', $chave);

		$query = $this->db->get();

		// echo $this->db->last_query();

		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}

	/*============================================================================================*/
	/**
	 *| Busca a nota na tabela t_cabecalho_notas e verificar se a nota é realmente da loja vinculada ao usuário logado.
	 *| Atualizar o campo cbn_status dessa tabela para ENTREGUE. (t_cabecalho_notas)
	 *| Atualizar o campo vol_conferencia para 'SIM' (t_volumes)
	 *| Tabela: t_cabecalho_notas, t_filial, t_volumes, t_item_notas, t_movimentacoes_estoque
	 */
	function gravar_chave_complementar($chave_nf, $num_loja)
	{
		$this->db->select('t_cabecalho_notas.cbn_id, t_cabecalho_notas.cbn_chave_nota, t_cabecalho_notas.cbn_status, t_cabecalho_notas.cbn_id_fil, t_filial.fil_num_loja');
		$this->db->from('t_cabecalho_notas');
		$this->db->join('t_filial', 't_filial.fil_cpf_cnpj = t_cabecalho_notas.cbn_cnpj_destinatario', 'INNER');
		$this->db->where('t_cabecalho_notas.cbn_chave_nota', $chave_nf);
		$this->db->where('cbn_status', 'FATURADA');

		$query = $this->db->get();
		
		if($query->num_rows()>0)
		{
			$row = $query->row();
			$cbn_id 		= $row->cbn_id;
			$fil_num_loja 	= $row->fil_num_loja;

			if($fil_num_loja != $num_loja)
			{
				return 'Chave da NF informada não está vinculada a sua loja!';
			}
			else
			{
				
				// Atualizar o campo cbn_status dessa tabela para ENTREGUE
				$this->db->set('cbn_status', 'ENTREGUE');
				$this->db->where('cbn_chave_nota', $chave_nf);
				$this->db->update('t_cabecalho_notas');

				// Atualizar o campo vol_conferencia para 'SIM'
				$this->db->set('vol_conferencia', 'SIM');
				$this->db->where('vol_id_cbn', $cbn_id);
				$this->db->update('t_volumes');
				
				// Gravar Movimentação de Estoque
				$this->db->where('itn_id_cbn', $cbn_id);
				$query = $this->db->get('t_item_notas');
				if($query->num_rows()>0)
				{
					foreach ($query->result() as $row)
					{
						$arraymovimento = array(
							'mov_data'                  => date('Y-m-d H:i:s.v', strtotime('NOW')),
							'mov_id_filial'             => $num_loja,
							'mov_id_produto'            => $row->itn_id_pro,
							'mov_qtd_movimentada'       => $row->itn_qtd_ven,
							'mov_estoque_destino'       => 'FECHAMENTO',
							'mov_controle_integracao'   => 'L',
							'mov_chave_nfe'             => $chave_nf,
						);
						
						$this->db->insert('t_movimentacoes_estoque', $arraymovimento);

					}
				}

				return 'OK';
			}
		}
		else
		{
			return 'Chave da NF informada não encontrada!';
		}
	}

	/*============================================================================================*/
	/**
	 *| Retorna NFs de recebimentos com prazo de conferencia expirado
	 */
	function verificar_conferencias_expiradas($prazo = 24)
	{
		$sql = "
			-- INTERNO E CROSS
			SELECT
				cbn_id,
				cbn_id_fil,
				cbn_num_nota,
				cbn_data_emissao,
				cbn_hora_emissao,
				cbn_cnpj_emitente,
				cbn_nome_emitente,
				tra_entrega
			FROM
				t_cabecalho_notas
			LEFT JOIN
				t_transportes ON tra_id_nota_cab = cbn_id
			WHERE
				cbn_status = 'ENTREGUE'
				AND tra_entrega is not NULL
				AND ( SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND ( vol_conferencia = 'S' OR vol_controlado = 'S' ) ) > 0
				AND DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) > ${prazo}
				
			UNION

			-- EXTERNO
			SELECT
				cbn_id,
				cbn_id_fil,
				cbn_num_nota,
				cbn_data_emissao,
				cbn_hora_emissao,
				cbn_cnpj_emitente,
				cbn_nome_emitente,
				tra_entrega
			FROM
				t_cabecalho_notas
			LEFT JOIN
				aux.t_conferencia_externa ON tce_chave_nota = cbn_chave_nota
			LEFT JOIN
				t_transportes ON tra_id_nota_cab = cbn_id
			WHERE
				cbn_status = 'ENTREGUE'
				AND tra_entrega is not NULL
				AND tce_chave_nota IS NULL
				AND (
					cbn_cnpj_emitente not in('27849963000110', '71605265006283', '71605265010205')
					OR (SELECT COUNT(0) FROM t_volumes WHERE vol_id_cbn = cbn_id AND vol_cod_barras LIKE '%CROSS%') > 0
				)
				AND ( SELECT COUNT(0) FROM t_item_notas JOIN t_produtos ON pro_id = itn_id_pro WHERE itn_id_cbn = cbn_id AND pro_venda_controlada = 'S' ) > 0
				AND DATEDIFF(hour, tra_entrega, CURRENT_TIMESTAMP ) > ${prazo}
			;
		";

		$query = $this->db->query($sql);
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	function gravar_recebimento_nota_em_transportes($cbn_id, $usr_id) {

		$dt = date("Y-m-d H:m:i.v");

		$this->db->set('tra_id_usu', $usr_id);
		$this->db->set('tra_id_usu_rec', $usr_id);
		$this->db->set('tra_id_nota_cab', $cbn_id);
		$this->db->set('tra_id_vei', 0);
		$this->db->set('tra_entrega', str_replace(" ", "T", $dt));



		if ($this->db->insert('t_transportes')) {			
			
			return true;
		} else {

			return false;
		}
	}

	function gravar_recebimento_nota_em_transportes_uso($cbn_id, $usr_id) {

		$dt = date("Y-m-d H:m:i.v");

		$this->db->set('tra_id_usu', $usr_id);
		$this->db->set('tra_id_usu_rec', $usr_id);
		$this->db->set('tra_id_nota_cab', $cbn_id);
		$this->db->set('tra_id_vei', 0);
		$this->db->set('tra_entrega', str_replace(" ", "T", $dt));


		if ($this->db->insert('t_transportes')) {			
			return true;
		} else {
			return false;
		}
	}
	

	function passou_da_validade_permitida($itn_id, $filial)
	{
		$sql = "
			SELECT
				itn_validade,
				DATEDIFF(DAY, CURRENT_TIMESTAMP, itn_validade ) AS dias_ate_vencimento,
				( SELECT val_dias FROM t_validade WHERE val_fil_id = ${filial} ) AS limite_dias_loja
			FROM
				t_item_notas
			WHERE
				itn_id = ${itn_id};
		";
		$query = $this->db->query($sql);
		if ( $query->num_rows() > 0 ) {
			$row = $query->row();
			if (!empty($row->dias_ate_vencimento) && !empty($row->limite_dias_loja)) {
				if ($row->dias_ate_vencimento > $row->limite_dias_loja) {
					return false;
				} else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Retorna as horas de conferência obrigatória, configurados no db
	 */
	function parametro_conferencia_obrigatoria() {
		$this->db->where('conf_obrigatoria_id', 1);
		$query = $this->db->get('t_conferencia_obrigatoria');
		if($query->num_rows() > 0){
			$row = $query->row();
			return $row->conf_obrigatoria_valor;
		}
		return 24;
	}

	/**
	 * Retorna se a loja está em inventário
	 */
	function inventario($filialId) {
		// $sql = "SELECT COUNT(0) AS 'N' FROM t_calendario_inventario WHERE cal_id_filial = ".$filialId." AND cal_status = 1 AND CURRENT_TIMESTAMP >= cal_data_hora AND CURRENT_TIMESTAMP <= DATETIMEFROMPARTS(YEAR(cal_data_hora), MONTH(cal_data_hora), DAY(cal_data_hora), 23, 59, 59, 0)";
		$sql = "
			SELECT
				COUNT(0) AS 'N'
			FROM
				t_calendario_inventario
				LEFT JOIN t_par_inventario ON parinv_loja = cal_id_filial
			WHERE
				cal_id_filial = ".$filialId."
				AND cal_status = 1
				AND CURRENT_TIMESTAMP >= DATEADD (hour, COALESCE(DATEPART (HOUR, parinv_hora_limite), 0), DATEADD (minute, COALESCE(DATEPART (MINUTE, parinv_hora_limite), 0), cal_data_hora))
				AND CURRENT_TIMESTAMP <= DATETIMEFROMPARTS (YEAR(cal_data_hora), MONTH(cal_data_hora), DAY(cal_data_hora), 23, 59, 59, 0)
		";
		$query = $this->db->query($sql);

		//echo $this->db->last_query();

		if ( $query->num_rows() > 0 ) {
			$row = $query->row();
			if ($row->N != 0) {
				return true;
			}
		}
		return false;
	}
	function inventarioAmanha($filialId) {
		$sql = "SELECT COUNT(0) AS 'N' FROM t_calendario_inventario WHERE cal_id_filial = ".$filialId." AND cal_status = 1 AND DATEADD(day, 1, CURRENT_TIMESTAMP) >= cal_data_hora AND DATEADD(day, 1, CURRENT_TIMESTAMP) <= DATETIMEFROMPARTS(YEAR(cal_data_hora), MONTH(cal_data_hora), DAY(cal_data_hora), 23, 59, 59, 0)";
		$query = $this->db->query($sql);
		if ( $query->num_rows() > 0 ) {
			$row = $query->row();
			if ($row->N != 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Transfere movimento de estoque da tabela auxiliar
	 * para a tabela de produção para uma filial informada
	 */
	function movimentaEstoqueAuxiliar($filialId) {
		
		$sql = "SELECT * FROM aux.t_movimentacoes_estoque WHERE mov_id_filial = ".$filialId;
		$query = $this->db->query($sql);
		
		if ( $query->num_rows() > 0 ) {
			$rows = $query->result();
			foreach ($rows as $row) {

				$this->db->set('mov_data', $row->mov_data); // Manter a data original ou aplicar data atual? Aqui está mantendo a original
				$this->db->set('mov_id_filial', $row->mov_id_filial);
				$this->db->set('mov_id_produto', $row->mov_id_produto);
				$this->db->set('mov_qtd_movimentada', $row->mov_qtd_movimentada);
				$this->db->set('mov_estoque_destino', $row->mov_estoque_destino);
				$this->db->set('mov_controle_integracao', $row->mov_controle_integracao);
				$this->db->set('mov_chave_nfe', $row->mov_chave_nfe);
				$this->db->set('mov_estoque_origem', $row->mov_estoque_origem);
		
				if ($this->db->insert('t_movimentacoes_estoque')) {
					$sql = "DELETE FROM aux.t_movimentacoes_estoque WHERE mov_id = ".$row->mov_id;
					$this->db->query($sql);
				}

			}
		}

	}


	public function obtem_justificativas(){	

		$sql = "select * from t_justificativas";
		$query = $this->db->query($sql);

		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}

	}	

	public function obtem_nota_recusada($num_nota,$id_filial){	
		
		$sql = "select * from t_cabecalho_notas where cbn_id_fil = $id_filial and cbn_num_nota = $num_nota";
				
		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	public function verifica_nota_recusada($chave){	
		
		$sql = "select * from t_cabecalho_notas where cbn_chave_nota = '$chave' and cbn_status = 'RECUSADA'";
				
		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	public function verifica_nota_sem_xml($chave){	
		
		$sql = "select * from t_cabecalho_notas where cbn_chave_nota = '$chave' and cbn_status = 'XML Não encontrado na base de dados'";
				
		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	public function verifica_nota_finalizada($chave){	
		
		$sql = "select * from t_cabecalho_notas where cbn_chave_nota = '$chave' and cbn_status = 'FINALIZADA'";
				
		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	public function verifica_nota_cancelada($chave){	
		
		$sql = "select * from t_cabecalho_notas where cbn_chave_nota = '$chave' and cbn_status = 'CANCELADA'";
				
		$query = $this->db->query($sql);


		if($query->num_rows()>  0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	}

	
}


?>

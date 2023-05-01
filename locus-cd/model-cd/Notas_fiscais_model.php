<?php
class Notas_fiscais_model extends CI_Model{

	function __construct(){
		parent::__construct();
	}		
	
	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: t_cabecalho_notas
	 */			
	function obtem_todas_nf()
	{			
		$query = $this->db->query('SELECT TOP(2000) * FROM t_cabecalho_notas ');
		
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */		
	function notas_auditoria()
	{	
		$sql = "select * from vw_buscar_notas_auditoria WHERE  status = 'PENDENTE AUDITORIA'  ";

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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */	
	function obtem_nf_filtro($data_incial,$data_final,$status_nf,$num_nota)
	{
		if($status_nf != "")
		{
			$status = "AND status = '". $status_nf ."' ";
		}
		else
		{
			$status = "";
		}

		if($num_nota != ""){
			$nota = "AND nota_fiscal = '" . $num_nota . "' ";
		}
		else{
			$nota = "";
		}
		
		$sql = "select * from vw_buscar_notas_fiscais
		WHERE data_emissao BETWEEN '" .$data_incial. "' AND '". $data_final. "' ". $status ." " . $nota . " ";
		$query = $this->db->query($sql);

		// echo $this->db->last_query();
		
		if($query->num_rows()>0){
			return $query->result();
		}
		else
		{
			return false;
		}

		//echo $sql;

		return false;
	}		

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: t_cabecalho_notas, t_historico , t_veiculos, t_usuarios, t_transportes
	 */		
	function obtem_dados_transporte($tipo_filtro,$valor_filtro)
	{			
		switch ($tipo_filtro) 
		{
			case 'V':
				$sql_filtro = "and v.vei_placa = '". $valor_filtro ."' ";
				break;
			case 'T':
				$sql_filtro = "and u.usu_nome like  '%". $valor_filtro ."%' ";
				break;
			case 'N':
				$sql_filtro = "and c.cbn_num_nota = '". $valor_filtro ."' ";
				break;
			default:
				$sql_filtro = "";
				break;
		}
					
		$query = "SELECT	
					h.his_id_vei,
					MAX(H.his_geracao) AS his_geracao,	
					MAX(h.his_latitude) AS his_latitude, 
					MAX(h.his_longitude) as his_longitude,
					v.vei_placa, 
					u.usu_nome,
					c.cbn_status,
					F.fil_num_loja,
					F.fil_latitude,
					F.fil_longitude
			FROM t_filial F
				JOIN t_cabecalho_notas C	ON C.cbn_id_fil = F.fil_id
				JOIN t_transportes T		ON T.tra_id_nota_cab = C.cbn_id
				JOIN t_usuarios U			ON U.usu_id = T.tra_id_usu
				JOIN t_veiculos V			ON V.vei_id = T.tra_id_vei
				JOIN t_historico H			ON H.his_id_vei = V.vei_id
			WHERE c.cbn_status = 'EM TRANSITO' ".$sql_filtro."
			GROUP BY	F.fil_num_loja,
						F.fil_latitude,
						F.fil_longitude,
						c.cbn_status,
						h.his_id_vei,
						v.vei_placa, 
						u.usu_nome";

		$query = $this->db->query($query);
		
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
	 *| DESCRIÇÃO
	 *| Tabela: t_cabecalho_notas, t_historico , t_veiculos, t_usuarios, t_transportes
	 */
	function obtem_dados_transporte_grid($tipo_filtro,$valor_filtro)
	{			
		switch ($tipo_filtro) 
		{
			case 'V':
				$sql_filtro = "placa = '". $valor_filtro ."' AND";
				break;
			case 'T':
				$sql_filtro = "usuario like  '%". $valor_filtro ."%' AND ";
				break;
			case 'N':
				$sql_filtro = "cbn_num_nota = '". $valor_filtro ."' AND";
				break;
			default:
				$sql_filtro = "";
				break;
		}
		$sql = "SELECT * from vw_buscar_transportes WHERE ".$sql_filtro." status = 'EM TRANSITO' ";
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
	 *| DESCRIÇÃO
	 *| Tabela: t_cabecalho_notas, t_historico , t_veiculos, t_usuarios, t_transportes
	 */
	function verificar_notas($filial, $usuario)
	{
		$this->db->join('t_transportes','t_transportes.tra_id_nota_cab = t_cabecalho_notas.cbn_id','INNER');
		$this->db->where('t_cabecalho_notas.cbn_id_fil', $filial);
		$this->db->where('t_cabecalho_notas.cbn_status', 'EM TRANSITO');
		$this->db->order_by('t_cabecalho_notas.cbn_id_fil');
		$this->db->where('t_transportes.tra_id_usu', $usuario);
		$query = $this->db->get('t_cabecalho_notas');
		
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			return 0;
		}
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_produtos_nota($id_nota, $volume)
	{	
		$sql = "
		select sum(qtd) as qtd, pro_descricao, pro_codigo, pro_id, qtd_conferida, fil_id, fil_num_loja , cbn_id, num_nota, itn_cod_barras   from VW_CONSULTA_PRODUTOS_NOTA
				where cbn_id = '".$id_nota."' and itn_cod_barras = '".$volume."'
		group by pro_descricao, pro_codigo, pro_id, qtd_conferida, fil_id, fil_num_loja, cbn_id, num_nota, itn_cod_barras ";
	
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

	function consulta_produtos_nota_id($id_nota)
	{	
		$sql = "
		select sum(qtd) as qtd, pro_descricao, pro_codigo, pro_id, qtd_conferida, fil_id, fil_num_loja , cbn_id, num_nota, itn_cod_barras   from VW_CONSULTA_PRODUTOS_NOTA
				where cbn_id = '".$id_nota."'
		group by pro_descricao, pro_codigo, pro_id, qtd_conferida, fil_id, fil_num_loja, cbn_id, num_nota, itn_cod_barras ";
	
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function auditada($id_nota)
	{
		$data = array('cbn_status'=>'AUDITADA');
		$this->db->where('t_cabecalho_notas.cbn_id', $id_nota);
		$this->db->update('t_cabecalho_notas',$data);
	}

	function validar_nota_volume($id_nota, $volume)
	{
		// VERIFICA SE EXISTE ALGUM ITEM DO VOLUME COM O STATUS 'AUDITADA'

		$sql = "SELECT COUNT(*) as qtd
			FROM t_historico_auditoria
			WHERE hia_id_cbn = " . $id_nota . "
				AND hia_status LIKE 'AUDITADA'
				AND hia_cod_barras = '" . $volume . "'
		";

		$query = $this->db->query($sql);

		// SE EXISTIR VALIDA SE EXISTE ALGUM QUE ESTEJA DIVERGENTE

		if($query->result()[0]->qtd != 0){

			$sql = "SELECT COUNT(*) AS qtd
				FROM t_historico_auditoria
				WHERE hia_id_cbn = " . $id_nota . "
					AND hia_status NOT LIKE 'AUDITADA'
					AND hia_cod_barras = '" . $volume . "'
			";

			$query = $this->db->query($sql);

			// echo $this->db->last_query();

			// SE TODOS OS ITENS DO VOLUME ESTAO AUDITADOS VALIDA A NOTA

			if($query->result()[0]->qtd == 0){
				return '100%';
			}
			else{
				// VALIDA SE EXISTE ALGUMA AUDITORIA DO VOLUME EM ANDAMENTO
				$sql = "SELECT COUNT(*) as qtd
				FROM t_auditoria_andamento
				WHERE ada_id_cbn = " . $id_nota . "
					AND ada_cod_barras = '" . $volume . "'
				";

				$query = $this->db->query($sql);

				// SE NAO EXISTIR VALIDA A NOTA

				if($query->result()[0]->qtd == 0){
					return '0%';
				}
				else{
					return '50%';
				}
			}
		}
		// SE NAO VALIDA SE EXISTE ALGUMA AUDITORIA DO VOLUME EM ANDAMENTO
		else{
			$sql = "SELECT COUNT(*) as qtd
			FROM t_auditoria_andamento
			WHERE ada_id_cbn = " . $id_nota . "
				AND ada_cod_barras = '" . $volume . "'
			";

			$query = $this->db->query($sql);

			// SE NAO EXISTIR VALIDA A NOTA

			if($query->result()[0]->qtd == 0){
				return '0%';
			}
			else{
				return '50%';
			}
		}
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_produtos_ean($id_nota, $volume)
	{	
		$sql = "
		select *  from VW_produtos_ean
		where cbn_id = '".$id_nota."' and itn_cod_barras = '".$volume."'
			";
	
		$query = $this->db->query($sql);
		
		//$this->db->where('VW_produtos_ean.fil_id', $fil_id);
		//$this->db->where_in('VW_produtos_ean.num_nota', $listanotas);
		//$this->db->where('VW_produtos_ean.cbn_status', 'PENDENTE AUDITORIA');
		//$query = $this->db->get('VW_produtos_ean');
		//$query = $this->db->query($sql);
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_produtos_ean_nota($id_nota)
	{	
		$sql = "select *  from VW_produtos_ean where cbn_id = '".$id_nota."' ";
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function alterar_status($id_nota, $status_nf)
	{
		$data = array('cbn_status'=>$status_nf);
		$this->db->where('t_cabecalho_notas.cbn_id',$id_nota);
		$this->db->update('t_cabecalho_notas',$data); 
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */

	function consulta_dados_nota($id_nota)
	{
		$this->db->where('vw_buscar_notas_fiscais.id_nota',$id_nota);
		$query = $this->db->get('vw_buscar_notas_fiscais');

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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_volumes_loja($fil_id)
	{
		$sql = "
		select cbn.cbn_num_nota, cbn.cbn_status, f.fil_id, f.fil_num_loja, cbn.cbn_data_emissao, count(v.vol_cod_barras) as qtd_volume, 0 as qtd from t_volumes v
				inner join t_cabecalho_notas cbn on v.vol_id_cbn = cbn.cbn_id
				inner join t_filial f on cbn.cbn_id_fil = f.fil_id
				where f.fil_id = '".$fil_id."'  and cbn.cbn_data_emissao = convert(date,getdate())  OR ( cbn.cbn_status = 'PENDENTE AUDITORIA' AND f.fil_id = '".$fil_id."')
				group by cbn.cbn_num_nota, cbn.cbn_status, f.fil_id, f.fil_num_loja, cbn.cbn_data_emissao
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */

	function consulta_volumes_nota($cbn_id)
	{
		$query = "
		select v.vol_id, v.vol_cod_barras, cbn.cbn_num_nota, cbn.cbn_id, cbn.cbn_status, f.fil_id, f.fil_num_loja, cbn.cbn_data_emissao, 0 as qtd from t_volumes v
				inner join t_cabecalho_notas cbn on v.vol_id_cbn = cbn.cbn_id
				inner join t_filial f on cbn.cbn_id_fil = f.fil_id
				where cbn.cbn_id = '".$cbn_id."' 
				";
		$query = $this->db->query($query);

		// echo $this->db->last_query()
		// echo '<br /> <br />';
		
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_notas_auditoria($fil_id)
	{
		$query = "	SELECT * 
					FROM t_cabecalho_notas		CBN
						INNER JOIN t_filial		F on CBN.cbn_id_fil = F.fil_id
					WHERE F.fil_id = '".$fil_id."' and (CBN.cbn_data_emissao = CONVERT(DATE, GETDATE()) or CBN.cbn_status = 'PENDENTE AUDITORIA')
				";	

		$query = $this->db->query($query);	
		
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_notas_por_ip($ipaddress)
	{
		$vetor				    = explode(".", $ipaddress);

		if (trim($vetor[0]) == 10) {
			$ip1                 	= trim($vetor[0]);													
		
			$ip2					= trim($vetor[1]);	
			$ip3					= trim($vetor[2]);
			$ip4					= trim($vetor[3]);
		}
		else {
				$ip2					= 0;
				$ip3					= 0;
				$ip4					= 0;
		}

		//$ip3 = 105;

		$query = "	SELECT * 
					FROM t_cabecalho_notas		CBN
						INNER JOIN t_filial		F on CBN.cbn_id_fil = F.fil_id
					WHERE F.fil_num_loja = '".$ip3."' and  CBN.cbn_status = 'EM TRANSITO'
				";
	
		$query = $this->db->query($query);
		
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function gravar_historico($historico, $historico2)
	{
		
		$this->db->insert('t_historico_divergencia', $historico2);	

		$hia_id_cbn = $historico['hia_id_cbn'];
		$hia_id_pro = $historico['hia_id_pro'];
		$hia_cod_barras = $historico['hia_cod_barras'];

		$this->db->where('hia_id_cbn', $hia_id_cbn);
		$this->db->where('hia_id_pro', $hia_id_pro);
		$this->db->where('hia_cod_barras', $hia_cod_barras);
		
		$query = $this->db->get('t_historico_auditoria');

		if($query->num_rows()==0)
		{
			$this->db->insert('t_historico_auditoria', $historico);						
		}
		else
		{
			$this->db->where('hia_id_cbn', $hia_id_cbn);
			$this->db->where('hia_id_pro', $hia_id_pro);
			$this->db->where('hia_cod_barras', $hia_cod_barras);
			$this->db->update('t_historico_auditoria', $historico);
		}
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
			
	function inserir_auditoria_andamento($andamento)
	{
		$this->db->insert('t_auditoria_andamento', $andamento);
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
			
	function limpar_auditoria_andamento($pro_id, $id_nota)
	{
		$query = "DELETE FROM t_auditoria_andamento WHERE ada_id_pro = " . $pro_id . " AND ada_id_cbn =" . $id_nota;
		$this->db->query($query);
	}

	//Função SENE
	function limpar_auditoria_andamento_um_a_um($pro_id, $id_nota, $qt)
	{
		
		for ($i = 1; $i <= $qt; $i++) {	    

		    $query = "DELETE TOP(1) FROM t_auditoria_andamento WHERE ada_id_pro = " . $pro_id . " AND ada_id_cbn =" . $id_nota;
			$this->db->query($query);
		}
		
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_produtos_banco_auditoria($cbn_id, $volume)
	{
		$query = "SELECT	SUM(R.qtd)as qtd, 
						R.pro_descricao, 
						R.pro_codigo, 
						R.pro_id, 
						R.itn_cod_barras, 
						SUM(R.qtd_conferida) as qtd_conferida, 
						R.fil_id, 
						R.fil_num_loja, 
						R.cbn_id, 
						R.num_nota, 
						SUM(R.qtd) - SUM(R.qtd_conferida) as diferenca 
				FROM (
					SELECT		SUM(qtd)	AS qtd, 
								pro_descricao, 
								pro_codigo, 
								pro_id, 
								itn_cod_barras, 
								qtd_conferida, 
								fil_id, 
								fil_num_loja , 
								cbn_id, 
								num_nota  
					FROM VW_CONSULTA_PRODUTOS_NOTA
					WHERE		cbn_id = '".$cbn_id."'
								AND 
								itn_cod_barras = '".$volume."'
							
					GROUP BY	pro_descricao, 
								pro_codigo, 
								pro_id, 
								itn_cod_barras, 
								qtd_conferida, 
								fil_id,
								fil_num_loja, 
								cbn_id, 
								num_nota

					UNION
					
					SELECT		0 as qtd,
								P.pro_descricao,   
								P.pro_cod_pro_cli	AS pro_codigo, 
								P.pro_id, 
								A.ada_cod_barras as itn_cod_barras,  
								(	SELECT COUNT(A2.ada_id_pro) 
									FROM t_auditoria_andamento A2
											INNER JOIN t_cabecalho_notas	C2 ON A2.ada_id_cbn =	C2.cbn_id
									
									WHERE C2.cbn_id = C.cbn_id
												AND
											A2.ada_cod_barras =  A.ada_cod_barras
													AND
											A2.ada_id_pro = P.pro_id ) AS qtd_conferida,
								F.fil_id, 
								F.fil_num_loja, 
								C.cbn_id, 
								C.cbn_num_nota		AS num_nota 
					FROM		t_auditoria_andamento A 
						INNER JOIN t_produtos			P ON A.ada_id_pro = P.pro_id
						INNER JOIN t_filial				F ON A.ada_id_fil = F.fil_id
						INNER JOIN t_cabecalho_notas	C ON A.ada_id_cbn = C.cbn_id
					
					WHERE		C.cbn_id = '".$cbn_id."'
								AND
								A.ada_cod_barras = '".$volume."'
		
					GROUP BY	P.pro_descricao,
								P.pro_id, 
								P.pro_cod_pro_cli, 
								F.fil_id, 
								F.fil_num_loja, 
								C.cbn_id, 
								C.cbn_num_nota, 
								A.ada_cod_barras
					) as R
				GROUP BY	R.pro_descricao, 
							R.pro_codigo, 
							R.pro_id, 
							R.fil_id, 
							R.fil_num_loja, 
							R.cbn_id, 
							R.num_nota, 
							R.itn_cod_barras";
				
		$query = $this->db->query($query);

		// echo '<br /> PRODUTOS <br /> <br />';
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_produtos_banco_auditoria_nota($cbn_id)
	{
		$query = "SELECT	SUM(R.qtd)as qtd, 
						R.pro_descricao, 
						R.pro_codigo, 
						R.pro_id, 
						R.itn_cod_barras, 
						SUM(R.qtd_conferida) as qtd_conferida, 
						R.fil_id, 
						R.fil_num_loja, 
						R.cbn_id, 
						R.num_nota, 
						SUM(R.qtd) - SUM(R.qtd_conferida) as diferenca 
				FROM (
					SELECT		SUM(qtd)	AS qtd, 
								pro_descricao, 
								pro_codigo, 
								pro_id, 
								itn_cod_barras, 
								qtd_conferida, 
								fil_id, 
								fil_num_loja , 
								cbn_id, 
								num_nota  
					FROM VW_CONSULTA_PRODUTOS_NOTA
					WHERE		cbn_id = '".$cbn_id."'
							
					GROUP BY	pro_descricao, 
								pro_codigo, 
								pro_id, 
								itn_cod_barras, 
								qtd_conferida, 
								fil_id,
								fil_num_loja, 
								cbn_id, 
								num_nota

					UNION
					
					SELECT		0 as qtd,
								P.pro_descricao,   
								P.pro_cod_pro_cli	AS pro_codigo, 
								P.pro_id, 
								A.ada_cod_barras as itn_cod_barras,  
								(	SELECT COUNT(A2.ada_id_pro) 
									FROM t_auditoria_andamento A2
											INNER JOIN t_cabecalho_notas	C2 ON A2.ada_id_cbn =	C2.cbn_id
									
									WHERE C2.cbn_id = C.cbn_id
												AND
											A2.ada_cod_barras =  A.ada_cod_barras
													AND
											A2.ada_id_pro = P.pro_id ) AS qtd_conferida,
								F.fil_id, 
								F.fil_num_loja, 
								C.cbn_id, 
								C.cbn_num_nota		AS num_nota 
					FROM		t_auditoria_andamento A 
						INNER JOIN t_produtos			P ON A.ada_id_pro = P.pro_id
						INNER JOIN t_filial				F ON A.ada_id_fil = F.fil_id
						INNER JOIN t_cabecalho_notas	C ON A.ada_id_cbn = C.cbn_id
					
					WHERE		C.cbn_id = '".$cbn_id."'
		
					GROUP BY	P.pro_descricao,
								P.pro_id, 
								P.pro_cod_pro_cli, 
								F.fil_id, 
								F.fil_num_loja, 
								C.cbn_id, 
								C.cbn_num_nota, 
								A.ada_cod_barras
					) as R
				GROUP BY	R.pro_descricao, 
							R.pro_codigo, 
							R.pro_id, 
							R.fil_id, 
							R.fil_num_loja, 
							R.cbn_id, 
							R.num_nota, 
							R.itn_cod_barras";
				
		$query = $this->db->query($query);

		// echo '<br /> PRODUTOS <br /> <br />';
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function consulta_falta_sobra_auditoria($cbn_id, $volume)
	{
		$query = "SELECT	SUM(R.qtd) - SUM(R.qtd_conferida) as diferenca 
		FROM 
		(
			SELECT	SUM(qtd)	AS qtd, 
					pro_descricao, 
					pro_codigo, 
					pro_id, 
					itn_cod_barras, 
					qtd_conferida, 
					fil_id, 
					fil_num_loja , 
					cbn_id, 
					num_nota  
			FROM VW_CONSULTA_PRODUTOS_NOTA
			WHERE		cbn_id = '".$cbn_id."'
						AND 
						itn_cod_barras = '".$volume."'
					
			GROUP BY	pro_descricao, 
						pro_codigo, 
						pro_id, 
						itn_cod_barras, 
						qtd_conferida, 
						fil_id,
						fil_num_loja, 
						cbn_id, 
						num_nota

			UNION
				SELECT	0 as qtd,
						P.pro_descricao,   
						P.pro_cod_pro_cli	AS pro_codigo, 
						P.pro_id, 
						I.itn_cod_barras,  
						(	SELECT COUNT(A2.ada_id_pro) 
							FROM t_auditoria_andamento A2
									INNER JOIN t_cabecalho_notas	C2 ON A2.ada_id_cbn =	C2.cbn_id
									INNER JOIN t_item_notas			I2 ON A2.ada_id_pro =	I2.itn_id_pro and C2.cbn_id = I2.itn_id_cbn
							WHERE C2.cbn_id = C.cbn_id
										AND
									I2.itn_cod_barras = I.itn_cod_barras
											AND
									I2.itn_id_pro = P.pro_id ) AS qtd_conferida,
						F.fil_id, 
						F.fil_num_loja, 
						C.cbn_id, 
						C.cbn_num_nota		AS num_nota 
			FROM	t_auditoria_andamento A 
					INNER JOIN t_produtos			P ON A.ada_id_pro = P.pro_id
					INNER JOIN t_filial				F ON A.ada_id_fil = F.fil_id
					INNER JOIN t_cabecalho_notas	C ON A.ada_id_cbn = C.cbn_id
					INNER JOIN t_item_notas			I ON C.cbn_id =		I.itn_id_cbn and P.pro_id = I.itn_id_pro
			
			WHERE		C.cbn_id = '".$cbn_id."'
						AND
						I.itn_cod_barras = '".$volume."'

			GROUP BY	P.pro_descricao,
						P.pro_id, 
						P.pro_cod_pro_cli, 
						F.fil_id, 
						F.fil_num_loja, 
						C.cbn_id, 
						C.cbn_num_nota, 
						I.itn_cod_barras
		) as R";
				
		$query = $this->db->query($query);
		$row = $query->row();
		
		if (isset($row))
		{
			return $row->diferenca;
		}
		else
		{
			return false;
		}
	}

	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function apagar_auditoria_andamento($cbn_id)
	{
		$this->db->where('ada_id_cbn', $cbn_id);	
		$this->db->delete('t_auditoria_andamento');
	}
				
	/*============================================================================================*/
	/**
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function relatorio_divergencia_auditoria($data_incial, $data_final,$status_nf)
	{
		if($status_nf == "DIVERGENTE" || $status_nf == "AUDITADA")
		{
			if($status_nf == "DIVERGENTE"){
				$filtroStatus = '
					AND
					( 
						(SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X WHERE hid_divergencia < 0 AND X.hid_data_inicio = H.hid_data_inicio) <> 0
						OR 
						(SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X WHERE hid_divergencia >= 0 AND X.hid_data_inicio = H.hid_data_inicio) <> 0
					)
				';
			}
			else{
				$filtroStatus = '
					AND (SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X WHERE hid_divergencia < 0 AND X.hid_data_inicio = H.hid_data_inicio) = 0
					AND (SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X WHERE hid_divergencia >= 0 AND X.hid_data_inicio = H.hid_data_inicio) = 0
				';
			}

			$query = "
			SELECT CBN.cbn_num_nota, 
					CBN.cbn_chave_nota, 
					CBN.cbn_qtd_item,
					F.fil_num_loja, 
					(SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X where hid_divergencia < 0 and X.hid_data_inicio = H.hid_data_inicio) as qtd_sobras, 
					(SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X where hid_divergencia >= 0 and X.hid_data_inicio = H.hid_data_inicio) as qtd_faltas,
					coalesce(H.hid_data_inicio,0) as data_inicio, 
					MAX(H.hid_data_fim) AS data_fim,
					U.usu_nome,
					hid_cod_barras,
					cpp_descricao,
					(SELECT SUM(ITN.itn_qtd_ven) FROM t_item_notas ITN WHERE ITN.itn_id_cbn = CBN.cbn_id) AS itn_qtd_ven,
					(SELECT SUM(ITN.itn_vlr_unitario) FROM t_item_notas ITN WHERE ITN.itn_id_cbn = CBN.cbn_id) AS itn_vlr_unitario
			FROM t_historico_divergencia H
			LEFT JOIN t_produtos P							ON P.pro_id = H.hid_id_pro
			LEFT JOIN t_classificacoes_produtos_procfit C	ON C.cpp_id = P.pro_cla_proc
			LEFT JOIN t_cabecalho_notas CBN				ON H.hid_id_cbn = CBN.cbn_id
			LEFT JOIN t_usuarios U							ON H.hid_id_usu	= U.usu_id
			LEFT JOIN t_filial F							ON CBN.cbn_id_fil = f.fil_id
			WHERE convert(date,H.hid_data_fim) BETWEEN '".$data_incial."' AND '".$data_final . "'" . $filtroStatus . "
			GROUP BY	CBN.cbn_num_nota, 
						CBN.cbn_chave_nota, 
						CBN.cbn_qtd_item,					
						F.fil_num_loja, 
						H.hid_data_inicio,
						U.usu_nome ,
						hid_cod_barras,
						cpp_descricao,
						CBN.cbn_id


			";

		}
		else
		{
			$query = "
			SELECT CBN.cbn_num_nota, 
					CBN.cbn_chave_nota, 
					CBN.cbn_qtd_item,
					F.fil_num_loja, 
					(SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X where hid_divergencia < 0 and X.hid_data_inicio = H.hid_data_inicio) as qtd_sobras, 
					(SELECT coalesce(SUM(hid_divergencia), 0) FROM t_historico_divergencia X where hid_divergencia >= 0 and X.hid_data_inicio = H.hid_data_inicio) as qtd_faltas,
					coalesce(H.hid_data_inicio,0) as data_inicio, 
					MAX(H.hid_data_fim) AS data_fim,
					U.usu_nome,
					hid_cod_barras,
					cpp_descricao,
					(SELECT SUM(ITN.itn_qtd_ven) FROM t_item_notas ITN WHERE ITN.itn_id_cbn = CBN.cbn_id) AS itn_qtd_ven,
					(SELECT SUM(ITN.itn_vlr_unitario) FROM t_item_notas ITN WHERE ITN.itn_id_cbn = CBN.cbn_id) AS itn_vlr_unitario
			FROM t_historico_divergencia H
			LEFT JOIN t_produtos P							ON P.pro_id = H.hid_id_pro
			LEFT JOIN t_classificacoes_produtos_procfit C	ON C.cpp_id = P.pro_cla_proc
			LEFT JOIN t_cabecalho_notas CBN				ON H.hid_id_cbn = CBN.cbn_id
			LEFT JOIN t_usuarios U							ON H.hid_id_usu	= U.usu_id
			LEFT JOIN t_filial F							ON CBN.cbn_id_fil = f.fil_id
			WHERE convert(date,H.hid_data_fim) BETWEEN '".$data_incial."' AND '".$data_final."'
			GROUP BY	CBN.cbn_num_nota, 
						CBN.cbn_chave_nota, 
						CBN.cbn_qtd_item,					
						F.fil_num_loja, 
						H.hid_data_inicio,
						U.usu_nome ,
						hid_cod_barras,
						cpp_descricao,
						CBN.cbn_id
			";
		}

		$query = $this->db->query($query);

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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function auditoria_lojas()
	{
		$query =    "SELECT  
			f.fil_num_loja as loja,
			f.fil_id, 
			count(VOL.vol_cod_barras) AS volumes
			FROM t_cabecalho_notas C 
			INNER JOIN t_filial F ON F.fil_id = C.cbn_id_fil 
			INNER JOIN t_volumes VOL ON VOL.vol_id_cbn = C.cbn_id 
		WHERE 
			C.cbn_data_emissao = convert(date,getdate()) OR C.cbn_status = 'PENDENTE AUDITORIA'
		GROUP BY 
			F.fil_num_loja, 
			f.fil_id
		ORDER BY
			F.fil_num_loja";

		$query = $this->db->query($query);

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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function lista_sobras($id_nota) 
	{
		$query =    "SELECT	c.cbn_num_nota,
							p.pro_cod_pro_cli,
							p.pro_descricao,
							h.hia_divergencia,
							h.hia_cod_barras
					FROM t_historico_auditoria h
					inner join t_cabecalho_notas c on h.hia_id_cbn = c.cbn_id
					inner join t_produtos p on h.hia_id_pro = p.pro_id
					WHERE hia_divergencia <0 AND h.hia_status = 'DIVERGENTE' AND hia_id_cbn =  '".$id_nota."'

					group by	c.cbn_num_nota,
								p.pro_cod_pro_cli,
								p.pro_descricao,
								h.hia_divergencia,
								h.hia_cod_barras";

		$query = $this->db->query($query);
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function lista_faltas($id_nota) 
	{
		$query =    "SELECT	c.cbn_num_nota,
							p.pro_cod_pro_cli,
							p.pro_descricao,
							h.hia_divergencia,
							h.hia_cod_barras
					FROM t_historico_auditoria h
					left join t_cabecalho_notas c on h.hia_id_cbn = c.cbn_id
					left join t_produtos p on h.hia_id_pro = p.pro_id
					WHERE h.hia_divergencia >0 AND h.hia_status = 'DIVERGENTE' AND h.hia_id_cbn =  '".$id_nota."'
					group by	c.cbn_num_nota,
								p.pro_cod_pro_cli,
								p.pro_descricao,
								h.hia_divergencia,
								h.hia_cod_barras";

		$query = $this->db->query($query);
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function relatorio_auditoria_grafico($data_incial, $data_final, $filial)
	{
		if($filial != "")
		{
			$filial2 = "AND F2.fil_id = '".$filial."' ";
			$filial  = "AND F.fil_id = '".$filial."' ";
		}
		else
		{
			$filial = "";
			$filial2 = "";
		}

		$query = "SELECT convert(date,r.data_fim) as data ,count(r.cbn_num_nota) as auditada, 
			(select count(i.cbn_num_nota) as total from (SELECT CBN2.cbn_num_nota, 
											CBN2.cbn_chave_nota, 
											CBN2.cbn_qtd_item,
											F2.fil_id,
											F2.fil_num_loja, 
											H2.hid_status, 
											sum(coalesce(H2.hid_divergencia,0)) as qtd_divergencia, 
											coalesce(H2.hid_data_inicio,0) as data_inicio, 
											MAX(H2.hid_data_fim) AS data_fim,
											U2.usu_nome 
									FROM t_historico_divergencia H2
										INNER JOIN t_cabecalho_notas	CBN2 on H2.hid_id_cbn = CBN2.cbn_id
										INNER JOIN t_usuarios			U2   on H2.hid_id_usu = U2.usu_id
										INNER JOIN t_filial             F2   on CBN2.cbn_id_fil = f2.fil_id
								WHERE convert(date,H2.hid_data_fim)  = convert(date,r.data_fim) ". $filial2 ." 
										GROUP BY  CBN2.cbn_num_nota, 
										
										CBN2.cbn_chave_nota, 
										CBN2.cbn_qtd_item,
										F2.fil_id,
										F2.fil_num_loja, 
									
										H2.hid_status, 
										coalesce(H2.hid_data_inicio,0),
										U2.usu_nome ) as i) as total from (
								SELECT CBN.cbn_num_nota, 
											CBN.cbn_chave_nota, 
											CBN.cbn_qtd_item,
											F.fil_id,
											F.fil_num_loja, 
											H.hid_status, 
											sum(coalesce(H.hid_divergencia,0)) as qtd_divergencia, 
											coalesce(H.hid_data_inicio,0) as data_inicio, 
											MAX(H.hid_data_fim) AS data_fim,
											U.usu_nome 
									FROM t_historico_divergencia H
										INNER JOIN t_cabecalho_notas	CBN on H.hid_id_cbn = CBN.cbn_id
										INNER JOIN t_usuarios			U   on H.hid_id_usu = U.usu_id
										INNER JOIN t_filial             F   on CBN.cbn_id_fil = f.fil_id
								WHERE convert(date,H.hid_data_fim) BETWEEN '" .$data_incial. "' AND '". $data_final. "' AND h.hid_status = 'AUDITADA' ". $filial ." 
										GROUP BY  CBN.cbn_num_nota, 
										
										CBN.cbn_chave_nota, 
										CBN.cbn_qtd_item,
										F.fil_id,
										F.fil_num_loja, 
										H.hid_status, 
										coalesce(H.hid_data_inicio,0),
										U.usu_nome 
										) as r
	
					group by convert(date,r.data_fim)
			";

		$query = $this->db->query($query);
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function relatorio_auditoria_dados($data_incial, $data_final,$filial)
	{
		if($filial != "")
		{
			$filial2 = "AND F2.fil_id = '".$filial."' ";
			$filial = "AND F.fil_id = '".$filial."' ";
		}
		else
		{
			$filial = "";
			$filial2 = "";
		}

		$query = "SELECT coalesce(sum(a.auditada),0) as auditada, coalesce(sum(a.total),0) as total from (
					SELECT convert(date,r.data_fim) as data ,count(r.cbn_num_nota) as auditada, 
						(select count(i.cbn_num_nota) as total from (SELECT CBN2.cbn_num_nota, 
									CBN2.cbn_chave_nota, 
									CBN2.cbn_qtd_item,
									F2.fil_id,
									F2.fil_num_loja, 
									H2.hid_status, 
									sum(coalesce(H2.hid_divergencia,0)) as qtd_divergencia, 
									coalesce(H2.hid_data_inicio,0) as data_inicio, 
									MAX(H2.hid_data_fim) AS data_fim,
									U2.usu_nome 
							FROM t_historico_divergencia H2
								INNER JOIN t_cabecalho_notas	CBN2 on H2.hid_id_cbn = CBN2.cbn_id
								INNER JOIN t_usuarios			U2   on H2.hid_id_usu = U2.usu_id
								INNER JOIN t_filial             F2   on CBN2.cbn_id_fil = f2.fil_id
						WHERE convert(date,H2.hid_data_fim)  = convert(date,r.data_fim) ". $filial2 ." 
								GROUP BY  CBN2.cbn_num_nota, 
													
										CBN2.cbn_chave_nota, 
										CBN2.cbn_qtd_item,
										F2.fil_id,
										F2.fil_num_loja, 
									
										H2.hid_status, 
										coalesce(H2.hid_data_inicio,0),
										U2.usu_nome ) as i) as total from (
						SELECT CBN.cbn_num_nota, 
														CBN.cbn_chave_nota, 
														CBN.cbn_qtd_item,
														F.fil_id,
														F.fil_num_loja, 
														H.hid_status, 
														sum(coalesce(H.hid_divergencia,0)) as qtd_divergencia, 
														coalesce(H.hid_data_inicio,0) as data_inicio, 
														MAX(H.hid_data_fim) AS data_fim,
														U.usu_nome 
												FROM t_historico_divergencia H
													INNER JOIN t_cabecalho_notas	CBN on H.hid_id_cbn = CBN.cbn_id
													INNER JOIN t_usuarios			U   on H.hid_id_usu = U.usu_id
													INNER JOIN t_filial             F   on CBN.cbn_id_fil = f.fil_id
											WHERE convert(date,H.hid_data_fim) BETWEEN '" .$data_incial. "' AND '". $data_final. "' AND h.hid_status = 'AUDITADA' ". $filial ." 
													GROUP BY  CBN.cbn_num_nota, 
													
													CBN.cbn_chave_nota, 
													CBN.cbn_qtd_item,
													F.fil_id,
													F.fil_num_loja, 
													H.hid_status, 
													coalesce(H.hid_data_inicio,0),
													U.usu_nome 
													) as r
				
					group by convert(date,r.data_fim)
					) as a
			";
			
		$query = $this->db->query($query);
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function verificar_nota_existe($chave_nota, $num_filial) 
	{
		$sql = "
		SELECT  cbn_id, cbn_id_fil, cbn_data_emissao, cbn_hora_emissao, cbn_qtd_item,cbn_num_nota, cbn_chave_nota, cbn_status, cbn_cnpj_destinatario, 
				fil_id, fil_num_loja, tra_id_usu, tra_id_vei, tra_id_nota_cab, tra_id_rota, tra_carregamento, tra_chegada, 
				fil_latitude, fil_longitude
		FROM 	t_cabecalho_notas c
		INNER JOIN t_transportes t on t.tra_id_nota_cab = c.cbn_id
		INNER JOIN t_filial f on f.fil_id = c.cbn_id_fil
		WHERE c.cbn_status = 'EM TRANSITO' 
		  AND cbn_chave_nota = '" . $chave_nota . "'
		  AND f.fil_num_loja = " . $num_filial . "
		GROUP BY 	cbn_id, cbn_id_fil, cbn_data_emissao, cbn_hora_emissao, cbn_qtd_item,
					cbn_num_nota, cbn_chave_nota, cbn_status, cbn_cnpj_destinatario, 
					fil_id, fil_num_loja, tra_id_usu, tra_id_vei, tra_id_nota_cab, tra_id_rota, tra_carregamento,
					tra_chegada, fil_latitude, fil_longitude;
			";
	
		$query = $this->db->query($sql);
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function buscar_dados_chave($chave_nota, $num_filial) 
	{
		$sql = "
		SELECT  cbn_id, cbn_id_fil, cbn_data_emissao, cbn_hora_emissao, cbn_qtd_item,cbn_num_nota, cbn_chave_nota, cbn_status, cbn_cnpj_destinatario,
				fil_id, fil_num_loja, tra_id_usu, tra_id_vei, tra_id_nota_cab, tra_id_rota, tra_carregamento, tra_chegada, 
				fil_latitude, fil_longitude
		FROM 	t_cabecalho_notas c
		INNER JOIN t_transportes t on t.tra_id_nota_cab = c.cbn_id
		INNER JOIN t_filial f on f.fil_id = c.cbn_id_fil
		WHERE c.cbn_status = 'EM TRANSITO' 
		  AND cbn_chave_nota = '" . $chave_nota . "'
		  AND f.fil_num_loja = " . $num_filial . "
		GROUP BY 	cbn_id, cbn_id_fil, cbn_data_emissao, cbn_hora_emissao, cbn_qtd_item,
					cbn_num_nota, cbn_chave_nota, cbn_status, cbn_cnpj_destinatario, 
					fil_id, fil_num_loja, tra_id_usu, tra_id_vei, tra_id_nota_cab, tra_id_rota, tra_carregamento,
					tra_chegada, fil_latitude, fil_longitude;
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
	 *| DESCRIÇÃO
	 *| Tabela: 
	 */
	function finalizar_entrega_chave($tra_entrega, $tra_id_usu_rec, $tra_ip_local, $tra_id_usu, $tra_id_nota_cab) 
	{
		$this->db->set('tra_entrega', $tra_entrega);
		$this->db->set('tra_id_usu_rec', $tra_id_usu_rec);
		$this->db->set('tra_ip_local', $tra_ip_local);
		//$this->db->where('tra_id_usu_mot', $tra_id_usu_mot);
		$this->db->where('tra_id_nota_cab', $tra_id_nota_cab);
		$this->db->update('t_transportes');	

		$this->db->set('cbn_status', 'ENTREGUE');
		$this->db->where('cbn_id', $tra_id_nota_cab);
		$this->db->update('t_cabecalho_notas');	

		
	}

	
}

?>

<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<ol class="breadcrumb bc-3">
	<li>
		<a href="<?php echo base_url(); ?>dashboard"><i class="fas fa-home"></i>Home</a>
	</li>
	<li class="active">
		<strong>Conferência de Mercadoria Externa</strong>
	</li>
</ol>

<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Start Page Content -->
<!-- ============================================================== -->
<div class="row titulo-pagina">
	<div class="col-md-8">
		<h2>
			<h2>Conferência de Mercadoria Externa</h2>
		</h2>
	</div>
</div>

<div class="row"> 
	<div class="col-md-12"> 
		<form role="form" id="form1" method="post" action="<?php echo base_url();?>conferencia_externa/buscar_nota" class="validate">
			<div class="panel panel-default panel-custom-form" data-collapsed="0"> 
				<div class="panel-heading">
					<div class="panel-title"><span class="text-danger"></span></div>
				</div>
				<div class="panel-body"> 
					<div class="row"> 
						<div class="col-md-6 form-group">
							<label class="control-label">Chave da Nota</label>
							<input type="text" class="form-control form-uppercase" name="form-chave" id="form-chave" data-validate="required" data-message-required="Este campo é obrigatório." />
                            <?php echo form_error('form-chave');?>
							<input type="hidden" id="recusa" name="recusa">
							<input type="hidden" id="recusa_justificativa" name="recusa_justificativa">
						</div>
					</div>
				</div>
				<div class="panel-footer"> 
					<button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Buscar</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel-body panel-datatable">
			<table id="table-export-filter" class="table table-bordered table-striped datatable" style="width: 100%;">
				<thead>
					<tr>
						<th>Data</th>
						<th>Nota Fiscal</th>
						<th>Origem</th>
						<th>Ação</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (isset($grid_dados) && $grid_dados) {
						foreach ($grid_dados as $dados) :
							$data_emissao	= date('d/m/Y', strtotime($dados->cbn_data_emissao));
							$hora_emissao	= $dados->cbn_hora_emissao;
							$numero_nota	= $dados->cbn_num_nota;
							$emitente		= $dados->cbn_nome_emitente;
							$pro_controlado = '';


							// Link Conferência Controlados



							if ($inventario) {
								$link_conferencia = '<div data-toggle="tooltip" data-placement="left" title="Loja em Inventário"><div class="btn btn-info btn-sm btn-icon icon-left disabled"><i class="fas fa-box-open"></i>Conferir</div></div>';
							// } elseif ($dados->horas_da_entrega > $limiteConferencia) {
							// 	$link_conferencia = '<div data-toggle="tooltip" data-placement="left" title="Prazo para conferência expirado"><div class="btn btn-info btn-sm btn-icon icon-left disabled"><i class="fas fa-box-open"></i>Conferir</div></div>';
							} else {
								$link_conferencia = '<a href="' . base_url() . 'conferencia_recebimento_externo/conferir/' . $dados->cbn_chave_nota . '" class="btn btn-info btn-sm btn-icon icon-left"><i class="fas fa-box-open"></i>Conferir</a>';
							}


					?>
							<tr>
								<td><?php echo $data_emissao; ?></td>
								<td><?php echo $numero_nota; ?></td>
								<td><?php echo $emitente; ?></td>
								<td>
									<?php echo $link_conferencia; ?>
								</td>
							</tr>

					<?php
						endforeach;
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th>Data</th>
						<th>Nota Fiscal</th>
						<th>Origem</th>
						<th>Ação</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<script>

	var justificativas = <?php echo json_encode($justificativas); ?>
				
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});

	function confirmReiniciar() {

		var formChave = document.getElementById("form-chave").value;
		if(formChave === '') return;

		swal({
			title: "Você deseja aceitar essa nota fiscal?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: 'btn-success',
			confirmButtonText: 'Aceitar',
			cancelButtonText: "Recusar",
			cancelButtonClass: "btn-danger",
			closeOnConfirm: false,
			closeOnCancel: false
		}, function(isConfirm) {
			
			if (isConfirm) {

				document.getElementById("form1").submit(); //envia o formulário
				swal.close(); //fecha o popup

			} else {
			// Cria um selectbox com as opções de justificativas
			
			// Exibe o selectbox no popup
			swal({

				title: "Justificativa",
				text: "Por favor, selecione uma justificativa:",
				type: "input",
				confirmButtonClass: 'btn-success',
				confirmButtonText: 'Recusar',
				showCancelButton: true,
				cancelButtonText: "Cancelar",
				closeOnConfirm: false,
				customClass: 'classe-unica2',
				inputPlaceholder: "Selecione uma justificativa"
				}, function (isConfirm) {
				
					if (isConfirm === false) {
						console.log('cancelar');
					} else {
						
						swal({
						title: 'Deseja realmente realizar esta ação?',
						showCancelButton: true,
						confirmButtonClass: 'btn-success',
						confirmButtonText: 'Sim, recusar',
						cancelButtonText: 'Não, aceitar'
						},function (isConfirm) {
						if (isConfirm === false) {
							
							document.getElementById("form1").submit(); //envia o formulário

						} else {
							var justificativaSelecionada = selectList.value;
							
							document.getElementById("recusa").value = 'recusada';
							document.getElementById("recusa_justificativa").value = justificativaSelecionada;
							
							document.getElementById("form1").submit(); //envia o formulário
						}
						});
						
					}
								
				});

				var input_select = document.getElementsByClassName('classe-unica2');
				var insuck = input_select[0].childNodes[7];
				var inp = input_select[0].childNodes[9];
				inp.style.display = "none";
				var selectList = document.createElement("select");
				selectList.id = "mySelect";
				
				var justificativasText = justificativas.map(function(j) { return j.justificativa; });
				for (var i = 0; i < justificativasText.length; i++) {
				var option = document.createElement("option");
				option.value = justificativasText[i];
				option.text = justificativasText[i];
				selectList.appendChild(option);
				}
				var div = document.createElement("div");
				console.log(input_select);
				div.appendChild(selectList);
				insuck.appendChild(div);		
			}
		});
	}



	const form = document.getElementById('form1');
	
	form.addEventListener("submit", (e) => {
		e.preventDefault();

		confirmReiniciar();
		return;
		// handle submit
	});
</script>
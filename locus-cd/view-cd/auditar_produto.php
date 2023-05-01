<body onload="javascript:form1.ean_prod.focus()">
            <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <ol class="breadcrumb bc-3" >
					<li>
						<a href="<?php echo base_url();?>/dashboard"><i class="fa-home"></i>Home</a>

					</li>
					
					<li class="active">

						<strong> Auditoria </strong>
					</li>
				</ol>	
				
				<h2>Resumo de Itens</h2>
				<br />					
				
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <!-- Row -->
				<div class="col-md-12">
			
			</div>	
				
			
				
	<div class="col-md-12" >

        <form  role="form" id="form1" name="form1" method="post" action="<?php echo base_url();?>auditar_produto/adicionar" class="validate" autocomplete="off">
    
			<div class="form-group">
        
				<label class="control-label">Código EAN do Produto</label>
                <input type="text" class="form-control" name="ean_prod" style="border: 1px solid #DDD"  />

                
			</div>
        </form>

			<div  style="max-height: 350px; overflow-y:scroll;margin-bottom:2em">
				<table class="table table-bordered " style="border: 1px solid #DDD;">
					<thead>
						<tr>
							
							<th style="border: 1px solid #DDD">Código Prod</th>
							<th style="border: 1px solid #DDD">Descricao</th>
                            <th style="border: 1px solid #DDD">Conferido</th>
							<th style="border: 1px solid #DDD">Status</th>
							<th style="border: 1px solid #DDD; display: none" name="diferenca">Diferença</th>
							<th style="border: 1px solid #DDD"></th>
						                                    
						</tr>
					</thead>                                        
					<tbody>
					<?php

			            if($dados_nfs != false){

							$nota = 0;
							$dif = false;
			
							foreach($dados_nfs as $dados){

								
								//$nota_fiscal				= $dados->num_nota;
								$fil_id                 = $dados->fil_id;
								$cbn_id                 =$dados->cbn_id;
								$volume                 =$dados->itn_cod_barras;
								$num_nota               =$dados->num_nota;
                                $codigo				    = $dados->pro_codigo;
                                $pro_descricao			= $dados->pro_descricao;
                                $quantidade 			= $dados->qtd;		
								$diferenca				= $dados->diferenca;										
                                //$id_nota                    = $dados->cbn_id;	
								$nota 					= $dados->num_nota;

								if($diferenca != 0){

									//ISAQUE SENE
									if($diferenca != 0){
										$status = '<td style="border: 1px solid #DDD; color: red">DIVERGENTE------</td>';
										$color = 'red';
										$dif = true;
									}
									else{
										$status = '<td style="border: 1px solid #DDD; color: green">AUDITADO</td>';
										$color = 'slategray';
										
									}

									if($dados->qtd_conferida == 0){
										$display = 'none';
									}
									else{
										$display = 'table-row';
									}
							
									?>
									<tr style="border: 1px solid #DDD;color:<?php echo $color ?>;display: <?php echo $display ?>" name="diferenca">
	                        
		                            <td style="border: 1px solid #DDD"><?php echo $codigo; ?></td>
		                            <td style="border: 1px solid #DDD"><?php echo $pro_descricao; ?></td>
		                            <td style="border: 1px solid #DDD"><?php echo $dados->qtd_conferida; ?></td>
									<?php echo $status; ?>
									
									<td style="border: 1px solid #DDD; display: none" name="diferenca">
									<?php
										if($dados->diferenca < 0){
											echo "+" . ($dados->diferenca * -1);
										}
										elseif($dados->diferenca > 0){
											echo "-" . $dados->diferenca;
										}
										else{
											echo $dados->diferenca;
										}
									?>
									</td>
								
									<td style="border: 1px solid #DDD;text-align: center">
									<?php

									if($dados->diferenca != 0){
									?>
										<input type="hidden" name="qt_conferida_<?=$codigo?>" id="qt_conferida_<?=$codigo?>" value="<?=$dados->qtd_conferida?>">
										<input type="number" style="padding:4px; margin-bottom: 5px; margin: 0 auto; color: #000; width: 50px" name="qt_<?=$codigo?>" id="qt_<?=$codigo?>">
										<button class="btn btn-info" onclick="javascript:zerarContagem('<?php echo $codigo;?>')">-</button>

										<!-- <a style="display:none;" href="javascript:zerarContagem('<?php echo $codigo;?>')" class="btn btn-info ok"> OK </a> -->
									<?php
									}

									?>
									</td>
								    
									</tr> 	
									<?php
								}								  					
							}

							foreach($dados_nfs as $dados){
								
								
								//$nota_fiscal				= $dados->num_nota;
								$fil_id                 = $dados->fil_id;
								$cbn_id                 =$dados->cbn_id;
								$volume                 =$dados->itn_cod_barras;
								$num_nota               =$dados->num_nota;
                                $codigo				    = $dados->pro_codigo;
                                $pro_descricao			= $dados->pro_descricao;
                                $quantidade 			= $dados->qtd;		
								$diferenca				= $dados->diferenca;										
                                //$id_nota                    = $dados->cbn_id;	
								$nota 					= $dados->num_nota;

								if($diferenca == 0){

									//ISAQUE SENE
									if($diferenca != 0){
										$status = '<td style="border: 1px solid #DDD; color: red">DIVERGENTE------</td>';
										$color = 'red';
										$dif = true;
									}
									else{
										$status = '<td style="border: 1px solid #DDD; color: green">AUDITADO</td>';
										$color = 'slategray';
										
									}

									if($dados->qtd_conferida == 0){
										$display = 'none';
									}
									else{
										$display = 'table-row';
									}
							
									?>
									<tr style="border: 1px solid #DDD;color:<?php echo $color ?>;display: <?php echo $display ?>" name="diferenca">
	                        
		                            <td style="border: 1px solid #DDD"><?php echo $codigo; ?></td>
		                            <td style="border: 1px solid #DDD"><?php echo $pro_descricao; ?></td>
		                            <td style="border: 1px solid #DDD"><?php echo $dados->qtd_conferida; ?></td>
									<?php echo $status; ?>
									
									<td style="border: 1px solid #DDD; display: none" name="diferenca">
									<?php
										if($dados->diferenca < 0){
											echo "+" . ($dados->diferenca * -1);
										}
										elseif($dados->diferenca > 0){
											echo "-" . $dados->diferenca;
										}
										else{
											echo $dados->diferenca;
										}
									?>
									</td>
								
									<td style="border: 1px solid #DDD;text-align: center">
									<?php

									if($dados->diferenca != 0){
									?>
										<input type="hidden" name="qt_conferida_<?=$codigo?>" id="qt_conferida_<?=$codigo?>"" value="<?=$dados->qtd_conferida?>">
										<input type="number" style="padding:4px; margin-bottom: 5px; margin: 0 auto; color: #000; width: 50px" name="qt_<?=$codigo?>" id="qt_<?=$codigo?>">
										<button class="btn btn-info" onclick="javascript:zerarContagem('<?php echo $codigo;?>')">-</button>

										<!-- <a style="display:none;" href="javascript:zerarContagem('<?php echo $codigo;?>')" class="btn btn-info ok"> OK </a> -->
									<?php
									}

									?>
									</td>
								    
									</tr> 	
									<?php
								}								  					
							}
						}
						
						
					
					?>
					</tbody>
                </table>	
			</div>
                
                <?php

					if($dados_nfs != false){

				?>
                
				<div class="example pull-right">
					<!-- <a href="<?php echo base_url();?>auditar_produto/salvar" class="btn btn-info">Salvar	</a> -->
					<a href="javascript:salvarConferencia()" class="btn btn-info"> Salvar Auditoria </a>
					<a href="javascript:salvarauditoria('<?php echo $dif;?>')" class="btn btn-success"> Finalizar Auditoria de Volume </a>						
				</div>

				<?php
					}
				?>
                
    </div>

	<script type="text/javascript">
		function salvarauditoria(query){
			var dif = document.getElementsByClassName('diferenca');

			foreach(var el in dif){
				el.style.display = 'block';
			}

			// var link = "<?php echo base_url();?>auditar_produto/salvar" + query;

			// window.open(link, "_self"); 
	
		}
	</script>
</body>
<script type="text/javascript">

			var win = null;
			function NovaJanela(pagina,nome,w,h,scroll){
				LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
				TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
				settings = 'height='+h+',width='+w+',top='+TopPosition+',left='+LeftPosition+',scrollbars='+scroll+',resizable'
				win = window.open(pagina,nome,settings);
			}

			window.name = "main";

			function salvarauditoria(query){
				if (confirm ("Tem certeza que deseja salvar a auditoria desse volume ?")){
					var dif = document.getElementsByName('diferenca');

					for(var i = 0; i < dif.length; i++){
						if(dif[i].localName != 'tr'){
							dif[i].style.display = 'table-cell';
						}
						else{
							dif[i].style.display = 'table-row';
						}
						
					}

					if(query == 1){
						alert('Conferência inválida. Existem produtos divergentes.');
					}
					else{
						alert('Conferência validada com sucesso.');

						window.location.href = '<?php echo base_url();?>auditar_produto/salvar';
					}
				}
			}

			function salvarConferencia(){
				
				var volume = window.location.pathname.split("/").pop();
				window.location.href = '<?php echo base_url();?>auditar_produto/salvar/' + volume;
			}

			function zerarContagem(codigo){

				var qt = document.getElementById("qt_"+codigo).value;
				var qt_conferida = document.getElementById("qt_conferida_"+codigo).value;

				if(qt <= 0 || qt == '') {
					alert('Digite um numero maior que zero!')
					return
				}

				if(qt > qt_conferida) {
					alert('Não foi possivel, o valor escolhido é superior ao valor Conferido!')
					return
				}

				
				if(confirm("Tem certeza que deseja subtriar a contagem?")){
					window.location.href = '<?php echo base_url();?>auditar_produto/zerarContagem/' + codigo +'_'+ qt;
				}
			}

			/*
			function showOK(click){
				var showok = click.nextElementSibling;
				showok.style.display = "";
				var botao = click;
				var bb = botao.parentNode;				
				var tr = bb.parentElement;
				var total = tr.childNodes[5].textContent;
				var resto = total - 1;
				tr.childNodes[5].textContent = resto;
				console.log(click);
			}
			*/

</script>
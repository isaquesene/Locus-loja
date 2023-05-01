<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<ol class="breadcrumb bc-3">
	<li>
		<a href="<?php echo base_url(); ?>dashboard"><i class="fas fa-home"></i>Home</a>
	</li>
	<li class="active">
		<strong>Parametrização de Tempo</strong>
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
			<h2>Parametrização de Tempo</h2>
		</h2>
	</div>
</div>

<div class="row"> 
	<div class="col-md-12"> 
		<form role="form" id="form1" method="post" action="<?php echo base_url();?>parametrizar_tempo/salvar" class="validate">
			<div class="panel panel-default panel-custom-form" data-collapsed="0"> 
				<div class="panel-heading">
					<div class="panel-title"><span class="text-danger"></span></div>
				</div>
				<div class="panel-body"> 
					<div class="row"> 
						
						<div class="col-md-12 form-group">
                            <label class="control-label" style="font-size: 1.1em">Selecione o tempo máximo de entrega:</label>
                            <div class="form-group">
                                <select id="tempo" name="tempo" class="form-control" >
                                    <option value="1">1 dia</option>
                                    <option value="2">2 dias</option>
                                    <option value="3">3 dias</option>
                                    <option value="4">4 dias</option>
                                    <option value="5">5 dias</option>
                                    <option value="6">6 dias</option>
                                    <option value="7">7 dias</option>
                                    <option value="8">8 dias</option>
                                    <option value="9">9 dias</option>
                                    <option value="10">10 dias</option>
                                    <option value="11">11 dias</option>
                                    <option value="12">12 dias</option>
                                    <option value="13">13 dias</option>
                                    <option value="14">14 dias</option>
                                    <option value="15">15 dias</option>
                                </select>
								<?php echo form_error('tempo');?>
                            </div>
                        </div>
						
					</div>
				</div>
				<div class="panel-footer"> 
					<button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Salvar</button>
				</div>
			</div>
		</form>
		
	</div>
</div>


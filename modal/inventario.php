<style>
    .modal-header.custom-header {
        width: calc(100% - 45px);
        /* 45px é a largura do botão Fechar */
    }

    .modal-header .close {
        float: left;
        margin-right: 10px;
        /* Ajuste a margem conforme necessário */
    }
</style>

<form action="<?php echo base_url(); ?>Inventario/delete" method="post">
    <div class="row new-page-title">
        <div class="col-md-8">
            <h1>INVENTÁRIO</h1>
        </div>
    </div>

    <div class="row new-page-subtitle">
        <div class="col-md-8">
            <h1>AGENDAMENTOS</h1>
        </div>
    </div>

    <div class="row">
        <div class="new-page-commentary" style="display: flex;">
            <div class="col-md-9 col-sm-7">
                <h1>GERENCIAMENTO DE REGIONAIS E CRIAÇÃO DE NOVA REGIONAL</h1>
            </div>
            <div class="col-md-3 col-sm-5" style="display: flex;">
                <button type="button" onclick="window.location='<?php echo base_url(); ?>Inventario_add'" class="btn btn-success" style="margin-bottom: 6px;margin-left: auto;">
                    NOVO INVENTÁRIO
                    <i class="fas fa-plus" style="margin-left: 4px;"></i>
                </button>
            </div>
        </div>
    </div>

<?php

if (!$agendamentos) {
?>
    <div class="panel panel-default panel-custom-form">
        <div class="panel-body" id="panel-body" style="display: flex;justify-content: center;align-items: center;">
            <div class="row" style="text-align: center">
                <div>
                    <label>VOCÊ NÃO TEM AGENDAMENTOS</label>
                </div>
                <div>
                    <button type="button" class="btn btn-success" style="margin-bottom: 6px;" onclick="window.location='<?php echo base_url(); ?>inventario_add'">
                        NOVO INVENTÁRIO
                        <i class="fas fa-plus" style="margin-left: 4px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
?>
<div class="row new-page-title3">
    <div class="col-md-8">
        <h1>INVENTÁRIOS DISPONÍVEIS PARA EDIÇÃO</h1>
    </div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default" data-collapsed="0">
			<div class="panel-body panel-datatable ">
				<table class="table table-bordered table-striped datatable" id="table-export">
					<thead>
						<tr class="">
							<th scope="col" class="bold text-center">INVENTÁRIO</th>
							<th scope="col" class="bold text-center">DATA DE INÍCIO</th>
							<th scope="col" class="bold text-center">LOJA</th>
							<th scope="col" class="bold text-center">DIAS DA SEMANA</th>
							<th scope="col" class="bold text-center">REPETIÇÃO</th>
							<th scope="col" class="bold text-center">LIMITE DE TEMPO</th>
							<th scope="col" class="bold text-center">AÇÕES</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ($agendamentos as $agendamento) :
						?>
                        <?php
                                if ($agendamento->agen_data_ini <= date("Y-m-d")) {
                                    $arrayDias = explode(',', $agendamento->agen_dias);
                                    $diasSemana = "";

                                    foreach ($arrayDias as $dia) :
                                        switch ($dia) {
                                            case "Monday":
                                                $diasSemana .= "SEG, ";
                                                break;
                                            case "Tuesday":
                                                $diasSemana .= "TER, ";
                                                break;
                                            case "Wednesday":
                                                $diasSemana .= "QUA, ";
                                                break;
                                            case "Thursday":
                                                $diasSemana .= "QUI, ";
                                                break;
                                            case "Friday":
                                                $diasSemana .= "SEX, ";
                                                break;
                                            case "Saturday":
                                                $diasSemana .= "SAB, ";
                                                break;
                                        }
                                    endforeach;
                                } ?>
								<tr>
                                <td class="text-center">
                                        <?php echo ($agendamento->agen_id); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (date("d/m/Y", strtotime($agendamento->agen_data_ini))); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo ($agendamento->agen_id_loja); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (substr($diasSemana, 0, -2)); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (strtoupper($agendamento->agen_repeticao)); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (date("H:i", strtotime($agendamento->agen_tempo_limite))); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo base_url(); ?>detalhes_agendamento/<?php echo $agendamento->agen_id ?>" class="btn btn-success btn-sm"></i>Ver Detalhes</a>
                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete-<?php echo $agendamento->agen_id; ?>" data-id="<?php echo $agendamento->agen_id; ?>" style="">
                                            Excluir Registro
                                        </button>
                                        <div class="modal fade" id="delete-<?php echo $agendamento->agen_id; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">Excluir Registro</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Tem certeza que deseja excluir este registro?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form action="<?php echo base_url(); ?>Inventario/delete" method="post">
                                                            <input type="hidden" name="id" value="<?php echo $agendamento->agen_id; ?>">
                                                            <input type="submit" value="Excluir" class="btn btn-danger">
                                                        </form>
                                                        <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
								</tr>
						<?php endforeach;?>	
					</tbody>		
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row new-page-title3">
    <div class="col-md-8">
        <h1>INVENTÁRIOS AGENDADOS</h1>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default" data-collapsed="0">
            <div class="panel-body panel-datatable">
                <table id="table-export" class="table table-bordered table-striped datatable" style="width: 100%;">
                    <thead>
                        <th class="bold text-center">ID INVENTÁRIO</th>
                        <th class="bold text-center">DATA DE INÍCIO</th>
                        <th class="bold text-center">ID LOJAS</th>
                        <th class="bold text-center">DIAS DA SEMANA</th>
                        <th class="bold text-center">REPETIÇÃO</th>
                        <th class="bold text-center">LIMITE DE TEMPO</th>
                        <!-- <th class="bold text-center">TIPO DE TEMPLATE</th> -->
                        <th class="bold text-center" colspan="3" scope="colgroup">AÇÕES</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($agendamentos as $agendamento) :
                            if ($agendamento->agen_data_ini > date("Y-m-d")) {
                                $arrayDias = explode(',', $agendamento->agen_dias);
                                $diasSemana = "";

                                foreach ($arrayDias as $dia) :
                                    switch ($dia) {
                                        case "Monday":
                                            $diasSemana .= "SEG, ";
                                            break;
                                        case "Tuesday":
                                            $diasSemana .= "TER, ";
                                            break;
                                        case "Wednesday":
                                            $diasSemana .= "QUA, ";
                                            break;
                                        case "Thursday":
                                            $diasSemana .= "QUI, ";
                                            break;
                                        case "Friday":
                                            $diasSemana .= "SEX, ";
                                            break;
                                        case "Saturday":
                                            $diasSemana .= "SAB, ";
                                            break;
                                    }
                                endforeach;
                        ?>
                                <tr>
                                    <td class="text-center">
                                        <?php echo ($agendamento->agen_id); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (date("d/m/Y", strtotime($agendamento->agen_data_ini))); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo ($agendamento->agen_id_loja); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (substr($diasSemana, 0, -2)); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (strtoupper($agendamento->agen_repeticao)); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (date("H:i", strtotime($agendamento->agen_tempo_limite))); ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                                            Excluir
                                        </button>

                                        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Tem certeza que deseja excluir esse registro? Essa ação não poderá ser desfeita.
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                        <button type="button" class="btn btn-danger" id="deleteButton">Excluir</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</form>
<?php
}
?>
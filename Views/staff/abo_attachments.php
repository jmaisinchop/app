<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>

<!-- Page Header -->
<div class="page-header row no-gutters py-4">
    <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
        <span class="text-uppercase page-subtitle"><?php echo lang('Admin.form.titleFormsManage');?></span>
        <h3 class="page-title"><?php echo lang('Admin.abo.settingsAttachments');?></h3>
    </div>
</div>
<!-- End Page Header -->

<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
}
if(isset($success_msg)){
    echo '<div class="alert alert-success">'.$success_msg.'</div>';
}
echo form_open('',['id'=>'manageForm'],['do'=>'remove']).
'<input type="hidden" name="department_ticket_id" id="department_ticket_id">'.
form_close();

?>

<div class="card mt-3">
    <div class="card-header">
        <div class="row">
            <div class="col d-none d-sm-block">
                <h6 class="m-0"><?php echo lang('Admin.form.manage') ?></h6>
            </div>
            <div class="col text-md-right">
                <a href="<?php echo site_url(route_to('staff_attachments_department_new'));?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo lang('Admin.abo.newConfigDepartment');?></a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="titles">
                <tr>
                    <th>Habilitar adjuntos</th>
                    <th>Parametro</th>
                    <th>Departamento</th>
                    <th># de Adjuntos</th>
                    <th>Tamaño archivo (MB)</th>
                    <th>Tipo de archivo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($list_confdepartments) > 0): ?>
                    <?php foreach ($list_confdepartments as $item): ?>
                        <tr>
                            <td class="text-center">
                                <?php echo $item->ticket_attachment === "1" ? 'Si' : 'No'; ?>
                            </td>
                            <td><?php echo $item->source_parameter === 'advisor' ? 'Asesor' : 'Ejecutivo'; ?></td>
                            <td><?php echo getNamesDepAdjuntosById($item->department_id); ?></td>
                            <td class="text-center"><?php echo $item->ticket_attachment_number; ?></td>
                            <td class="text-center"><?php echo $item->ticket_file_size; ?></td>
                            <td>
                                <?php echo implode(', ', unserialize($item->ticket_file_type)); ?> 
                            </td>
                            <td>
                                 <div class="btn-group">
                                    <?php
                                    echo '<a href="'.site_url(route_to('staff_tdepartment_id', $item->id)).'" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';

                                    echo ' <button type="button" onclick="removeConfigDpt('.$item->id.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                                    ?>
                                </div> 
                            </td>
                        </tr>
                <?php endforeach ?>

            <?php endif ?>
        </tbody>
    </table>        
</div>    
</div>

<?php
$this->endSection();
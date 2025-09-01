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
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.abo.titleFormAttachment') ?></span>
            <h3 class="page-title"><?php echo isset($ta_department) ? lang('Admin.abo.editConfigDepartment') : lang('Admin.abo.newConfigDepartment');?></h3>
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
?>

<div class="card">
    <div class="card-body">
        <?php
        echo form_open('',[],['do' => 'submit']);
        ?>
        <div class="form-group">
            <label class="col-form-label">Habilitar archivos adjuntos</label>
            <select name="ticket_attachment_dep" class="form-control custom-select">
            <?php
            $default = set_value('ticket_attachment_dep', isset($ta_department) ? $ta_department->ticket_attachment : '1');
            foreach (['0' => 'No','1'=> 'Si'] as $k => $v){
                if($k == $default){
                    echo '<option value="'.$k.'" selected>'.$v.'</option>';
                }else{
                    echo '<option value="'.$k.'">'.$v.'</option>';
                }
            }
            ?>
            </select>
        </div>
        <div class="form-group">
            <label class="col-form-label">Parametro</label>
            <select name="ticket_parameter_dep" class="form-control custom-select">
            <?php
            $default = set_value('ticket_parameter_dep', isset($ta_department) ? $ta_department->source_parameter : 'advisor');
            foreach (['advisor' => 'Asesor','executive'=> 'Ejecutivo'] as $k => $v){
                if($k == $default){
                    echo '<option value="'.$k.'" selected>'.$v.'</option>';
                }else{
                    echo '<option value="'.$k.'">'.$v.'</option>';
                }
            }
            ?>
            </select>
        </div>
        <div class="form-group">
            <label class="col-form-label">Departamento</label>
            <?php if($departments = getDepartments(true)) {  ?>
                <select name="ticket_department_dep" class="form-control custom-select">
                    <?php 
                    foreach ($departments as $dep) {
                        if ($dep->id_padre == 0) {
                            if(isset($ta_department) && $dep->id === $ta_department->department_id){
                                echo '<option value="'.$dep->id.'" selected>'.$dep->name.'</option>';
                            } else {
                                echo '<option value="'.$dep->id.'">'.$dep->name.'</option>';
                            }
                        }                                    
                    } ?>
                </select>        
            <?php } ?>
        </div>
        <div class="form-group">
            <label class="col-form-label">Número de adjuntos</label>
            <input type="number" name="ticket_attachments_dep" class="form-control"
            value="<?php echo set_value('ticket_attachments_dep', 
                isset($ta_department) ? $ta_department->ticket_attachment_number : ''); ?>">
        </div>

        <div class="form-group">
            <label class="col-form-label">Tamaño del archivo adjunto</label>
            <div class="input-group">
                <input type="text" name="ticket_size_dep" class="form-control" 
                value="<?php echo set_value('ticket_size_dep', 
                    isset($ta_department) ? $ta_department->ticket_file_size : ''); ?>">
                <div class="input-group-append"><span class="input-group-text">MB</span></div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-form-label">Tipo de archivo</label>
            <input type="text" name="ticket_type_dep" class="form-control"
            value="<?php echo set_value('ticket_type_dep', isset($ta_department) ? 
                implode(', ', unserialize($ta_department->ticket_file_type)) : ''); ?>">
            <small class="text-muted form-text">
                Ingrese la extensión del archivo separados por coma.
            </small>
        </div>

        <div class="form-group">
            <button class="btn btn-primary"><?php echo lang('Admin.form.submit');?></button>
            <a href="<?php echo site_url(route_to('staff_attachments_department'));?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack');?></a>
        </div>
        <?php
        echo form_close();
        ?> 
    </div>
</div>

<?php
$this->endSection();
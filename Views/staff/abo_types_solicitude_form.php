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
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.abo.titleFormSolicitudes') ?></span>
            <h3 class="page-title"><?php echo isset($solicitude) ? lang('Admin.abo.editSolicitude') : lang('Admin.abo.newSolicitude');?></h3>
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
/*echo '<pre>';
print_r($solicitude);
echo '</pre>';*/
?>

<div class="card">
    <div class="card-body">
        <?php
        echo form_open('',[],['do' => 'submit']);
        ?>
        <div class="form-group">
            <label class="col-form-label">Orden</label>
            <input type="number" name="orden" class="form-control"
            value="<?php echo set_value('orden',isset($solicitude) ? $solicitude->solicitude_order : '' );?>">
        </div>

        <div class="form-group">
            <label class="col-form-label">Nombre solicitud</label>
            <input type="text" name="descripcion" class="form-control"
                value="<?php echo set_value('descripcion',isset($solicitude) ? $solicitude->description : '' );?>">
        </div>

        <div class="form-group">
            <label class="col-form-label">Tipo</label>
            <select name="type" class="form-control custom-select" id="type">
            <?php
            $default = set_value('type', isset($solicitude) ? $solicitude->type : '');
            foreach ($type_solicitude as $k => $v){
                if($k == $default){
                    echo '<option value="'.$k.'" selected>'.$v.'</option>';
                }else{
                    echo '<option value="'.$k.'">'.$v.'</option>';
                }
            }   
            ?>
            </select>
        </div>

        <div id="drop_down_select">
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <?php 
                        $value = '';
                        if(isset($solicitude)){
                            $value .= $solicitude->multiple_select === '1' ? 'checked' : '';
                        }
                     ?>
                    <input type="checkbox" name="isMultiple" class="custom-control-input" 
                    <?php echo set_value('isMultiple', $value); ?> 
                    id="enabled_multiple">
                    <label class="custom-control-label" for="enabled_multiple">Multiple Drop-down select</label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-form-label">Valores</label>
                <input type="text" name="value" class="form-control"
                value="<?php echo set_value('value',isset($solicitude) ? $solicitude->value : '' );?>">
                <small class="text-muted form-text">
                    Ingrese los valores separados por una coma. Por ejemplo: maria, carlos, etc
                </small>
            </div>   
        </div>

        <?php if (isset($solicitude)): ?>
            <div class="form-group">
                <label class="col-form-label">Color</label>
                <input type="text" name="color" class="form-control" value="<?php echo set_value('color',isset($solicitude) ? $solicitude->color : '' );?>">
            </div>  
        <?php endif ?>

        <div class="form-group">
            <label class="col-form-label">Estatus</label>
                <select name="status" class="form-control custom-select">
                    <?php 
                    $default = set_value('status', isset($solicitude) ? $solicitude->enabled : '1');
                    foreach (['1' => 'Activo', '0' =>'Inactivo'] as $k => $status) {
                        if((int)$default === (int)$k){
                            echo '<option value="'.$k.'" selected>'.$status.'</option>';
                        } else {
                            echo '<option value="'.$k.'">'.$status.'</option>';
                        }                                 
                    } ?>
                </select>        
        </div>

        <div class="form-group">
            <button class="btn btn-primary"><?php echo lang('Admin.form.submit');?></button>
            <a href="<?php echo site_url(route_to('staff_types_solicitude'));?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack');?></a>
        </div>
        <?php
        echo form_close();
        ?> 
    </div>
</div>

<?php
$this->endSection();

$this->section('script_block');
?>

<script type="text/javascript">
    $(function () {
        showFormSelect();
        $('#type').on('change', function(){
            showFormSelect();
        });
    });

    function showFormSelect (){
        var type = $('#type').val();
        if(type === 'select') {
            $('#drop_down_select').show();
        } else {
            $('#drop_down_select').hide();
        }
    }
</script>

<?php
$this->endSection();
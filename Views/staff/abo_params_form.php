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
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.params.titleFormParams');?></span>
            <h3 class="page-title"><?php echo isset($param) ? lang('Admin.params.editParam') : lang('Admin.params.newParam');?></h3>
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
            <label><?php echo lang('Admin.abo.codParam'); ?></label>
            <input type="text" name="cparam" class="form-control"
                value="<?php echo set_value('cparam',isset($param) ? $param->cparam : '' );?>">
        </div>

        <div class="form-group">
            <label><?php echo lang('Admin.abo.type'); ?></label>
            <select name="type" id="type_param" class="form-control custom-select">
                <?php
                $default = set_value('type', isset($param) ? $param->type_param : 'T');
                foreach (['T' => lang('Admin.params.typeText'),'N' => lang('Admin.params.typeNumber')] as $k => $v){
                    if($k == $default){
                        echo '<option value="'.$k.'" selected>'.$v.'</option>';
                    }else{
                        echo '<option value="'.$k.'">'.$v.'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <?php if (isset($param)): ?>
            <?php if ($param->type_param === 'T'): ?>
                <div class="form-group">
                    <label><?php echo lang('Admin.abo.vText'); ?></label>
                    <input type="text" name="vtexto" class="form-control"
                        value="<?php echo set_value('vtexto',isset($param) ? $param->param_text : '' );?>">
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label><?php echo lang('Admin.abo.vNumber'); ?></label>
                    <input type="number" name="vnumero" class="form-control"
                        value="<?php echo set_value('vnumero',isset($param) ? $param->param_number : '' );?>">
                </div>
            <?php endif ?>
        <?php else: ?>
            <div class="form-group" id="vText">
                <label><?php echo lang('Admin.abo.vText'); ?></label>
                <input type="text" name="vtextoNew" id="vtextoNew" class="form-control"
                    value="<?php echo set_value('vtextoNew',isset($param) ? $param->param_text : '' );?>">
            </div>

            <div class="form-group" id="vNumber">
                <label><?php echo lang('Admin.abo.vNumber'); ?></label>
                <input type="number" name="vnumeroNew" id="vnumeroNew" class="form-control"
                    value="<?php echo set_value('vnumeroNew');?>">
            </div>
        <?php endif ?>

        <div class="form-group">
            <label><?php echo lang('Admin.abo.description'); ?></label>
            <input type="text" name="descripcion" class="form-control"
                value="<?php echo set_value('descripcion',isset($param) ? $param->param_description : '' );?>">
        </div>

        <div class="form-group">
            <button class="btn btn-primary"><?php echo lang('Admin.form.submit');?></button>
            <a href="<?php echo site_url(route_to('staff_params_settings'));?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack');?></a>
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

<script>
    $(document).ready( function (){
        showInputValueParam();
        $('#type_param').on('change', function () {
            showInputValueParam();
        });
    });

    function showInputValueParam () {
        let input = $('#type_param').val();

        if(input === 'T') {
            $('#vnumeroNew').val('null');
            $('#vText').show();
            $('#vNumber').hide();
        } else {
            $('#vtextoNew').val('');
            $('#vNumber').show();
            $('#vText').hide();
        }
    }

</script>

<?php
$this->endSection();

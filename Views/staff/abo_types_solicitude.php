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
        <h3 class="page-title"><?php echo lang('Admin.abo.settingsSolicitudes');?></h3>
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
    <div class="card-header">
        <div class="row">
            <div class="col d-none d-sm-block">
                <h6 class="m-0"><?php echo lang('Admin.form.manage') ?></h6>
            </div>
            <div class="col text-md-right">
                <a href="<?php echo site_url(route_to('staff_types_solicitude_new'));?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo lang('Admin.abo.newTypeSolicitude');?></a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="titles">
                <tr>
                    <th><?php echo lang('Admin.abo.sorder');?></th>
                    <th><?php echo lang('Admin.abo.description');?></th> 
                    <th><?php echo lang('Admin.abo.svalue');?></th>
                    <th><?php echo lang('Admin.abo.stype');?></th>
                    <th><?php echo lang('Admin.abo.senabled');?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if($types_solicitude) {
                foreach($types_solicitude as $ts) {?>
                    <tr>
                        <td>
                            <?php echo $ts->solicitude_order;?>
                        </td>
                        <td style="color: <?php echo $ts->type === 'label' ? '#000000' : $ts->color;?>;">
                            <?php echo $ts->description;?>
                        </td>
                        <td>
                            <?php echo $ts->value;?>
                        </td>
                        <td>
                            <?php echo $ts->type;?>
                        </td>
                        <td>
                            <?php echo (int)$ts->enabled === 1 ? 'Activo' : 'Inactivo';?>
                        </td>
                        <td>
                           <div class="btn-group">
                                <?php
                                echo '<a href="'.site_url(route_to('staff_solicitude_id', $ts->id)).'" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';

                                echo ' <button type="button" onclick="removeConfigDpt('.$ts->id.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                                ?>
                            </div> 
                        </td>
                    </tr>
            <?php }
            } else { ?>
                <tr>
                    <td colspan="6"><?php echo lang('Admin.error.recordsNotFound');?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$this->endSection();
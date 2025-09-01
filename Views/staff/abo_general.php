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
        <h3 class="page-title"><?php echo lang('Admin.abo.settingsABO');?></h3>
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
            <label><?php echo lang('Admin.abo.clienteEmailOrder');?></label>
            <select name="client_email_order" class="form-control custom-select">
                <?php
                $default = set_value('client_email_order', site_config('client_email_order'));
                foreach (['desc'=>lang('Admin.abo.newestEmailFirst'),'asc'=>lang('Admin.abo.oldestEmailFirst')] as $k => $v){
                    if($default == $k){
                        echo '<option value="'.$k.'" selected>'.$v.'</option>';
                    }else{
                        echo '<option value="'.$k.'">'.$v.'</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label><?php echo lang('Admin.abo.clienteEmailPages');?></label>
            <input type="number" step="1" min="1" name="client_emails_page" class="form-control" value="<?php echo set_value('client_emails_page', site_config('client_emails_page'));?>">
            <small class="text-muted form-text"><?php echo lang('Admin.settings.ticketsPerPageDescription');?></small>
        </div>
        <div class="form-group">
            <button class="btn btn-primary"><?php echo lang('Admin.form.save');?></button>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>

<?php
$this->endSection();
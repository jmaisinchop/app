<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('client/template');
$this->section('window_title');
echo lang('Client.login.menu');
$this->endSection();
$this->section('content');
?>
<div class="container mt-5">
    <!-- Login Box -->
    <div class="card">
        <div class="row align-items-center">
            <div class="col-md-6 d-none d-md-block">
                <img src="<?php echo base_url('assets/helpdeskz/images/desk_login_client.jpg');?>" alt="" class="img-fluid">
            </div>
            <div class="col-lg-6">
                <div class="card-body">
                    <?php echo form_open('',[],['do'=>'submit']);?>
                    <img src="assets/helpdeskz/images/logo.png" alt="" class="img-fluid mb-4">
                    <h4 class="mb-3 f-w-400">
                        <?php echo lang_replace('Client.login.title', ['%site_name%' => site_config('site_name')]);?>
                    </h4>
                    <?php
                    if(isset($error_msg)){
                        echo '<div class="alert alert-danger">'.$error_msg.'</div>';
                    }
                    ?>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-envelope" style="font-size: 18px;"></i></span>
                            </div>
                            <input type="email" name="email" placeholder="<?php echo lang('Client.form.email');?>" class="form-control" value="<?php echo set_value('email');?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock" style="font-size: 20px;"></i></span>
                            </div>
                            <input type="password" name="password" placeholder="<?php echo lang('Client.form.password');?>" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <button class="btn btn-primary btn-block"><?php echo lang('Client.login.button');?></button>
                            </div>
                            <div class="col-lg-6 text-lg-right">
                                <a href="<?php echo site_url(route_to('forgot_password'));?>"><?php echo lang('Client.login.forgotPassword');?></a>
                            </div>
                        </div>
                    </div>
                    <?php echo form_close();?>
                </div>
            </div>
        </div>       
    </div>
    <!-- End Login Box -->
</div>

<?php
$this->endSection();
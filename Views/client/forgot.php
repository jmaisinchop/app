<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('client/template');
$this->section('window_title');
echo lang('Client.login.forgotPassword');
$this->endSection();
$this->section('content');
?>

<?php 
    if(\Config\Services::session()->has('form_success')){
        echo '<div class="alert alert-success">'.\Config\Services::session()->getFlashdata('form_success').'</div>';
    }
    if(isset($error_msg)){
        echo '<div class="alert alert-danger">'.$error_msg.'</div>';
    }
 ?>
<div class="container mt-5">
    <?php
    echo form_open('',[],['do'=>'submit']);
    ?>

    <div class="row d-flex justify-content-center">
        <div class="col-md-6 col-md-offset-6 p-5 bg-light">
            <div class="panel panel-default">
              <div class="panel-body">                   
                    <h3 class="text-center"><i class="fa fa-lock fa-4x"></i></h3>
                    <h2 class="heading text-center"><?php echo lang('Client.login.forgotPassword');?></h2>
                    <p class="mt-5"><?php echo lang('Client.login.forgotDescription');?></p>
                    <div class="panel-body">       
                        <div class="form-group">
                            <label class="text-left"><?php echo lang('Client.form.email');?></label>
                            <div class="input-group">                               
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-envelope" style="font-size: 18px;"></i></span>
                                </div>                             
                                <input type="email" name="email" class="form-control" value="<?php echo set_value('email');?>">
                            </div>
                        </div>
                        <?php echo $recaptcha;?>
                        <div class="form-group">
                            <button class="btn btn-primary"><?php echo lang('Client.form.submit');?></button>
                        </div>  
                  </div>
              </div>
            </div>
        </div>
    </div>

<?php
echo form_close();
?>
</div>
<?php
$this->endSection();
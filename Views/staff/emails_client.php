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
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.form.titleFormsManage') ?></span>
            <h3 class="page-title"><?php echo lang('Admin.abo.clientEmail');?></h3>
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
    '<input type="hidden" name="agent_id" id="agent_id">'.
    form_close();
            if($pager->getPageCount() > 1){
            echo $pager->links();
        }
?>

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    $details = $pager->getDetails();
                    $perPage = $details['perPage'];
                    $showing = $perPage*$details['currentPage'];
                    $total = $details['total'];
                    $from = $showing+1-$perPage;
                    if($from > 0){
                        echo lang_replace('Admin.tickets.showingResults',[
                            '%x%' => ($showing+1-$perPage),
                            '%y%' => ($showing > $total ? $total : $showing),
                            '%z%' => $total
                        ]);
                    }else{
                        echo lang('Admin.tickets.menu');
                    }

                    ?>
                </div>
            </div>

        </div>
        <div class="table-responsive">
            <table  class="table table-striped table-hover">
                <thead class="titles">
                <tr>
                    <th><?php echo lang('Admin.clientEmail.ticket');?></th>
                    <th><?php echo lang('Admin.clientEmail.advisor');?></th>
                    <th><?php echo lang('Admin.clientEmail.dcreate');?></th>
                    <th><?php echo lang('Admin.clientEmail.identification');?></th>
                    <th><?php echo lang('Admin.clientEmail.client');?></th> 
                    <th><?php echo lang('Admin.clientEmail.destino');?></th>
                    <th><?php echo lang('Admin.clientEmail.typePerson');?></th>
                    <th><?php echo lang('Admin.clientEmail.date');?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($emails_client){
                    foreach ($emails_client as $email){
                        ?>
                        <tr>
                            <td>
                                <?php echo $email->ticket;?>
                            </td>
                            <td>
                                <?php echo $email->advisor;?>
                            </td>
                            <td>
                                <?php echo time_ago($email->datecreate);?>
                            </td>
                            <td>
                                <?php echo $email->identification;?>
                            </td>
                            <td>
                                <?php echo $email->cliente;?>
                            </td>
                            <td>
                                <?php echo $email->name.'<br><small class="text-lowercase">'.$email->email.'</small>';?>
                            </td>
                            <td>
                                <?php echo $email->type_person === 'nat' ? 'Natural' : 'Jurídica';?>
                            </td>
                            <td>
                                <?php echo time_ago($email->date);?> 
                            </td>
                        </tr>
                        <?php
                    }
                }else{
                    ?>
                    <tr>
                        <td colspan="6"><?php echo lang('Admin.error.recordsNotFound');?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            </table>
        </div>
    </div>
<?php
echo $pager->links();
$this->endSection();
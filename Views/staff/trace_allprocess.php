<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
$request = \CodeIgniter\Services::request();
?>
    <script>
        function viewMessage(message){
            console.log(message);
        }
    </script>
    <!-- Page Header -->
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.form.titleFormsManage') ?></span>
            <h3 class="page-title"><?php echo lang('Admin.abo.menuAllProcess');?></h3>
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

echo '<pre>';
//print_r($departments);
echo '</pre>';
?>

    <?php echo form_open('',['method'=>'get'],['do'=>'search']);?>
    <label>Proceso</label>
    <div class="input-group input-group-seamless  mb-3">
        <select name="id_proceso" class="form-control custom-select">
            <option value="" selected>Todos</option>
            <?php
            if(isset($list_process)){
                foreach ($list_process as $item){
                    if($request->getGet('id_proceso') == $item->id){
                        echo '<option value="'.$item->id.'" selected>'.$item->name.'</option>';
                    }else{
                        echo '<option value="'.$item->id.'">'.$item->name.'</option>';
                    }
                }
            }
            ?>
        </select>
        <div class="input-group-append">
            <button class="btn btn-primary btn-sm">Consultar</button>
        </div>
    </div>
    <?php echo form_close();?>

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
                    <th><?php echo lang('Admin.traceAllProcess.id');?></th>
                    <th><?php echo lang('Admin.traceAllProcess.subject');?></th>
                    <th><?php echo lang('Admin.traceAllProcess.advisor');?></th>
                    <th><?php echo lang('Admin.form.dptsAdjunto');?></th> 
                    <th><?php echo lang('Admin.traceAllProcess.dcreate');?></th>
                    <th><?php echo lang('Admin.traceAllProcess.staff');?></th>
                    <th><?php echo lang('Admin.form.priority');?></th>
                    <!--<th><?php //echo lang('Admin.traceAllProcess.message');?></th>-->
                    <th><?php echo lang('Admin.traceAllProcess.status');?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($valijas){
                    foreach ($valijas as $valija){
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo site_url(route_to('staff_ticket_view', $valija->id));?>"><?php echo $valija->id;?></a><br>
                                <small class="text-muted"><?php echo time_ago($valija->date);?></small>
                            </td>
                            <td>
                                <?php echo resume_content($valija->subject,30); ?>
                            </td>
                            <td>
                                <?php echo $valija->user;?>
                                <div class="text-muted">
                                    <?php echo $valija->user !='' ? '<i class="fas fa-building" style="font-size: 12px; color: #c16819;"></i>' : '';?>
                                    <?php echo getNamesDepAdjuntosById($valija->dep_user); ?>
                                </div>
                            </td>
                            <td>
                                <?php echo getNamesDepAdjuntosById($valija->department_id_child)?>
                            </td>
                            <td>
                                <?php echo time_ago($valija->frespuesta);?> 
                            </td>
                            <td>
                                <?php echo $valija->staff; ?>
                                <div class="text-muted">
                                    <?php echo $valija->staff !='' ? '<i class="fas fa-building" style="font-size: 12px; color: #c16819;"></i>' : '';?>
                                    <?php echo getNamesDepAdjuntosById($valija->dep_staff); ?>
                                </div>
                            </td>
                            <td style="color: <?php echo $valija->priority_color;?>">
                                <?php echo $valija->priority_name;?>
                            </td>
                            <!--<td>
                                <a 
                                    type="button"
                                    data-toggle="modal" 
                                    data-target="#modalViewMessage"
                                    onclick ="viewMessage(<?php //echo "<<<EOT".$valija->message."EOT" ?>)">
                                    <span><i class="fas fa-eye" style="color: #007b9d;"></i></span>
                                </a>
                            </td>-->
                            <td>
                                <?php echo getStatusName($valija->status)?>
                            </td>
                        </tr>
                        <?php
                    }
                }else{
                    ?>
                    <tr>
                        <td colspan="8"><?php echo lang('Admin.error.recordsNotFound');?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Message -->
    <div class="modal fade" id="modalViewMessage" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Mensaje </h5>
                    <button type="button" id="btnCloseHeader" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" id="btnClose" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php
echo $pager->links();
$this->endSection();
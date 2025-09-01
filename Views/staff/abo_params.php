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
        <h3 class="page-title"><?php echo lang('Admin.abo.sparams');?></h3>
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
                <h6 class="mb-0"><?php echo lang('Admin.form.manage');?></h6>
            </div>
			<div class="col text-md-right">
                    <a href="<?php echo site_url(route_to('staff_param_new'));?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo lang('Admin.tickets.newDepartment');?></a>
            </div>
			
		</div>
	</div>

	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead class="titles">
				<tr>
					<th><?php echo lang('Admin.params.cparam'); ?></th>
					<th><?php echo lang('Admin.params.type'); ?></th>
					<th><?php echo lang('Admin.params.text'); ?></th>
					<th><?php echo lang('Admin.params.number'); ?></th>
					<th><?php echo lang('Admin.params.description'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					if(!isset($paramsList)){
						?>
		                    <tr>
		                        <td colspan="5"><?php echo lang('Admin.error.recordsNotFound');?></td>
		                    </tr>
	                    <?php
					} else {
						foreach ($paramsList as $param){
							?>
							<tr>
								<td><?php echo $param->cparam; ?></td>
								<td><?php echo $param->type_param; ?></td>
								<td><?php echo $param->param_text; ?></td>
								<td><?php echo $param->param_number; ?></td>
								<td><?php echo $param->param_description; ?></td>
								<td>
									<div class="btn-group">
										<?php
	                                    echo '<a href="'.site_url(route_to('staff_param_id', $param->id)).'" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';
	                                    if($param->id === -1){
	                                        echo ' <button type="button" onclick="removeDepartment('.$param->id.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
	                                    }else{
	                                        echo ' <button type="button"class="btn btn-danger btn-sm disabled"><i class="fa fa-trash"></i></button>';
	                                    }
	                                    ?>
									</div>
								</td>
							</tr>
							<?php
						}
					}

				 ?>
			</tbody>
		</table>
		
	</div>
	
</div>



<?php
$this->endSection();

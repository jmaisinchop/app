<div class="form-row">
            <div class="form-group col-md-12">
                <label><?php echo lang('Admin.form.priority');?></label>
                <select name="priority" class="form-control custom-select">
                    <?php
                    if(isset($ticket_priorities)){
                        foreach ($ticket_priorities as $item){
                            if($item->id == set_value('priority')){
                                echo '<option value="'.$item->id.'" selected>'.$item->name.'</option>';
                            }else{
                                echo '<option value="'.$item->id.'">'.$item->name.'</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('nombre') ? 'text-danger' : ''); ?>">
                    <?php echo 'Nombre del cliente'?>
                </label>
                <input type="text" name="nombre" value="<?php echo set_value('nombre'); ?>" 
                class="form-control <?php echo($validation->hasError('nombre') ? 'is-invalid' : '') ?>">
            </div> 

            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('numeroPrestamo') ? 'text-danger' : ''); ?>">
                    <?php echo 'Número de préstamo'?>
                </label>
                <input type="text" name="numeroPrestamo" value="<?php echo set_value('numeroPrestamo'); ?>" 
                class="form-control <?php echo($validation->hasError('numeroPrestamo') ? 'is-invalid' : '') ?>">
            </div>

            <div class="form-group col-md-12">
                <label class="<?php echo ($validation->hasError('email') ? 'text-danger' : ''); ?>">
                    <?php echo 'Correo electrónico'?>
                </label>
                <input type="text" id="email" name="email" value="<?php echo set_value('email'); ?>" 
                class="form-control <?php echo($validation->hasError('email') ? 'is-invalid' : '') ?>">
            </div> 
        </div>
    
    </div> <!-- Termina Card -->
    
</div> <!-- Termina Card Body -->
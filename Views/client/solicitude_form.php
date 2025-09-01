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

            <div class="form-group col-md-12">
                <label>Tipo de Cliente</label>
                <select name="typePerson" class="custom-select" id="fieldTypePerson">
                    <?php
                    foreach (['nat' => 'Natural', 'jur' => 'Jurídico'] as $k => $v){
                        if($k == set_value('typePerson')){
                            echo '<option value="'.$k.'" selected>'.$v.'</option>';
                        }else{
                            echo '<option value="'.$k.'">'.$v.'</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('identificacion') ? 'text-danger' : ''); ?>">
                    <?php echo lang('Client.form.identification');?>
                </label>
                <input type="text" name="identificacion" value="<?php echo set_value('identificacion'); ?>" 
                class="form-control <?php echo($validation->hasError('identificacion') ? 'is-invalid' : '') ?>">
            </div> 

            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('nombreCliente') ? 'text-danger' : ''); ?>">
                    <?php echo lang('Client.form.nameClient');?>
                </label>
                <input type="text" name="nombreCliente" value="<?php echo set_value('nombreCliente'); ?>" 
                class="form-control <?php echo($validation->hasError('nombreCliente') ? 'is-invalid' : '') ?>">
            </div>

            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('destino1') ? 'text-danger' : ''); ?>">
                    <?php echo lang('Client.form.nameDestination');?>
                </label>
                <input type="text" name="destino1" value="<?php echo set_value('destino1'); ?>" 
                class="form-control <?php echo($validation->hasError('destino1') ? 'is-invalid' : '') ?>">
            </div> 

            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('emailCliente') ? 'text-danger' : ''); ?>">
                    <?php echo lang('Client.form.emailClient');?>
                </label>
                <input type="text" id="email1" name="emailCliente" value="<?php echo set_value('emailCliente'); ?>"
                class="form-control <?php echo($validation->hasError('emailCliente') ? 'is-invalid' : '') ?>">
            </div>
        </div>
        <div class="form-row" id="personJur">
            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('destino2') ? 'text-danger' : ''); ?>">
                    <?php echo lang('Client.form.nameDestination2');?>
                </label>
                <input type="text" name="destino2" value="<?php echo set_value('destino2'); ?>" 
                class="form-control <?php echo($validation->hasError('destino2') ? 'is-invalid' : '') ?>">
            </div>
            <div class="form-group col-md-6">
                <label class="<?php echo ($validation->hasError('emailCliente2') ? 'text-danger' : ''); ?>">
                    <?php echo lang('Client.form.emailClient2');?>
                </label>
                <input type="text" name="emailCliente2" id="email2" value="<?php echo set_value('emailCliente2'); ?>"
                class="form-control <?php echo($validation->hasError('emailCliente2') ? 'is-invalid' : '') ?>">
            </div>
        </div>
        
    </div> <!-- Termina Card -->
    
</div> <!-- Termina Card Body -->

<div class="card mt-3">
    <div class="card-body">
        <div class="form-row">
        <div class="form-group col-md-12">
        <?php 
        foreach ($ticket_solicitude as $solicitude) { ?>
            <?php if($solicitude->type === 'label') { ?>
            <h6 class="text-center" style="background: #007B9D; padding: 7px;
                color: <?php echo $solicitude->color; ?>;"> 
                <?php echo $solicitude->description ?>
            </h6>

            <?php } elseif($solicitude->type === 'text') { ?>
            <label style="color: <?php echo $solicitude->color; ?>;">
                <?php echo $solicitude->description; ?></label>
                <div class="input-numbers">
                    <input type="text" id="<?php echo $solicitude->id; ?>"
                    class="form-control" onchange="changeInputNumbers()">
                </div>
                <div class="id-input-numbers">
                    <input type="hidden" id="<?php echo $solicitude->id;  ?>" name="solicitudes[]" 
                    value="<?php echo $solicitude->id; ?>"> 
                </div>

            <?php } elseif($solicitude->type === 'select') { ?>
                <label style="color: <?php echo $solicitude->color; ?>;">
                    <?php echo $solicitude->description; ?>        
                </label>
                <div class="select-solicitude">
                    <select <?php echo $solicitude->multiple_select === "1" ? 'multiple' : ''; ?> 
                    class="custom-select" name="solSelect" id="<?php echo $solicitude->id; ?>">
                    <option value="">-------------------</option>
                    <?php   
                    $dataArray = explode(",", $solicitude->value);
                    foreach ($dataArray as $k => $v){
                        echo '<option value="'.$k.'">'.$v.'</option>';
                        /*if($k == set_value('solicitudes[]')){
                            echo '<option value="'.$k.'" selected>'.$v.'</option>';
                        }else{
                            echo '<option value="'.$k.'">'.$v.'</option>';
                        }*/
                    }
                    ?>
                    </select>
                </div>
                <div class="id-select-solicitude">
                    <input type="hidden" id="<?php echo $solicitude->id;  ?>" name="solicitudes[]" 
                    value="<?php echo $solicitude->id; ?>"> 
                </div>
            <?php } elseif($solicitude->type === 'checkbox') { ?>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" id="sol<?php echo $solicitude->id; ?>" name="solicitudes[]" 
                    value="<?php echo $solicitude->id; ?>" class="custom-control-input">
                    <label class="custom-control-label" for="sol<?php echo $solicitude->id; ?>" 
                        style="color: <?php echo $solicitude->color; ?>;">
                        <?php echo $solicitude->description; ?>
                    </label>
                </div>
            <?php } ?>
        <?php } ?>
        </div>
        </div>
    </div>
</div>
<div class="form-row">
    <?php 
        foreach ($solicitudeClient as $sol) { 
            $solicitudeSelect = unserialize($sol['solicitudes']);
            /*echo '<pre>';
            print_r($solicitudeSelect);
            echo '</pre>';*/
            ?>
            <div class="form-group col-md-6">
                <label><?php echo lang('Client.form.identification'); ?></label>
                <input type="text" class="form-control" 
                value="<?php echo $sol['identificacion']; ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label><?php echo lang('Client.form.nameClient'); ?></label>
                <input type="text" class="form-control" 
                value="<?php echo $sol['nombre']; ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label>Nombre Destinatario Final 1</label>
                <input type="text" class="form-control" 
                value="<?php echo $sol['destino1']; ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label>Correo electrónico 1</label>
                <input type="text" class="form-control"
                value="<?php echo $sol['email']; ?>" readonly>
            </div> 

            <?php if ($sol['tipopersona'] === 'jur' && $sol['destino2'] !="" && $sol['email2'] !=""): ?>
                <div class="form-group col-md-6">
                    <label >Nombre Detinatario Final 2</label>
                    <input type="text" class="form-control" 
                    value="<?php echo $sol['destino2']; ?>" readonly>
                </div>
                <div class="form-group col-md-6">
                    <label >Correo electrónico 2</label>
                    <input type="text" class="form-control"
                    value="<?php echo $sol['email2']; ?>" readonly>
                </div>                              
            <?php endif ?>                               

            <div class="form-group col-md-12 ">
            <?php 
                foreach ($ticket_solicitude as $solicitude) {
                    if ($solicitude->type === 'label') { ?>
                        <h6 class="text-center" style="background: #007B9D; padding: 7px;
                            color: <?php echo $solicitude->color; ?>;"> 
                            <?php echo $solicitude->description ?>
                        </h6>
                    <?php } elseif($solicitude->type === 'text') { ?>
                        <label style="color: <?php echo $solicitude->color; ?>;">
                            <?php echo $solicitude->description; ?>
                        </label>
                        <?php
                            $input = '<input type="text" class="form-control" ';
                                foreach($solicitudeSelect as $select ){ 
                                    $arrayNumber = explode(",", $select);
                                    if(is_array($arrayNumber) && isset($arrayNumber[1])){
                                        if((int)$arrayNumber[0] === (int)$solicitude->id){
                                            $input .= 'value="'.$arrayNumber[1].'" ';
                                        }       
                                    }
                                } 
                            $input .='readonly >'; 
                            echo $input;    
                         ?>
                    <?php } elseif ($solicitude->type === 'select') { ?>
                        <label style="color: <?php echo $solicitude->color; ?>;">
                            <?php echo $solicitude->description; ?>        
                        </label>
                        <select <?php echo $solicitude->multiple_select === "1" ? 'multiple' : '' ?> 
                            class="form-control custom-select select-solitude" id="<?php echo $solicitude->id; ?>">
                        <?php   
                        $dataArray = explode(",", $solicitude->value);
                        foreach ($dataArray as $k => $v){
                            foreach ($solicitudeSelect as $itemSelect){
                                $arraySelect = explode(",", $itemSelect);
                                if(is_array($arraySelect)){
                                    if((int)$arraySelect[0] === (int) $solicitude->id){
                                        if((int)$solicitude->multiple_select === 1){
                                            //Elimino la primera posicion, valor ID del tipo de Solicitud.
                                            unset($arraySelect[0]); 
                                            foreach ($arraySelect as $as){
                                                if((int) $as === $k){
                                                    echo '<option value="'.$k.'" selected>'.$v.'</option>';
                                                }           
                                            }
                                        } else {
                                            if($k == (int) $arraySelect[1]){
                                                echo '<option value="'.$k.'" selected>'.$v.'</option>';
                                            }
                                        } 
                                    }                                   
                                } 
                            }
                        }
                        ?>
                        </select>
                    <?php } elseif($solicitude->type === 'checkbox') { ?>
                        <div class="custom-control custom-checkbox check-client">
                            <input type="checkbox" id="sol<?php echo $solicitude->id; ?>" name="solicitudes[]"
                            value="<?php echo $solicitude->id; ?>" 
                            <?php echo in_array($solicitude->id,$solicitudeSelect) ? 'checked' : ''; ?>
                            class="custom-control-input">
                            <label class="custom-control-label" 
                            for="sol<?php echo $solicitude->id; ?>"
                            style="color: <?php echo $solicitude->color; ?>;">
                            <?php echo $solicitude->description; ?>
                        </label>
                    </div>
                <?php } ?>
            <?php } ?>
            </div>
        <?php 
    	}
	?>
</div>

<!--==========================================================================
=            Para cargar la respuesta de la solicitud del cliente            =
===========================================================================-->
<div id="msg_<?php echo $item->id;?>" class="form-group" style="display: none;">
    <h4 style="text-align: center; text-transform: uppercase;">Detalle de la solicitud del cliente</h4>
    <ol>
        <?php foreach ($solicitudeClient as $sd) {
            $selectedTypeSol = unserialize($sd['solicitudes']);
            /*echo '<pre>';
            print_r($selectedTypeSol);
            echo '</pre>';*/
            ?>
            <?php foreach ($ticket_solicitude as $v): ?>

                <?php foreach ($selectedTypeSol as $id): ?>

                    <?php
                        $arrayIdSol = explode(",", $id);
                        if($v->id === $arrayIdSol[0] && $v->type === 'checkbox') {
                            echo '<li style="font-size: 14px;">'.$v->description.'</li>';
                        } elseif($v->id === $arrayIdSol[0] && $v->type === 'text'){
                            $arrayText = explode(",", $id);
                            if(isset($arrayText[1])){
                                echo '<li style="font-size: 14px;">'.$v->description. ' => <span style="color:gray;">'.$arrayIdSol[1].'</span></li>';
                            } 
                        } elseif ($v->id === $arrayIdSol[0] && $v->type === 'select'){
                            $arraySelected = explode(",", $id);
                            $dataSelect = explode(",", $v->value);
                            if(isset($arraySelected[1])) {
                                echo '<li style="font-size: 14px;">'.$v->description.'</li>';
                            }
                            //Elimino la primera posicion, valor ID del tipo de Solicitud.
                            unset($arraySelected[0]);
                            foreach ($dataSelect as $k => $vs) {

                                foreach ($arraySelected as $s) {
                                    echo '<ul>';
                                    if((int)$k === (int)$s){
                                        echo '<li style="font-size: 14px;">'.$vs.'</li>';
                                    }  
                                    echo '</ul>'; 
                                }
                            } 
                        }
                     ?>
                    
                <?php endforeach ?>
                
            <?php endforeach ?>
        <?php } ?>
    </ol>                         
</div>
<!--====  End of Para cargar la respuesta de la solicitud del cliente  ====-->

<script type="text/javascript">
	//Para deshabilitar los check de los tipo de solicitud
	let check = document.querySelectorAll('div.check-client > input');
	let select = document.querySelectorAll('.select-solitude');

	for (var i = 0; i < check.length; i++) {
	    check[i].disabled = true;
	}

	for (var j = 0; j < select.length; j++) {
		select[j].disabled = true;
	}
</script>

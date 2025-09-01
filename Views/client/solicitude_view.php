<?php 

#Recibo el parametro definido en ticket_view.php (Client)
 $ticket = getTicket($id_ticket);
 $ticket_message = getMessages($id_message);
 $ticket_solicitude = getSolicitudes();
/*echo '<pre>';
print_r($ticket);
echo '</pre>';*/
 ?>

 <div class="form-row">
 	<?php 
 	$solicitude = unserialize($ticket_message->message);
 	foreach ($solicitude as $sol) { 
 		$allSol = unserialize($sol['solicitudes']);
 		?>
 		<div class="form-group col-md-6">
 			<label ><?php echo lang('Client.form.identification'); ?></label>
 			<input type="text" class="form-control" 
 			value="<?php echo $sol['identificacion']; ?>" readonly>
 		</div>
 		<div class="form-group col-md-6">
 			<label ><?php echo lang('Client.form.nameClient'); ?></label>
 			<input type="text" class="form-control" 
 			value="<?php echo $sol['nombre']; ?>" readonly>
 		</div>
 		<div class="form-group col-md-6">
 			<label >Nombre Destinatario Final 1</label>
 			<input type="text" class="form-control"
 			value="<?php echo $sol['destino1']; ?>" readonly>
 		</div>   
        <div class="form-group col-md-6">
            <label ><?php echo lang('Client.form.emailClient'); ?></label>
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

 		<div class="form-group col-md-12">
 			<?php 
 			foreach ($ticket_solicitude as $itemSol) {
 				?>
                <?php if($itemSol->type === 'label') { ?>
                    <h6 class="text-center" style="background: #007B9D; padding: 7px;
                        color: <?php echo $itemSol->color; ?>;"> 
                        <?php echo $itemSol->description ?>
                    </h6>
 				<?php } elseif($itemSol->type === 'text') { ?>
 					<label style="color: <?php echo $itemSol->color; ?>;"><?php echo $itemSol->description; ?></label>
                    <?php
                        $input = '<input type="text" class="form-control" ';
                            foreach($allSol as $select ){
                                $arrayNumber = explode(",", $select);
                                if(is_array($arrayNumber) && isset($arrayNumber[1])){
                                    if( (int)$arrayNumber[0] === (int)$itemSol->id){
                                        $input .= 'value="'.$arrayNumber[1].'" ';
                                    }       
                                }
                            } 
                        $input .='readonly >'; echo $input;    
                    ?>
                <?php } elseif($itemSol->type === 'select') { ?>
                    <label style="color: <?php echo $itemSol->color; ?>;">
                        <?php echo $itemSol->description; ?>        
                    </label>
                    <select <?php echo $itemSol->multiple_select === "1" ? 'multiple' : '' ?> 
                    class="custom-select" id="<?php echo $itemSol->id; ?>">
                    <?php   
                    $dataArray = explode(",", $itemSol->value);
                        foreach ($dataArray as $k => $v){
                            foreach ($allSol as $itemSelect){
                                $arraySelect = explode(",", $itemSelect);
                                if(is_array($arraySelect)){
                                    if((int)$arraySelect[0] === (int) $itemSol->id){
                                        if((int)$itemSol->multiple_select === 1){
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
 				<?php } elseif($itemSol->type === 'checkbox') { ?>
 					<div class="custom-control custom-checkbox disabled_inputs_check">
 						<input type="checkbox" id="sol<?php echo $itemSol->id; ?>" name="solicitudes[]"
 						value="<?php echo $itemSol->id; ?>" 
 						<?php echo in_array($itemSol->id,$allSol) ? 'checked' : ''; ?>
 						class="custom-control-input">
 						<label class="custom-control-label" for="sol<?php echo $itemSol->id; ?>" 
 								style="color: <?php echo $itemSol->color; ?>;">
 							<?php echo $itemSol->description; ?>
 						</label>
 					</div>
 				<?php } ?>
 			<?php } ?>
 		</div>
 	<?php } ?>
 </div>


 <script type="text/javascript">
     let check = document.querySelectorAll('div.disabled_inputs_check > input');
     let select = document.querySelectorAll('select');
     for (var i = 0; i < check.length; i++) {
         check[i].disabled = true;
     }

     for (var i = 0; i < select.length; i++) {
         select[i].disabled = true;
     }
 </script>
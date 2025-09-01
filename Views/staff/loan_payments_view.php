<!--==========================================================================
=            Para cargar la respuesta de la solicitud pagos recibidos        =
===========================================================================-->
<div class="form-row">
    <?php 
            /*echo '<pre>';
            print_r($loanPayment);
            echo '</pre>';*/
            ?>
            <div class="form-group col-md-6">
                <label>Nombre cliente</label>
                <input type="text" class="form-control" 
                value="<?php echo $loanPayment->client_name; ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label>Número de préstamo</label>
                <input type="text" class="form-control" 
                value="<?php echo $loanPayment->loan_number; ?>" readonly>
            </div>
            <div class="form-group col-md-12">
                <label>Correo electrónico</label>
                <input type="text" class="form-control" 
                value="<?php echo $loanPayment->email; ?>" readonly>
            </div>                           
        <?php 
	?>
</div>
<!--====  End of Para cargar la respuesta de la solicitud de pagos recibidos  ====-->

<!--==========================================================================
=            Para cargar la respuesta                                        =
===========================================================================-->
<div id="msg_<?php echo $item->id;?>" class="form-group" style="display: none;">
    <h4 style="text-align: center; text-transform: uppercase;">Detalle del pago recibo</h4>
    <ol>
        <?php echo '<li style="font-size: 14px;">' .'<b>Nombre del cliente: </b>' .$loanPayment->client_name.'</li>'; ?>
        <?php echo '<li style="font-size: 14px;">' .'<b>Número del préstamo: </b>' .$loanPayment->loan_number.'</li>'; ?>
        <?php echo '<li style="font-size: 14px;">' .'<b>Correo electrónico: </b>' .$loanPayment->email.'</li>'; ?>
    </ol>                         
</div>
<!--====  End of Para cargar la respuesta  ====-->
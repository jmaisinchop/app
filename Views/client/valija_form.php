<!-- Texto Cabecera --> 
<div class="form-row">
    <div class="form-group col-md-4">
        <label><?php echo lang('Client.form.remitent'); ?></label>
    </div>
    <div class="form-group col-md-3">
        <label><?php echo lang('Client.form.addressee'); ?></label>
    </div>
    <div class="form-group col-md-3">
        <label><?php echo lang('Client.form.reference'); ?></label>
    </div>
    <div class="form-group col-md-1">
        <label><?php echo lang('Client.form.quantity'); ?></label>
    </div>
    <div class="form-group col-md-1"></div>
</div>  

<!-- Tabla Dinamica -->        
<div class="form-row">
    <table class="table table-light" id="tabla">
        <tbody>
            <tr class="fila-fija">
                <td class="form-group col-md-4">
                    <input type="text" name="remitente[]" id="remitente" class="form-control" value="<?php echo set_value('fullname', client_data('fullname'));?>" readonly required>
                </td>
                <td class="form-group col-md-3">
                    <input type="text" name="destinatario[]" id="destinatario" class="form-control" required>
                </td>

                <td class="form-group col-md-3">                                
                    <input type="text" name="referencia[]" id="referencia" class="form-control" required>
                </td>

                <td class="form-group col-md-1">                                
                    <input type="number" name="cantidad[]" value="1" class="form-control" required>
                </td>

                <td class=" form-group col-md-1 eliminar">
                    <i class="fa fa-times text-danger" style="font-size: 30px; cursor: pointer;"></i>
                </td>
            </tr> 
        </tbody>

    </table>
</div>

<div class="form-group float-right">
    <button id="adicional" name="adicional" type="button" class="btn btn-warning"> Más + </button>
</div>

<div class="form-group">
    <input type="hidden" name="message">
</div>
<?php
/**
* @var $this \CodeIgniter\View\View
* @var $validation \CodeIgniter\Validation\Validation
*/
$this->extend('client/template');
$this->section('window_title');
echo lang('Client.submitTicket.menu');
$this->endSection();
$this->section('content');
header("Access-Control-Allow-Origin: *");

//Contabiliza el numero de dpts del proceso Padre
$countChildDpts = count_child_departments($department->id);

#Consulta el parametro para el formulario ATENCION DEL CLIENTE
$paramAtentionClient = getParam('DEPARTMENT_ATTENTION_CLIENT');

#Consulta el parametro para el formulario PAGOS RECIBIDOS PRESTAMOS
$paramLoans = getParam('DEPARTMENT_LOAN_PAYMENTS');

$paramAttentionClientText = isset($paramAtentionClient->param_text) ? trim($paramAtentionClient->param_text) : '';
$paramLoansText = isset($paramLoans->param_text) ? trim($paramLoans->param_text) : '';
?>
<div class="container mt-5">
    <h1 class="heading mb-5">
        <?php if($paramAttentionClientText === $department->name ){
            echo lang('Client.submitTicket.title2');
        } else {
            echo lang('Client.submitTicket.title');
        }
        ?>
    </h1>

<script>
    $(function(){
    // Clona la fila oculta que tiene los campos base, y la agrega al final de la tabla
    $("#adicional").on('click', function(){
        $("#tabla tbody tr:eq(0)").clone().removeClass('fila-fija').appendTo("#tabla");
    });

    // Evento que selecciona la fila y la elimina
    $(document).on("click",".eliminar",function(){
        var nFilas = $("#tabla tr").length;
        var parent = $(this).parents().get(0);
        if(nFilas > 1){
            $(parent).remove();
        }

    });
    });
</script>
<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
}

echo form_open_multipart('',
    ['name' => 'myForm'],
    ['do' => 'submit']);

    ?>
    <div class="row">
        <div class="col-lg-12">

            <input type="hidden" name="emailEjecutivo" value="<?php echo client_data('email'); ?>">
            
            <h3 class="mb-3" style="font-weight: 300"><?php echo lang('Client.submitTicket.generalInformation');?></h3>
            <?php if(!client_online()):?>
                <div class="form-group">
                    <label class="<?php echo ($validation->hasError('fullname') ? 'text-danger' : '');?>">
                        <?php echo lang('Client.form.fullName');?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="fullname" value="<?php echo set_value('fullname');?>" class="form-control <?php echo ($validation->hasError('fullname') ? 'is-invalid' : '');?>" required>
                </div>
                <div class="form-group">
                    <label class="<?php echo ($validation->hasError('email') ? 'text-danger' : '');?>">
                        <?php echo lang('Client.form.email');?> <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email" value="<?php echo set_value('email');?>" class="form-control <?php echo ($validation->hasError('email') ? 'is-invalid' : '');?>" required>
                </div>
            <?php endif;?>

            <div class="form-group">
                <label>
                    <?php echo lang('Client.form.department');?>
                </label>
                <input type="text" value="<?php echo esc($department->name);?>" class="form-control" readonly>
            </div>
            
            <?php
            if(isset($customFields)) { ?>
                <div class="form-row">
                    <?php
                    foreach ($customFields as $customField){
                        echo parseCustomFieldsForm($customField);
                    }
                    ?>
                </div>
            <?php } ?>

            <?php if($paramAttentionClientText === $department->name) { ?>
                <?php include_once ('solicitude_form.php'); ?>
            <?php } ?>

            <?php if($paramLoansText === $department->name) { ?>
                <?php include_once ('loan_payments_form.php'); ?>
            <?php } ?>

            <?php $departmentName = trim($department->name); ?>

            <?php if($paramAttentionClientText !== $departmentName && $paramLoansText !== $departmentName) { ?>
                <?php if (trim(getParamText('CREDIT_PROCESS')) === $department->name): ?>
                    <div class="form-group">
                        <label>Tipo Cliente <span class="text-danger">*</span></label>
                        <?php foreach (['1' => 'Persona Natural','2'=> 'Persona Jurídica'] as $k => $v): ?>
                           <div class="custom-control custom-radio check_type_person">
                                <input type="radio" id="typePerson<?php echo $k; ?>" name="typeClientCreditProcess" value="<?php echo $k; ?>" class="custom-control-input">
                                <label class="custom-control-label" for="typePerson<?php echo $k; ?>"><?php echo $v; ?></label>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
                <?php
                // Verificamos si el departamento actual es de los que usan la nueva lógica de selección única (radio buttons)
                $unique_selection_parents_str = trim(getParamText('DEPS_HIJOS_SELECCION_UNICA'));
                $unique_selection_parents_list = !empty($unique_selection_parents_str) ? explode(',', $unique_selection_parents_str) : [];
                $is_unique_selection = in_array($department->name, $unique_selection_parents_list);
                ?>

                <?php if ($is_unique_selection && isset($child_departments) && !empty($child_departments)): // Lógica nueva para "Sistemas" con radio buttons ?>
                <div class="form-group">
                    <label>Seleccione un Departamento Adjunto (solo uno): <span class="text-danger">*</span></label>
                    <?php foreach($child_departments as $child): ?>
                        <div class="custom-control custom-radio">
                            <input type="radio"
                                   id="child_<?php echo $child->id; ?>"
                                   name="departamento_adjunto"
                                   value="<?php echo $child->id; ?>"
                                   class="custom-control-input child-department-radio"
                                   <?php echo set_radio('departamento_adjunto', $child->id); ?>
                            >
                            <label class="custom-control-label" for="child_<?php echo $child->id; ?>">
                                <?php echo esc($child->name); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php elseif ($countChildDpts > 0): // Tu lógica original para los demás departamentos con checkboxes ?>
                    <div class="form-group">
                        <label>
                            <?php echo lang('Client.form.dptsAdjunto');?> <?php echo trim(getParamText('CREDIT_PROCESS')) === $department->name ? "" : '<span class="text-danger">*</span>'?>
                        </label>
                        <?php
                        if($departments = getDepartmentsChild(true, $department->id)){
                            foreach ($departments as $item){
                                ?>
                                <div class="custom-control custom-checkbox departments_child">
                                    <input type="checkbox" id="department<?php echo $item->id;?>" name="departamentos[]" value="<?php echo $item->id;?>" class="custom-control-input">
                                    <label class="custom-control-label" for="department<?php echo $item->id;?>"><?php echo $item->name;?></label>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <h3 class="mt-5 mb-3" style="font-weight: 300"><?php echo lang('Client.form.yourMessage');?></h3>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <?php $tipoAsunto = $department->name == "Valijas" ? lang('Client.form.subject') : lang('Client.form.asuntoCredito') ?>

                        <label class="<?php echo ($validation->hasError('subject') ? 'text-danger' : '');?>">
                            <?php echo $tipoAsunto; ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" value="<?php echo set_value('subject');?>" class="form-control <?php echo ($validation->hasError('subject') ? 'is-invalid' : '');?>" required>
                    </div>

                     <div class="form-group col-md-6">
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

                    <?php if (trim(getParamText('ONE_WAY_TICKET')) === $department->name ): ?>
                        <div class="form-group col-md-12">
                            <label class="<?php echo ($validation->hasError('advisor') ? 'text-danger' : '');?>">
                                <?php echo lang('Client.form.advisor');?>
                                <span class="text-danger">*</span>
                            </label>
                            <select name="advisor" class="form-control custom-select <?php echo ($validation->hasError('advisor') ? 'is-invalid' : '');?>">
                                <option value="">Seleccione asesor...</option>
                                <?php
                                if(isset($advisors_commercial)){
                                    foreach ($advisors_commercial as $advisor){
                                        echo '<option value="'.$advisor->id.'">'.$advisor->fullname.'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif ?>
                    </div>

                <?php if ($department->name == "Valijas"): ?>
                    
                    <?php include_once ('valija_form.php') ?>

                <?php else: ?>
                    <div class="form-group">
                        <textarea name="message" rows="10" class="form-control <?php echo ($validation->hasError('message') ? 'is-invalid' : '');?>" required><?php echo set_value('message');?></textarea>
                    </div>
                <?php endif ?>
            <?php } ?>

            <?php
            $configAttachmentDep = getConfigDepartmentById($department->id, 'advisor');
            if($configAttachmentDep !=null) {
                if($configAttachmentDep->ticket_attachment){
                ?>
                <div class="form-group <?php echo $department->name === $paramAttentionClientText ? 'mt-3' :''; ?>">
                    <label><?php echo lang('Client.form.attachments');?>
                        <small class="text-danger" id="attachment-notice" style="display: none;">(Adjunto obligatorio)</small>
                    </label>
                    <?php
                    for($i=1;$i<=$configAttachmentDep->ticket_attachment_number;$i++){
                        ?>
                        <div class="custom-file mb-2">
                            <input type="file" class="custom-file-input" name="attachment[]" id="customFile<?php echo $i;?>">
                            <label class="custom-file-label" for="customFile<?php echo $i;?>" data-browse="<?php echo lang('Client.form.browse');?>"><?php echo lang('Client.form.chooseFile');?></label>
                        </div>
                        <?php
                    }
                    ?>
                    <small class="text-muted"><?php echo lang('Client.form.allowedFiles');?> <?php echo '*.'.implode(', *.', unserialize($configAttachmentDep->ticket_file_type)).'. Tamaño máximo del archivo: '.$configAttachmentDep->ticket_file_size.' MB';?></small>
                </div>
                <?php
                }
            } else {
                if(site_config('ticket_attachment')){
                    ?>
                    <div class="form-group">
                        <label><?php echo lang('Client.form.attachments');?>
                           <small class="text-danger" id="attachment-notice" style="display: none;">(Adjunto obligatorio)</small>
                        </label>
                        <?php
                        for($i=1;$i<=site_config('ticket_attachment_number');$i++){
                            ?>
                            <div class="custom-file mb-2">
                                <input type="file" class="custom-file-input" name="attachment[]" id="customFile<?php echo $i;?>">
                                <label class="custom-file-label" for="customFile<?php echo $i;?>" data-browse="<?php echo lang('Client.form.browse');?>"><?php echo lang('Client.form.chooseFile');?></label>
                            </div>
                            <?php
                        }
                        ?>
                        <small class="text-muted"><?php echo lang('Client.form.allowedFiles');?> <?php echo '*.'.implode(', *.', unserialize(site_config('ticket_file_type')));?></small>
                    </div>
                    <?php
                }
            }
            if(isset($captcha)){
                echo $captcha;
            }
            ?>

            <div class="button_group mt-3">
                <button class="btn btn-primary" name="btnSubmit" id=btnSubmit><?php echo lang('Client.form.submit');?></button>
                <a href="<?php echo site_url(route_to('submit_ticket'));?>" class="btn btn-secondary" id="btnBack"><?php echo lang('Client.form.goBack');?></a>
            </div>
        </div>
    </div>

    <?php
    echo form_close();
    ?>
</div>

<?php
$this->endSection();
$this->section('script_block');
?>
<script type="text/javascript" src="<?php echo base_url('assets/components/bs-custom-file-input/bs-custom-file-input-min.js');?>"></script>
<script>
    //Para concatenar los numeros de (CTA, DPF y CREDITO) con el id (TIPO DE SOLICITUD), proceso ATENCION AL CLIENTE.
    let inputNumbers = document.querySelectorAll('div.input-numbers > input');
    let idInputNumbers = document.querySelectorAll('div.id-input-numbers > input');

    //Select tipo de Solicitud
    let selectData = document.querySelectorAll('div.select-solicitude > select');
    let idSelect = document.querySelectorAll('div.id-select-solicitude > input');

    //Check tipo de persona. Proceso de Creditos
    let radioTypePerson = document.querySelectorAll('div.check_type_person > input');
    let dptsChild = document.querySelectorAll('div.departments_child > input');

    $(function(){
        // =============================================================
        // ===== INICIO: CÓDIGO NUEVO PARA DEPARTAMENTOS ADJUNTOS ======
        // =============================================================
        <?php
            // Obtenemos el texto del parámetro y lo convertimos en array de forma segura
            $required_deps_text = trim(getParamText('DEPS_HIJOS_ADJUNTO_OBLIGATORIO'));
            $required_deps_array = !empty($required_deps_text) ? explode(',', $required_deps_text) : [];
        ?>
        const requiredChildDepsList = <?php echo json_encode($required_deps_array); ?>;

        const departmentNames = {
            <?php if(isset($child_departments)): ?>
                <?php foreach($child_departments as $dep): ?>
                    "<?php echo $dep->id; ?>": "<?php echo esc($dep->name); ?>",
                <?php endforeach; ?>
            <?php endif; ?>
        };

        const attachmentNotice = $('#attachment-notice');

        function checkAttachmentRequirement() {
            const selectedRadio = $('input[name="departamento_adjunto"]:checked');
            if (selectedRadio.length > 0) {
                const childId = selectedRadio.val();
                const childName = departmentNames[childId] || '';
                if (requiredChildDepsList.includes(childName)) {
                    attachmentNotice.show();
                } else {
                    attachmentNotice.hide();
                }
            } else {
                 attachmentNotice.hide();
            }
        }

        // El selector ahora es más específico para no interferir con otros radios
        $('input.child-department-radio').on('change', checkAttachmentRequirement);
        checkAttachmentRequirement(); // Revisar al cargar la página
        // =============================================================
        // ===== FIN: CÓDIGO NUEVO AÑADIDO =============================
        // =============================================================
        
        checkTypePersonCreditProcess();
        showFormPersonJur();
        $('#fieldTypePerson').on('change', function (){
            showFormPersonJur();
        });

        //Valida que el email del destino 1 siempre sea minúsculas
        $("#email1").on('input', function(){
            $(this).val( $(this).val().toLowerCase() );
        });

        //Event Change al seleccionar un dato del campo ComboBox (Tipo de Solicitud)
        changeSelectSolcitude();
        for (var i = 0; i < selectData.length; i++) {
            $(selectData[i]).on('change',function (){
                changeSelectSolcitude();
            })
        }

        //Event Change al radio button Tipo Cliente
        for (var i = 0 ; i < radioTypePerson.length; i++) {
            $(radioTypePerson[i]).on('change', function (){
                checkTypePersonCreditProcess();
            });
        }

        //Evento click submit - Guardar Ticket
        $('#btnSubmit').on('click', function () {
            enabledCheckBeforeSubmit();
        });

        $(document).ready(function () {
            bsCustomFileInput.init();
        });
    })

    function changeInputNumbers(){
        for(var i=0; i < inputNumbers.length; i++){
            for(var j=0; j<idInputNumbers.length; j++){
                if(idInputNumbers[j].id === inputNumbers[i].id && inputNumbers[i].value !=''){
                    idInputNumbers[j].value = idInputNumbers[j].id +','+ inputNumbers[i].value;
                    console.log('value input: '+idInputNumbers[j].value);
                    break;
                }
            }
        }
    }

    function changeSelectSolcitude(){
        for (var i = 0; i < selectData.length; i++) {
            let valueSelect = $(selectData[i]).val();
            for (var j = 0; j < idSelect.length; j++) {
                let selectId = $(idSelect[j]).attr('id');
                let selectValueid = $(idSelect[i]).attr('id');
                if( selectId === selectValueid &&  valueSelect !=''){
                    valueSelect = selectValueid+ ',' +valueSelect
                    $(idSelect[j]).val(valueSelect);
                    console.log('value select: '+$(idSelect[j]).val());
                    break;
                }
            }
        }
    }

    function showFormPersonJur() {
        let typePerson = $('#fieldTypePerson').val();
        if(typePerson === 'nat'){
            $('#personJur').hide();
        } else {
            $('#personJur').show();

            //Valida que el email del destino 2 siempre sea minúsculas
            $("#email2").on('input', function(){
                $(this).val( $(this).val().toLowerCase() );
            });
        }
    }

    function checkTypePersonCreditProcess () {
        /**
         *Deparments
         * 1  Cumplimiento
         * 8  Operaciones
         * 11 Legal
         **/
        
        for (var i = 0 ; i < radioTypePerson.length; i++) {
            let check = $(radioTypePerson[i]).prop('checked');

            for (var j = 0; j < dptsChild.length; j++) {
                //Disabled Checkbox, Departamentos Adjuntos Proceso de Credito - Originacion Comercial
                if(Number($(dptsChild[j]).val()) === 1 || Number($(dptsChild[j]).val()) === 8 || Number($(dptsChild[j]).val()) === 11){
                    dptsChild[j].disabled = true;
                }

                //Auto Check, segun el tipo de Cliente
                if(Number($(radioTypePerson[i]).val()) === 1 && check){
                    if(Number($(dptsChild[j]).val()) === 1 || Number($(dptsChild[j]).val()) === 8){
                        $(dptsChild[j]).prop('checked', true);
                    }
                    if(Number($(dptsChild[j]).val()) === 11){
                        $(dptsChild[j]).prop('checked', false);
                    }
                } else if(Number($(radioTypePerson[i]).val()) === 2 && check) {
                    if(Number($(dptsChild[j]).val()) === 1 || Number($(dptsChild[j]).val()) === 8 || Number($(dptsChild[j]).val()) === 11){
                        $(dptsChild[j]).prop('checked', true);
                    }
                }
            }
        }
    }

    //Funcion que habilita los check al enviar el formulario
    function enabledCheckBeforeSubmit (){
        $("form").submit(function (event) {
            event.preventDefault();
            for (var i = 0; i < dptsChild.length; i++) {
            $(dptsChild[i]).removeAttr('disabled');
            }
        });
    }

</script>
<?php
$this->endSection();
?>
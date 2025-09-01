<?php
/**
 * @var $this \CodeIgniter\View\View
 * @var $pager \CodeIgniter\Pager\Pager
 */
$this->extend('staff/template');
$this->section('content');

#Consulta el parametro del sistema para validar si muestra el boton para responder al ticket
$param = getParam('NO_REPLY');

#Convierto en array los departamentos asignados al Staff.
$myDtps = explode(',', getNamesDepAdjuntosById(serialize(staff_data('department'))));

#Para validar ticket, cuando se cierra sin agregar una repuesta.
$repliesTicket = getCountRepliesTicket($ticket->id);

echo '<input type="hidden" value="' . trim(getParamText('DEPARTMENT_ATTENTION_CLIENT')) . '" id="dpt-ac"> </input>';
echo '<input type="hidden" value="' . trim(getParamText('DEPARTMENT_LOAN_PAYMENTS')) . '" id="dpt-loan"> </input>';

//Obtiene datos del cliente que solicita un documento
$emailSolicitude = getEmailSolicitude($ticket->id);

//Se obtiene datos del préstamo
$loanPayment = getLoanPaymentByTicketId($ticket->id);
?>
    <!-- Page Header -->
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-12 text-center text-sm-left mb-0">
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.tickets.menu'); ?></span>
            <h3 class="page-title"><?php echo '[#' . $ticket->id . '] ' . esc($ticket->subject); ?></h3>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs border-bottom" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                       aria-selected="true"><?php echo lang('Admin.form.general'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="reply-tab" data-toggle="tab" href="#replyBox" role="tab"
                       aria-selected="false"><?php echo lang('Admin.form.reply'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-toggle="tab" href="#notesBox" role="tab"
                       aria-selected="false"><?php echo lang('Admin.tickets.notes'); ?></a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content mb-3" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="pb-3">
                        <div class="text-muted">
                            <i class="far fa-calendar"></i> <?php echo lang_replace('Admin.form.createdOn', ['%date%' => dateFormat($ticket->date)]); ?>
                            <i class="far fa-calendar"></i> <?php echo lang_replace('Admin.form.updatedOn', ['%date%' => dateFormat($ticket->last_update)]); ?>
                        </div>
                    </div>
                    <?php echo form_open('', [], ['do' => 'update_information']); ?>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('Admin.form.department'); ?></label>
                                <select name="department" class="form-control custom-select">
                                    <?php
                                    $depDefaultSelect = '';
                                    if (isset($departments_list)) {
                                        foreach ($departments_list as $item) {
                                            if ($item->id == $ticket->department_id) {
                                                echo '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
                                                $depDefaultSelect .= $item->name;
                                            } else {
                                                echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                    <!-- Campo oculto para almacenar el departamento seleccionado -->
                                    <input type="hidden" value="<?php echo $depDefaultSelect; ?>"
                                           id="departmentSel"> </input>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('Admin.form.status'); ?></label>
                                <select name="status" class="form-control custom-select">
                                    <?php
                                    foreach ($ticket_statuses as $k => $v) {
                                        if ($k == $ticket->status) {
                                            echo '<option value="' . $k . '" selected>' . lang('Admin.form.' . $v) . '</option>';
                                        } else {
                                            echo '<option value="' . $k . '">' . lang('Admin.form.' . $v) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('Admin.form.priority'); ?></label>
                                <select name="priority" class="form-control custom-select">
                                    <?php
                                    if (isset($ticket_priorities)) {
                                        foreach ($ticket_priorities as $item) {
                                            if ($item->id == $ticket->priority_id) {
                                                echo '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
                                            } else {
                                                echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php
                    if ($ticket->custom_vars != '') {
                        $custom_vars = unserialize($ticket->custom_vars);
                        if (is_array($custom_vars)) {
                            echo '<div class="row">';
                            foreach ($custom_vars as $item) {
                                ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label><?php echo $item['title']; ?></label>
                                        <input type="text" value="<?php echo esc($item['value']); ?>"
                                               class="form-control" readonly>
                                    </div>
                                </div>
                                <?php
                            }
                            echo '</div>';
                        }
                    }
                    ?>

                    <!-- Para leer los departamentos adjuntos ENVIOS VALIJAS -->
                    <?php
                    if ($ticket->department_id_child != "0") {
                        $namesDep = "";
                        $departments_child = unserialize($ticket->department_id_child);
                        if (is_array($departments_child) && count($departments_child) != 0) {
                            foreach ($departments_child as $dep) {
                                foreach ($departments_list as $itemDep) {
                                    if ($itemDep->id == $dep)
                                        $namesDep .= $itemDep->name . ", ";
                                }
                            }
                            $namesDep = substr($namesDep, 0, -2);
                            ?>
                            <div class="row">
                                <div class="col-md-12 col-lg-8">
                                    <div class="form-group">
                                        <label>Departamentos Adjuntos</label>
                                        <input type="text" value="<?php echo $namesDep; ?>" class="form-control"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <!-- Campo oculto para validar que el ticket tenga respuestas para cerrar. -->
                    <div class="form-group">
                        <input type="text" class="form-control" name="replies"
                               value="<?php echo $repliesTicket->replies; ?>" hidden>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary"><?php echo lang('Admin.form.save'); ?></button>
                    </div>

                    <?php echo form_close(); ?>
                </div>

                <!-- Inicio Bloque de respuesta -->
                <div class="tab-pane fade" id="replyBox" role="tabpanel" aria-labelledby="reply-tab">
                    <?php
                    echo form_open_multipart('', ['name' => 'myForm'], ['do' => 'reply']);
                    ?>

                    <!-- PROCESO ATENCION AL CLIENTE -->
                    <?php if (trim(getParamText('DEPARTMENT_ATTENTION_CLIENT')) === $ticket->department_name && $emailSolicitude != null) { ?>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2"><?php echo lang('Admin.form.to'); ?></label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control"
                                       value="<?php echo esc($ticket->fullname . ' <' . $ticket->email . '>'); ?>"
                                       readonly>
                            </div>
                        </div>
                        <div id="mailClients">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">To Client</label>
                                <div class="col-lg-5">
                                    <input type="text" class="form-control" name="destino1"
                                           value="<?php echo $emailSolicitude->name_destino1; ?>" readonly>
                                </div>
                                <div class="col-lg-5">
                                    <input type="text" name="emailCliente"
                                           value="<?php echo $emailSolicitude->email; ?>"
                                           class="form-control">
                                </div>
                            </div>
                            <?php if ($emailSolicitude->type_person === 'jur' && ($emailSolicitude->name_destino2 != "" || $emailSolicitude->email2 != "")): ?>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">To Client 2</label>
                                    <div class="col-lg-5">
                                        <input type="text" class="form-control" name="destino2"
                                               value="<?php echo $emailSolicitude->name_destino2; ?>" readonly>
                                    </div>
                                    <div class="col-lg-5">
                                        <input type="text" name="emailCliente2"
                                               value="<?php echo $emailSolicitude->email2; ?>"
                                               class="form-control">
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                        <!-- PROCESO DE PAGOS RECIBIDOS HOPE -->
                    <?php } else if (trim(getParamText('DEPARTMENT_LOAN_PAYMENTS')) === $ticket->department_name && isset($loanPayment)) { ?>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2"><?php echo lang('Admin.form.to'); ?></label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control"
                                       value="<?php echo esc($ticket->fullname . ' <' . $ticket->email . '>'); ?>"
                                       readonly>
                            </div>
                        </div>
                        <div id="mailClientLoan">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">To Client</label>
                                <div class="col-lg-5">
                                    <input type="text" class="form-control" name="clientNameLoan"
                                           value="<?php echo $loanPayment->client_name; ?>" readonly>
                                </div>
                                <div class="col-lg-5">
                                    <input type="text" name="emailLoan"
                                           value="<?php echo $loanPayment->email; ?>"
                                           class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- OTROS PROCESOS -->
                    <?php } else { ?>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2"><?php echo lang('Admin.form.to'); ?></label>
                            <div class="col">
                                <input type="text" class="form-control"
                                       value="<?php echo esc($ticket->fullname . ' <' . $ticket->email . '>'); ?>"
                                       readonly>
                            </div>
                        </div>

                    <?php } ?>

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2"><?php echo lang('Admin.form.quickInsert'); ?></label>
                        <div class="col">
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="canned" id="cannedList" onchange="addCannedResponse(this.value);"
                                            class="custom-select">
                                        <option value=""><?php echo lang('Admin.cannedResponses.menu'); ?></option>
                                        <?php
                                        if (isset($canned_response)) {
                                            foreach ($canned_response as $item) {
                                                echo '<option value="' . $item->id . '">' . $item->title . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select name="knowledgebase" id="knowledgebaseList"
                                            onchange="addKnowledgebase(this.value);" class="custom-select">
                                        <option value=""><?php echo lang('Admin.kb.menu'); ?></option>
                                        <?php
                                        echo $kb_selector;
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" name="message" id="messageBox"
                                  rows="20"><?php echo set_value('message'); ?></textarea>
                    </div>

                    <!--==============================================================================
                    =            Check para agregar adjuntos. Proceso Atención al cliente            =
                    ===============================================================================-->
                    <?php
                        $nameDptAttentionClient = trim(getParamText('DEPARTMENT_ATTENTION_CLIENT'));
                        $nameDptLoanPayment = trim(getParamText('DEPARTMENT_LOAN_PAYMENTS'));
                        $departmentName = $ticket->department_name;
                    ?>
                    <?php if ($nameDptAttentionClient === $departmentName || $nameDptLoanPayment === $departmentName): ?>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="isAttached" class="custom-control-input"
                                       id="select_attachment_file">
                                <label class="custom-control-label" for="select_attachment_file">
                                    Responder al cliente
                                </label>
                            </div>
                        </div>
                    <?php endif ?>
                    <!--====  End of Check para agregar adjuntos. Proceso Atención al cliente  ====-->

                    <!-- Se carga la parametrizacion del adjunto de archivos por departamento -->
                    <?php
                    $configAttachmentDep = getConfigDepartmentById($ticket->department_id, 'executive');

                    if ($configAttachmentDep != null && $configAttachmentDep->ticket_attachment) { ?>
                        <div class="form-group" id="adjuntar_archivos">
                            <label><?php echo lang('Admin.form.attachments'); ?></label>
                            <?php
                            for ($i = 1; $i <= $configAttachmentDep->ticket_attachment_number; $i++) {
                                ?>
                                <div class="row">
                                    <div class="col-lg-6 mb-2">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="attachment[]"
                                                   id="customFile<?php echo $i; ?>">
                                            <label class="custom-file-label" for="customFile<?php echo $i; ?>"
                                                   data-browse="<?php echo lang('Admin.form.browse'); ?>"><?php echo lang('Admin.form.chooseFile'); ?></label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <small class="text-muted"><?php echo lang('Admin.form.allowedFiles') . ' *.' . implode(', *.', unserialize($configAttachmentDep->ticket_file_type)) . '. Tamaño máximo del archivo: ' . $configAttachmentDep->ticket_file_size . ' MB'; ?></small>
                        </div>
                    <?php } ?>

                    <!-- Verifico para mostrar o no el boton Responder -->
                    <?php
                    $flagButton = false;
                    foreach ($myDtps as $dep) {
                        if (trim($dep) === $ticket->department_name && $param->param_text === $ticket->department_name) {
                            $flagButton = true;
                            break;
                        }
                    }
                    ?>

                    <?php if (trim(getParamText('ONE_WAY_TICKET')) != $ticket->department_name) { ?>
                        <div class="form-group">
                            <button class="btn btn-primary"><i class="fa fa-paper-plane"></i>
                                <?php echo lang('Admin.form.submit'); ?>
                            </button>
                        </div>
                    <?php } ?>

                    <?php echo form_close(); ?>
                </div>
                <!-- Fin Bloque de respuesta -->

                <div class="tab-pane fade" id="notesBox" role="tabpanel" aria-labelledby="notes-tab">
                    <?php
                    if (isset($notes)) {
                        foreach ($notes as $note) {
                            ?>
                            <div class="alert alert-light border mb-3">
                                <div class="alert-heading">
                                    by <?php echo $note->fullname; ?>
                                    <small>&raquo; <?php echo dateFormat($note->date); ?></small>
                                    <?php
                                    if (staff_data('admin') == 1 || staff_data('id') == $note->staff_id) {
                                        ?>
                                        <div class="float-right">
                                            <?php echo form_open('', ['id' => 'noteForm' . $item->id], ['do' => 'delete_note', 'note_id' => $note->id]); ?>
                                            <button type="button" onclick="editNoteToggle('<?php echo $note->id; ?>');"
                                                    class="btn btn-link" title="Edit note" data-toggle="tooltip"><i
                                                        class="fa fa-edit"></i></button>
                                            <button type="button"
                                                    onclick="deleteNote('noteForm<?php echo $item->id; ?>');"
                                                    class="btn btn-link" title="Delete note" data-toggle="tooltip"><i
                                                        class="fa fa-trash-alt"></i></button>
                                            <?php echo form_close(); ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <hr>
                                </div>
                                <div id="plainNote<?php echo $note->id; ?>">
                                    <p><?php echo nl2br($note->message); ?></p>
                                </div>
                                <div id="inputNote<?php echo $note->id; ?>" style="display: none">
                                    <?php echo form_open('', ['id' => 'editNoteForm' . $item->id], ['do' => 'edit_note', 'note_id' => $note->id]); ?>
                                    <div class="form-group">
                                        <textarea class="form-control" name="new_note">
                                            <?php echo set_value('new_note', $note->message, false); ?>
                                        </textarea>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-primary"><?php echo lang('Admin.form.save'); ?></button>
                                        <button type="button" onclick="editNoteToggle(<?php echo $note->id; ?>);"
                                                class="btn btn-dark"><?php echo lang('Admin.form.cancel'); ?></button>
                                    </div>
                                    <?php
                                    echo form_close();
                                    ?>
                                </div>

                            </div>
                            <?php
                        }
                    }
                    ?>
                    <?php
                    echo form_open_multipart('', [], ['do' => 'save_notes']);
                    ?>
                    <div class="form-group">
                        <textarea class="form-control" name="noteBook"><?php echo set_value('noteBook'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary"><i
                                    class="fa fa-edit"></i> <?php echo lang('Admin.tickets.addNote'); ?></button>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <!--========================================================================================
    =            Resumen del email enviado al cliente - Proceso atencion al cliente            =
    =========================================================================================-->
<?php if (trim(getParamText('DEPARTMENT_ATTENTION_CLIENT')) === $ticket->department_name):
    $client_solicitude = getEmailSolicitude($ticket->id);
    $close_ticket = getStatusTicketSolicitude($ticket->id);
    $status = (int)$close_ticket->status;
    ?>

    <?php if ($client_solicitude != null && (int)$client_solicitude->send_email === 1 && $status === 5) { ?>
    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col d-none d-sm-block">
                    <h6 class="m-0 text-uppercase">Resumen del envío de documentos al cliente</h6>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nombre Destinatario Final 1</label>
                        <input type="text" class="form-control" value="<?php echo $client_solicitude->name_destino1; ?>"
                               readonly>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="text" class="form-control" value="<?php echo $client_solicitude->email; ?>"
                               readonly>
                    </div>
                </div>
                <?php if ($client_solicitude->type_person === 'jur' && ($client_solicitude->name_destino2 != "" || $client_solicitude->email2 != "")): ?>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Nombre Destinatario Final 2</label>
                            <input type="text" class="form-control"
                                   value="<?php echo $client_solicitude->name_destino2; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Correo electrónico 2</label>
                            <input type="text" class="form-control" value="<?php echo $client_solicitude->email2; ?>"
                                   readonly>
                        </div>
                    </div>
                <?php endif ?>
            </div>
            <?php
            if ($files = ticket_files($ticket->id, $client_solicitude->msg_id)) {
                ?>
                <div class="alert alert-info">
                    <p class="font-weight-bold"><?php echo lang('Admin.form.attachments'); ?></p>
                    <?php foreach ($files as $file): ?>
                        <div class="form-group">
                            <span class="knowledgebaseattachmenticon"></span>
                            <i class="far fa-file-archive"></i> <a
                                    href="<?php echo current_url() . '?download=' . $file->id; ?>"
                                    target="_blank"><?php echo $file->name; ?></a>

                            <?php echo number_to_size($file->filesize, 2); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
            }
            ?>
            <div class="border-top mt-3 pt-4 text-right">
                <?php
                $staffName = staff_info($close_ticket->last_replier);
                echo '<i class="fas fa-user"></i> ' . $staffName['fullname'];
                ?>
            </div>
        </div>
    </div>

<?php } elseif ($client_solicitude === null) { ?>
    <div class="alert alert-danger">
        No se encontro la información del cliente.
    </div>
<?php } else { ?>
    <?php if ((int)$client_solicitude->msg_id != 0 && $status === 5): ?>
        <div class="alert alert-warning">
            El correo electrónico no fue enviado al cliente.
        </div>
    <?php endif ?>

<?php } ?>

<?php endif ?>
    <!--====  End of Resumen del email enviado al cliente - Proceso atencion al cliente  ====-->

    <!--============================================
    =            Se crea Pojo de Valija            =
    =============================================-->
<?php

class Valija
{
    public $asesor;
    public $destinatario;
    public $cliente;
    public $documento;
    public $cantidad;
}

?>
    <!--====  End of Se crea Pojo de Valija  ====-->

<?php

if (isset($error_msg)) {
    echo '<div class="alert alert-danger">' . $error_msg . '</div>';
}
if (isset($success_msg)) {
    echo '<div class="alert alert-success">' . $success_msg . '</div>';
}
if (isset($message_result)) {
    $minId = min(array_column($message_result, 'id'));
    foreach ($message_result as $item) {
        $isFirst = $item->id == $minId; //Para identificar el primer elemento del array
        ?>
        <div class="card mb-3 <?php echo($item->customer == 1 ? '' : 'bg-staff'); ?>">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-2 col-lg-3">
                        <?php
                        if ($item->customer == 1) {
                            ?>
                            <div class="text-center">
                                <div class="mb-3">
                                    <img src="<?php echo user_avatar($ticket->avatar); ?>"
                                         class="user-avatar rounded-circle img-fluid" style="max-width: 100px">
                                </div>
                                <?php
                                #dataUser
                                $dataUser = findStaffByEmail($ticket->email);
                                ?>
                                <div class="mb-3">
                                    <div><?php echo $ticket->fullname; ?></div>
                                    <div style="font-size: 11px; color: #007b9d;">
                                        <?php echo getNamesDepAdjuntosById($dataUser->department); ?>
                                    </div>
                                    <?php
                                    echo '<span class="badge badge-dark">' . lang('Admin.form.user') . '</span>';
                                    ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            $staffData = staff_info($item->staff_id);
                            $dataStaff = findStaffByEmail($staffData['email']);
                            ?>
                            <div class="text-center">
                                <div class="mb-3">
                                    <img src="<?php echo $staffData['avatar']; ?>"
                                         class="user-avatar rounded-circle img-fluid" style="max-width: 100px">
                                </div>
                                <div class="mb-3">
                                    <div><?php echo $staffData['fullname']; ?></div>
                                    <div style="font-size: 11px; color: #007b9d;">
                                        <?php echo getNamesDepAdjuntosById($dataStaff->department); ?>
                                    </div>
                                    <?php
                                    echo '<span class="badge badge-primary">' . lang('Admin.form.staff') . '</span>';
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <!-- Seccion para cargar los mensajes de los tickets -->
                    <div class="col">
                        <div class="mb-3">
                            <div class="text-muted"><i
                                        class="fa fa-calendar"></i> <?php echo dateFormat($item->date); ?>

                            </div>
                        </div>
                        <!--===================================================
                        =                     View form Valijas               =
                        ====================================================-->
                        <?php if ($ticket->department_name == "Valijas" && $item->customer == 1) { ?>
                            <?php
                            include __DIR__ . '/valija_view.php';
                            ?>
                            <!--====  End of View form Valijas  ====-->

                            <!--===================================================
                            =            View form Atención al cliente            =
                            ====================================================-->
                        <?php } elseif (trim(getParamText('DEPARTMENT_ATTENTION_CLIENT')) === $ticket->department_name) { ?>
                            <?php
                            $solicitudeClient = unserialize($item->message);
                            //$solicitudeClient = getEmailSolicitude($item->ticket_id);
                            if (is_array($solicitudeClient)) {
                                include __DIR__ . '/solicitude_view.php';
                            } else {
                                echo '<div id="msg_' . $item->id . '" class="form-group">';
                                echo($item->email == 1 ? $item->message : $item->message);
                                echo '</div>';
                            }
                            ?>
                            <!--====  End of View form Atención al cliente  ====-->

                            <!--===================================================
                            =            View form Pagos recibidos                =
                            ====================================================-->
                        <?php } elseif (trim(getParamText('DEPARTMENT_LOAN_PAYMENTS')) === $ticket->department_name) {
                            if (isset($loanPayment) && is_object($loanPayment) && $isFirst) {
                                include __DIR__ . '/loan_payments_view.php';
                            } else {
                                echo '<div id="msg_' . $item->id . '" class="form-group">';
                                echo nl2br($item->message);
                                echo '</div>';
                            }
                            ?>
                            <!--====  End of View form Pagos recibidos  ====-->

                            <!--==========================================================================
                            =            Para mostrar mensajes del proceso Crédito-Desembolso            =
                            ===========================================================================-->
                        <?php } elseif (trim(getParamText('ONE_WAY_TICKET')) === $ticket->department_name) { ?>
                            <?php
                            $arrayInfoCD = unserialize($item->message);
                            foreach ($arrayInfoCD as $info) {
                                echo nl2br($info['message']);
                            }
                            ?>
                            <!--====  End of Para mostrar mensajes del proceso Crédito-Desembolso  ====-->

                            <!--=================================================================
                            =            Para mostrar mensajes de los demas procesos            =
                            ==================================================================-->
                        <?php } else { ?>
                            <div id="msg_<?php echo $item->id; ?>" class="form-group">
                                <?php
                                if ($ticket->department_name == "Valijas") {
                                    echo($item->email == 1 ? $item->message : $item->message);
                                } else {
                                    echo($item->email == 1 ? $item->message : nl2br($item->message));
                                }
                                ?>
                            </div>
                        <?php } ?>
                        <!--====  End of Para mostrar mensajes de los demas procesos  ====-->

                        <?php
                        if ($files = ticket_files($ticket->id, $item->id)) {
                            ?>
                            <div class="alert alert-info">
                                <p class="font-weight-bold"><?php echo lang('Admin.form.attachments'); ?></p>
                                <?php foreach ($files as $file): ?>
                                    <div class="form-group">
                                        <span class="knowledgebaseattachmenticon"></span>
                                        <i class="far fa-file-archive"></i> <a
                                                href="<?php echo current_url() . '?download=' . $file->id; ?>"
                                                target="_blank"><?php echo $file->name; ?></a>

                                        <?php echo number_to_size($file->filesize, 2); ?>
                                        <a href="<?php echo current_url() . '?delete_file=' . $file->id; ?>"
                                           class="btn btn-danger btn-sm"><i
                                                    class="fa fa-trash"></i> <?php echo lang('Admin.form.delete'); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php
                        }
                        ?>

                        <?php if (trim(getParamText('ONE_WAY_TICKET')) != $ticket->department_name) { ?>
                            <div class="form-group mt-5 btn_response">
                                <button class="btn btn-dark btn-sm" type="button"
                                        onclick="quoteMessage(<?php echo $item->id; ?>);"><i
                                            class="fa fa-quote-left"></i> <?php echo lang('Admin.form.quote'); ?>
                                </button>
                            </div>
                        <?php } ?>

                        <div class="border-top mt-3 pt-4 text-right">
                            <?php
                            if ($item->ip != '') {
                                echo '<i class="fa fa-globe"></i> ' . $item->ip;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    echo $pager->links();
}

$this->endSection();
$this->section('script_block');
include __DIR__ . '/tinymce.php';
?>
    <script>
        $(document).ready(function () {
            bsCustomFileInput.init();

            //Animación de scroll, cuando se de click en RESPONDER, el scroll se ubicara al inicio de la pagina.
            let buttonsUp = document.querySelectorAll('div.btn_response > button');

            for (var i = 0; i < buttonsUp.length; i++) {
                $(buttonsUp[i]).click(function () {
                    $('html, body').animate({scrollTop: 0}, '3000');
                });
            }

            viewAjuntarArchivos();

            /* Evento change al check Adjuntar Documentos - Proceso Atención al Cliente */
            $('#select_attachment_file').on('change', function () {
                var valueCheck = $('#select_attachment_file:checked').length;
                if (valueCheck === 1) {
                    $('#adjuntar_archivos').show();
                } else {
                    $('#adjuntar_archivos').hide();
                }
            });

        });

        <?php
        if (isset($canned_response)) {
            echo 'var canned_response = ' . json_encode($canned_response) . ';';
        }
        ?>
        var KBUrl = '<?php echo site_url(route_to('staff_ajax_kb'));?>';

        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        })

        /* Funcion para mostrar u ocultar el bloque de Adjuntar archivos al Cargar la página */
        function viewAjuntarArchivos() {
            let paramAtencionCliente = $('#dpt-ac').val();
            let paramLoanPayments = $('#dpt-loan').val();
            let department = $('#departmentSel').val();

            if (paramAtencionCliente === department || paramLoanPayments === department) {
                $('#adjuntar_archivos').hide();
            } else {
                $('#adjuntar_archivos').show();
            }
        }

        function deleteNote(noteFormId) {
            Swal.fire({

                text: langNoteConfirmation,
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: langDelete,
                cancelButtonText: langCancel,
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.value) {
                    $('#' + noteFormId).submit();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    return false;
                }
            });
        }

        function editNoteToggle(noteId) {
            $('#plainNote' + noteId).toggle();
            $('#inputNote' + noteId).toggle();
        }
    </script>
<?php
$this->endSection();

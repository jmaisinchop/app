<?php
/**
 * @var $this \CodeIgniter\View\View
 * @var $pager \CodeIgniter\Pager\Pager
 */
$this->extend('client/template');
$this->section('window_title');
echo lang_replace('Client.viewTickets.ticketID',['%id%' => $ticket->id]);
$this->endSection();
$this->section('script_block');
?>
    <script type="text/javascript" src="<?php echo base_url('assets/components/bs-custom-file-input/bs-custom-file-input-min.js');?>"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js">"</script>
    
    <script>

        //Variables para validar descarga de archivos
        let attachments = document.querySelectorAll('div.attachment-file > a');
        let department = $('#department').val();
        let dptAC = $('#dpt-ac').val();
        
        $(function(){
            $(document).ready(function () {
                bsCustomFileInput.init();
            });

            //Se deshabilita descarga de archivos para ATENCION AL CLIENTE
            /*if(dptAC === department){ 
                disabledAttachments();
                for (var i = 0; i < attachments.length; i++) {
                $(attachments[i]).on('click', function (e) {
                    e.preventDefault();
                });
                }
            }*/

            $(this).keyup(function(e){
                if(e.keyCode == 44 || e.keyCode == 137 ||e.KeyCode == 93 )
                {
                copyToClipboard();
                return false;
                }
            })

            /**
             *
             * Para deshabilitar el click derecho
             *
             */
            
            var msg = "¡El botón derecho está desactivado para este sitio !";
            function disableIE() {
                if (document.all) { alert(msg); return false; }
            }

            function disableNS(e) {
                if (document.layers || (document.getElementById && !document.all)) {
                    if (e.which == 2 || e.which == 3) {
                        Swal.fire({
                            type: 'warning',
                            //title: 'No permitido',
                            text: msg
                        });
                        return false; 
                    }
                }
            }

            if (document.layers) {
                document.captureEvents(Event.MOUSEDOWN); document.onmousedown = disableNS;
            } else {
                document.onmouseup = disableNS; document.oncontextmenu = disableIE;
            }

            document.oncontextmenu = ev =>{
              ev.preventDefault();
              console.log("Prevented to open menu!");
            }

            /**
             *
             * Recargo la pagina para cargar nuevo canvas con el PDF
             * Evento click en los botones del modal que carga el PDF
             */
            
            $('#btnCloseHeader').click( function () {
                location.reload();
            });
            
            $('#btnClose').click( function () {
                location.reload();
            });


            //disable Ctrl + keys
            document.onkeydown = function (e) {
                e = e || window.event;//Get event
                if (e.ctrlKey) {
                    var c = e.which || e.keyCode;//Get key code
                    console.log(c);
                    switch (c) {
                        case 83://Block Ctrl+S
                        case 80 : //block Ctrl+P
                        case 17 : //block Ctrl
                        case 16 : //block Shift
                            e.preventDefault();     
                            e.stopPropagation();
                            Swal.fire({
                                type: 'warning',
                                text: 'Atajo de teclado deshabilitado'
                            });
                        break;
                    }
                }
            };

        })

        function copyToClipboard() {
            var aux = document.createElement("input");
            aux.setAttribute("value", "Function Disabled.....");
            document.body.appendChild(aux);
            aux.select();
            document.execCommand("copy");
            document.body.removeChild(aux);
            Swal.fire({
                type: 'warning',
                text: 'Print is not allowed'
            });
        }

        /**
        *
        * Funciones para abrir el PDF en el modal
        * Más funciones de Zoom y Cambio de página.
        */

        //View PDF with PDF.js
        var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1
        }

        //Función que abre el modal con el PDF
        function openModalPDF(url){
           //$('#frameviewPDF').attr('src',url+'#toolbar=0&navpanes=0&scrollbar=0');
           //http://10.5.1.28/tickets/show/58?view=148

            //Reseteo el numero de pagina al cargar un nuevo documento.
            myState.currentPage = 1;
            document.getElementById("current_page").value = myState.currentPage;

            pdfjsLib.getDocument(url).then((pdf) => {
                myState.pdf = pdf;

                renderPDF();    
            });

            nextPreviewPDF();
            zoomInOutPDF ();
        }

        function zoomInOutPDF (){
            document.getElementById('zoom_in')
                .addEventListener('click', (e) => {
                    if(myState.pdf == null) return;
                    myState.zoom += 0.5;

                    //$("#canvas_container").html(""); //Elimina el canvas
                    //$("#canvas_container").html("<canvas id='pdf_renderer'></canvas>"); //Crea un nuevo canvas
                    renderPDF();
                });

            document.getElementById('zoom_out')
                .addEventListener('click', (e) => {
                    if(myState.pdf == null) return;
                    myState.zoom -= 0.5;

                    renderPDF();
                });
        }

        function nextPreviewPDF (){
            document.getElementById('go_previous')
                .addEventListener('click', (e) => {
                    if(myState.pdf == null
                       || myState.currentPage == 1) return;
                    myState.currentPage -= 1;
                    document.getElementById("current_page").value = myState.currentPage;
                    renderPDF();
                });

            document.getElementById('go_next')
                .addEventListener('click', (e) => {
                    if(myState.pdf == null 
                       || myState.currentPage > myState.pdf._pdfInfo.numPages) 
                       return;
                
                    myState.currentPage += 1;
                    document.getElementById("current_page").value = myState.currentPage;
                    
                    renderPDF();
                });

            document.getElementById('current_page')
                .addEventListener('keypress', (e) => {
                    if(myState.pdf == null) return;
                
                    // Get key code
                    var code = (e.keyCode ? e.keyCode : e.which);
                
                    // If key code matches that of the Enter key
                    if(code == 13) {
                        var desiredPage = 
                                document.getElementById('current_page')
                                        .valueAsNumber;
                                        
                        if(desiredPage >= 1 
                           && desiredPage <= myState.pdf
                                                    ._pdfInfo.numPages) {
                                myState.currentPage = desiredPage;
                                document.getElementById("current_page")
                                        .value = desiredPage;
                                renderPDF();
                        }
                    }
                });
        }

        //Función que renderiza el PDF en el modal
        function renderPDF (){
            myState.pdf.getPage(myState.currentPage).then((page) => {

            var canvas = document.getElementById("pdf_renderer");
            var ctx = canvas.getContext('2d');

            var viewport = page.getViewport(myState.zoom);

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            //Limpiamos el cambas anterior
            if(ctx) {
              ctx.clearRect(0, 0, canvas.width, canvas.height); 
            }
            
            page.render({
                canvasContext: ctx,
                viewport: viewport
            });

            });
        }

        function disabledAttachments (){            
            for (var i = 0; i < attachments.length; i++) {
                attachments[i].style.cursor = "default";  
                attachments[i].style.color = "gray";
            }
        }


    </script>
<?php
$this->endSection();
$this->section('content');
?>

<!--============================================
=            Se crea Pojo de Valija            =
=============================================-->
<?php 
    class Valija {
        public $asesor;
        public $destinatario;
        public $cliente;
        public $documento;
        public $cantidad;
    }
?>
<!--====  End of Se crea Pojo de Valija  ====-->

    <div class="container mt-5">
        <h1 class="heading mb-5">
            <?php echo lang('Client.viewTickets.title');?>
        </h1>

        <div class="card mb-3">
            <div class="card-body">
                <h4 class="card-title">[#<?php echo $ticket->id;?>] <?php echo esc($ticket->subject);?></h4>
                <div class="text-muted">
                    <i class="fa fa-calendar"></i> <?php echo dateFormat($ticket->date);?> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                    <i class="fa fa-calendar"></i> <?php echo dateFormat($ticket->last_update);?>
                </div>
                <div class="row mt-3">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('Client.form.department');?></label>
                            <input type="text" value="<?php echo $ticket->department_name;?>" class="form-control" id="department" readonly >
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('Client.form.status');?></label>
                            <input type="text" value="<?php echo $ticket_status;?>" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('Client.form.priority');?></label>
                            <input type="text" value="<?php echo $ticket->priority_name;?>" class="form-control" readonly>
                        </div>
                    </div>
                    <?php if(getNamesDepAdjuntosById($ticket->department_id_child) !=""){ ?>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label><?php echo lang('Client.form.dptsAdjuntoList');?></label>
                            <input type="text" value="<?php echo getNamesDepAdjuntosById($ticket->department_id_child);?>" class="form-control" readonly>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- End ticket info -->
        <?php
        if(isset($error_msg)){
            echo '<div class="alert alert-danger">'.$error_msg.'</div>';
        }
        if($ticket->status == 5){
            echo '<div class="alert alert-info">'.lang('Client.viewTickets.ticketClosed').'</div>';
        }
        ?>

        <!-- Obtengo el estatus del ticket -->
        <?php $close_ticket = getStatusTicketSolicitude($ticket->id); 
            $flag_status_ticket = false;
            if($close_ticket != null && (int)$close_ticket->status === 5 ){
                $flag_status_ticket = true;
            }
        ?>

        <div class="mb-3">
            <div id="replyButtons" <?php echo !isset($error_msg) ? '' : 'style="display:none;"';?>>
                <?php if (!$flag_status_ticket): ?>
                  <button class="btn btn-primary" type="button" onclick="$('#replyButtons').hide(); $('#replyForm').show();" 
                    <?php echo $flag_status_ticket === true ? 'disabled' : '';?>>
                    <i class="fa fa-edit"></i> <?php echo lang('Client.form.addReply');?>
                </button>    
                <?php endif ?>
                
                <a href="<?php echo site_url(route_to('view_tickets'));?>" class="btn btn-secondary"><?php echo lang('Client.form.goBack');?></a>
            </div>

            <?php
            echo form_open_multipart('',['id'=>'replyForm','style'=>(!isset($error_msg) ? 'display:none;' : ''), 
                'name' => 'myForm'],['do' => 'reply']);
            ?>
            <div class="form-group">
                <label><?php echo lang('Client.form.yourMessage');?></label>
                <textarea class="form-control" name="message" rows="5"><?php echo set_value('message');?></textarea>
            </div>
            <?php
            $configAttachmentDep = getConfigDepartmentById($ticket->department_id, 'executive');
            if($configAttachmentDep != null && $configAttachmentDep->ticket_attachment){
                ?>
                <div class="form-group">
                    <label><?php echo lang('Client.form.attachments');?></label>
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
            ?>

            <div class="form-group">
                <button class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?php echo lang('Client.form.submit');?></button>
                <a href="<?php echo site_url(route_to('view_tickets'));?>" class="btn btn-secondary" id="btnBack"><?php echo lang('Client.form.goBack');?></a>
            </div>
            <?php
            echo form_close();
            ?>
        </div>
        <?php
        if(\Config\Services::session()->has('form_success')){
            echo '<div class="alert alert-success">'.\Config\Services::session()->getFlashdata('form_success').'</div>';
        }
        ?>
        <?php
        if($pager->getPageCount() > 1){
            echo $pager->links();
        }
        ?>

        <?php if(isset($result_data)):?>
            <?php 
                //Se obtiene datos del prestamo
                $loanPayment = getLoanPaymentByTicketId($ticket->id);
                $minId = min(array_column($result_data, 'id'));   
                 
            foreach ($result_data as $item):
                $isFirst = $item->id == $minId; //Para identificar el primer elemento del array
            ?>
                <div class="card mb-3 <?php echo ($item->customer == 1 ? '' : 'bg-staff');?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-2 col-lg-3">
                                <?php
                                if($item->customer == 1){
                                    ?>
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <img src="<?php echo user_avatar($ticket->avatar);?>" class="user-avatar rounded-circle img-fluid" style="max-width: 100px">
                                        </div>
                                        <div class="mb-3">
                                            <div><?php echo $ticket->fullname;?></div>
                                            <?php
                                            echo '<span class="badge badge-dark">'.lang('Client.form.user').'</span>';
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }else{
                                    $staffData = staff_info($item->staff_id);
                                    ?>
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <img src="<?php echo $staffData['avatar'];?>" class="user-avatar rounded-circle img-fluid" style="max-width: 100px">
                                        </div>
                                        <div class="mb-3">
                                            <div><?php echo $staffData['fullname'];?></div>
                                            <?php
                                            echo '<span class="badge badge-primary">'.lang('Client.form.staff').'</span>';
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <small class="text-muted"><i class="fa fa-calendar"></i> <?php echo dateFormat($item->date);?></small>
                                </div>
                                
                                <!--==============================================================================
                                =            Se obtiene parametro para el Proceso Atencion al Cliente            =
                                ===============================================================================-->
                                    <?php 
                                    //Obtiene parametro del Proceso Atencion al Cliente.
                                    $paramAT = getParam('DEPARTMENT_ATTENTION_CLIENT');   
                                    echo '<input type="hidden" value="'.$paramAT->param_text.'" id="dpt-ac"> </input>';                        
                                    ?>
                                <!--====  End of Se obtiene parametro para el Proceso Atencion al Cliente  ====-->
                                

                                <!-- Proceso de Valijas -->                   
                                <?php if ($ticket->department_name == "Valijas" && $item->customer == 1) { ?>
                                    
                                    <?php include __DIR__.'/valija_view.php' ?>

                                <!-- Proceso Atencion al cliente -->
                                <?php } elseif(trim(getParamText('DEPARTMENT_ATTENTION_CLIENT')) === $ticket->department_name) { ?>
                                    <?php 
                                        //Parametro que define el id del ticket y el id del mensaje
                                        $id_ticket = $item->ticket_id;
                                        $id_message = $item->id;
                                        $solicitude = unserialize($item->message);
                                        if(is_array($solicitude)){
                                            include_once ('solicitude_view.php');
                                        } else {
                                            echo '<div id="msg_'.$item->id.'" class="form-group">';
                                            echo ($item->email == 1 ? $item->message : $item->message);
                                            echo '</div>';
                                        }
                                     ?>

                                <!-- Proceso Pagos Recibidos Préstamo -->
                                <?php } elseif(trim(getParamText('DEPARTMENT_LOAN_PAYMENTS')) === $ticket->department_name) { ?>
                                    <?php 
                                        if(isset($loanPayment) && is_object($loanPayment) && $isFirst){
                                            include __DIR__.'/loan_payments_view.php';
                                        } else {
                                            echo '<div id="msg_'.$item->id.'" class="form-group">';
                                            echo nl2br($item->message);
                                            echo '</div>';
                                        }
                                     ?>

                                <!-- Para mostrar mensajes del proceso Crédito-Desembolso -->
                                <?php } elseif (trim(getParamText('ONE_WAY_TICKET')) === $ticket->department_name) { ?>
                                 
                                    <?php 
                                        $arrayInfoCD = unserialize($item->message);
                                        foreach($arrayInfoCD as $info){
                                            echo nl2br($info['message']);
                                        }
                                     ?>

                                <!-- Para mostrar mensajes de los demas departamentos -->       
                                <?php } else { ?>
                                    <div id="msg_<?php echo $item->id;?>" class="form-group">
                                        <?php 
                                            if($ticket->department_name == "Valijas"){
                                                echo ($item->email == 1 ? $item->message : $item->message);
                                            } else {
                                                echo ($item->email == 1 ? $item->message : nl2br($item->message));
                                            }
                                         ?> 
                                    </div>
                                <?php } ?>
                                
                                <?php
                                if($files = ticket_files($ticket->id, $item->id)){
                                    $typeGet = $paramAT->param_text === $ticket->department_name ? 'view' : 'download';
                                    ?>
                                    <div class="alert alert-info ">
                                        <p class="font-weight-bold"><?php echo lang('Client.form.attachments');?></p>
                                        <?php foreach ($files as $file):?>
                                            <div class="form-group attachment-file">
                                                <span class="knowledgebaseattachmenticon"></span>
                                                <i class="far fa-file-archive"></i> 
                                                    <?php if (trim($paramAT->param_text) === $ticket->department_name): ?>
                                                        <a 
                                                            href="#"
                                                            onclick ="openModalPDF('<?php echo current_url().'?view='.$file->id;?>')"
                                                            data-toggle="modal" 
                                                            data-target="#modalViewPDF">
                                                            <?php echo $file->name;?>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo current_url().'?download='.$file->id;?>" 
                                                            target="_blank">
                                                            <?php echo $file->name;?>
                                                        </a>  
                                                    <?php endif ?>
                                                <?php echo number_to_size($file->filesize,2);?>
                                            </div>
                                        <?php endforeach;?>
                                    </div>
                                    <?php
                                }
                                ?>
                                
                                <!-- Modal -->
                                <div class="modal fade" id="modalViewPDF" data-backdrop="static" 
                                    data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title" id="staticBackdropLabel">View PDF</h5>
                                        <button type="button" id="btnCloseHeader" class="close" 
                                            data-dismiss="modal" 
                                            aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                        </button>
                                      </div>
                                      <div class="modal-body">
                                        <!--<div style="position: relative; width: 100%;">
                                            <div style="width: 100%; background: #000; height: 35px; position: absolute;"></div>
                                          <iframe id="frameviewPDF" frameborder="0" width="100%" height="500"
                                            scrolling="no"></iframe>  
                                        </div>-->
                    
                                        <div id="my_pdf_viewer">
                                            <div id="canvas_container" 
                                                style=" width: 100% !important;
                                                    height: 450px !important;
                                                    overflow: auto !important;
                                                    background: #333 !important;
                                                    text-align: center !important;
                                                    border: solid 3px !important;
                                            ">
                                                <canvas id="pdf_renderer"></canvas>
                                            </div>
                                        </div>

                                        <div class="form-inline mt-3">
                                            <div id="navigation_controls" class="form-group">
                                                <button class="btn btn-primary btn-sm" id="go_previous">
                                                    Previous
                                                </button>
                                                <input class="form-control form-control-sm ml-2" 
                                                    id="current_page" value="1" type="number"/> 
                                                <button class="btn btn-primary btn-sm ml-2" id="go_next">
                                                    Next
                                                </button>
                                            </div>
                                            <div id="zoom_controls" class="form-group ml-5">
                                                <button class="btn btn-secondary btn-sm" id="zoom_in">    +
                                                </button>
                                                <button class="btn btn-secondary btn-sm ml-2" id="zoom_out">
                                                    -
                                                </button>
                                            </div> 
                                        </div>                      
                                                                            
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" id="btnClose" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>


                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach;?>
        <?php endif;?>
        <?php
        if($pager->getPageCount() > 1){
            echo $pager->links();
        }
        ?>
    </div>


<?php
$this->endSection();
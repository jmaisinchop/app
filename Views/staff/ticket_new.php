<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>
<div class="page-header row no-gutters py-4">
    <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
        <span class="text-uppercase page-subtitle">HelpDeskZ</span>
        <h3 class="page-title"><?php echo lang('Admin.tickets.newTicket'); ?></h3>
    </div>
</div>
<?php if (isset($error_msg)) : ?>
    <div class="alert alert-danger"><?= $error_msg ?></div>
<?php endif; ?>
<?php if (isset($success_msg)) : ?>
    <div class="alert alert-success"><?= $success_msg ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header border-bottom">
        <h6 class="mb-0"><?php echo lang('Admin.tickets.submitNewTicket'); ?></h6>
    </div>
    <div class="card-body">
        <?= form_open_multipart('', [], ['do' => 'submit']) ?>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><?php echo lang('Admin.form.email'); ?></label>
                    <input type="email" name="email" class="form-control" value="<?= set_value('email'); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><?php echo lang('Admin.form.fullName'); ?></label>
                    <input type="text" name="fullname" class="form-control" value="<?= set_value('fullname'); ?>">
                    <small class="text-muted form-text"><?php echo lang('Admin.tickets.fullName'); ?></small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label><?php echo lang('Admin.form.department'); ?></label>
                    <select name="department" id="parent-department" class="form-control custom-select">
                        <option value="">-- Select --</option>
                        <?php if (isset($departments_list)) : ?>
                            <?php foreach ($departments_list as $item) : ?>
                                <?php if ($item->id_padre == 0) : ?>
                                    <option value="<?= $item->id ?>" <?= ($item->id == set_value('department')) ? 'selected' : '' ?>>
                                        <?= $item->name ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label><?php echo lang('Admin.form.priority'); ?></label>
                    <select name="priority" class="form-control custom-select">
                        <?php if (isset($ticket_priorities)) : ?>
                            <?php foreach ($ticket_priorities as $item) : ?>
                                <option value="<?= $item->id ?>" <?= ($item->id == set_value('priority')) ? 'selected' : '' ?>>
                                    <?= $item->name ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label><?php echo lang('Admin.form.status'); ?></label>
                    <select name="status" class="form-control custom-select">
                        <?php foreach ($ticket_statuses as $k => $v) : ?>
                            <option value="<?= $k ?>" <?= ($k == set_value('status')) ? 'selected' : '' ?>>
                                <?= lang('Admin.form.' . $v) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group" id="child-department-container" style="display:none;">
            <label>Seleccione un Departamento Adjunto (solo uno): <span class="text-danger">*</span></label>
            <div id="child-department-options"></div>
        </div>

        <div class="form-group">
            <label><?php echo lang('Admin.form.subject'); ?></label>
            <input type="text" name="subject" class="form-control" value="<?= set_value('subject'); ?>" required>
        </div>

        <div class="form-group">
            <label><?php echo lang('Admin.form.quickInsert'); ?></label>
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <select name="canned" id="cannedList" onchange="addCannedResponse(this.value);" class="custom-select">
                        <option value=""><?php echo lang('Admin.cannedResponses.menu'); ?></option>
                        <?php if (isset($canned_response)) : ?>
                            <?php foreach ($canned_response as $item) : ?>
                                <option value="<?= $item->id ?>"><?= $item->title ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <select name="knowledgebase" id="knowledgebaseList" onchange="addKnowledgebase(this.value);" class="custom-select">
                        <option value=""><?php echo lang('Admin.kb.menu'); ?></option>
                        <?= $kb_selector ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <textarea class="form-control" name="message" id="messageBox" rows="20"><?= set_value('message'); ?></textarea>
        </div>

        <?php if (site_config('ticket_attachment')) : ?>
            <div class="form-group">
                <label>
                    <?php echo lang('Admin.form.attachments'); ?>
                    <small class="text-danger" id="attachment-notice" style="display:none;">(Adjunto obligatorio)</small>
                </label>
                <?php for ($i = 1; $i <= site_config('ticket_attachment_number'); $i++) : ?>
                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="attachment[]" id="customFile<?= $i; ?>">
                                <label class="custom-file-label" for="customFile<?= $i; ?>" data-browse="<?php echo lang('Admin.form.browse'); ?>">
                                    <?php echo lang('Admin.form.chooseFile'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
                <small class="text-muted">
                    <?= lang('Admin.form.allowedFiles') . ' *.' . implode(', *.', unserialize(site_config('ticket_file_type'))); ?>
                </small>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <button class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> <?php echo lang('Admin.form.submit'); ?>
            </button>
        </div>
        <?= form_close() ?>
    </div>
</div>

<?php
$this->endSection();
$this->section('script_block');
include __DIR__ . '/tinymce.php';
?>
<script>
$(document).ready(function () {
    bsCustomFileInput.init();
});
<?php if (isset($canned_response)) : ?>
    var canned_response = <?= json_encode($canned_response); ?>;
<?php endif; ?>
var KBUrl = '<?= site_url(route_to('staff_ajax_kb'));?>';

// =============================================================
// ===== INICIO: CÓDIGO NUEVO PARA DEPARTAMENTOS ADJUNTOS ======
// =============================================================
$(document).ready(function(){
    const parentDepsList = <?= json_encode(service('settingsAbo')->getParamTextAsArray('DEPS_PADRE_CON_HIJOS') ?? []); ?>;
    const requiredChildDepsList = <?= json_encode(service('settingsAbo')->getParamTextAsArray('DEPS_HIJOS_ADJUNTO_OBLIGATORIO') ?? []); ?>;
    const childMap = <?= json_encode($child_map ?? []); ?>;
    const departmentNames = {
        <?php foreach($departments_list as $dep): ?>
            "<?= $dep->id ?>": "<?= esc($dep->name, 'js'); ?>",
        <?php endforeach; ?>
    };

    const parentSelect = $('#parent-department');
    const childContainer = $('#child-department-container');
    const childOptionsDiv = $('#child-department-options');
    const attachmentNotice = $('#attachment-notice');

    function updateChildDepartments() {
        const parentId = parentSelect.val();
        const parentName = departmentNames[parentId] || '';

        childOptionsDiv.empty();
        childContainer.hide();
        attachmentNotice.hide();

        if (parentDepsList.includes(parentName) && childMap[parentId]) {
            childContainer.show();
            childMap[parentId].forEach(function(child) {
                const radioId = 'child_dep_' + child.id;
                const radioInput = $('<input>', {
                    type: 'radio',
                    name: 'departamento_adjunto',
                    value: child.id,
                    id: radioId,
                    class: 'custom-control-input child-department-radio'
                });
                const radioLabel = $('<label>', {
                    text: child.name,
                    for: radioId,
                    class: 'custom-control-label'
                });
                const divWrapper = $('<div>', { class: 'custom-control custom-radio' });
                divWrapper.append(radioInput).append(radioLabel);
                childOptionsDiv.append(divWrapper);
            });
        }
    }

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

    parentSelect.on('change', updateChildDepartments);
    childOptionsDiv.on('change', 'input.child-department-radio', checkAttachmentRequirement);
});
// =============================================================
// ===== FIN: CÓDIGO NUEVO AÑADIDO =============================
// =============================================================
</script>
<?php $this->endSection(); ?>

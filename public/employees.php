<?php
require_once '../app/config/db.php';
require_once '../app/helpers/authentication.php';
require_once '../app/helpers/subscription.php';

try {
    subscription_require_feature($pdo, (int)($_SESSION['user']['company_id'] ?? 0), 'rh');
} catch (Exception $e) {
    $cid = (int)($_SESSION['user']['company_id'] ?? 0);
    header('Location: subscription.php?company_id=' . $cid . '&upgrade=rh');
    exit;
}

require_once '../app/views/layout_creation.php';
?>

<main class="main-content">
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Funcionários</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger" id="btnExportEmployeesPdf">
                                <i class="material-icons-round">picture_as_pdf</i>
                                Exportar PDF
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEmployee">
                                <i class="material-icons-round">person_add</i>
                                Adicionar Funcionário
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="employeesTable" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>BI</th>
                                        <th>Cargo</th>
                                        <th>Salário</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Funcionário -->
<div class="modal fade" id="modalEmployee" tabindex="-1" aria-labelledby="modalEmployeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEmployee" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmployeeLabel">Cadastrar Funcionário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nome Completo</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Nº do BI</label>
                            <input type="text" name="bi" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Cargo</label>
                            <select name="position" id="positionSelect" class="form-control"></select>
                        </div>
                        <div class="col-md-6">
                            <label>Salário</label>
                            <input type="number" step="0.01" name="salary" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Tipo de documento</label>
                            <select name="document_type" class="form-control">
                                <option value="BI">BI</option>
                                <option value="Passaporte">Passaporte</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Data de Nascimento</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Tipo de Vínculo</label>
                            <input type="text" name="contract_type" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Data de Admissão</label>
                            <input type="date" name="admission_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>IBAN</label>
                            <input type="text" name="iban" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Estado civil</label>
                            <select name="marital_status" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="Solteiro(a)">Solteiro(a)</option>
                                <option value="Casado(a)">Casado(a)</option>
                                <option value="União de facto">União de facto</option>
                                <option value="Divorciado(a)">Divorciado(a)</option>
                                <option value="Viúvo(a)">Viúvo(a)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Nível acadêmico</label>
                            <select name="academic_level" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="Ensino de base">Ensino de base</option>
                                <option value="Ensino médio">Ensino médio</option>
                                <option value="Ensino secundário">Ensino secundário</option>
                                <option value="Ensino superior">Ensino superior</option>
                                <option value="Mestrado">Mestrado</option>
                                <option value="Doutorado">Doutorado</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <div id="currentPhoto" class="mt-1 small text-muted"></div>
                        </div>
                        <div class="col-md-6">
                            <label>Documento 1 (anexo)</label>
                            <input type="file" name="doc1" class="form-control" accept=".pdf,image/*">
                            <div id="currentDoc1" class="mt-1 small text-muted"></div>
                        </div>
                        <div class="col-md-6">
                            <label>Documento 2 (anexo)</label>
                            <input type="file" name="doc2" class="form-control" accept=".pdf,image/*">
                            <div id="currentDoc2" class="mt-1 small text-muted"></div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let isEditing = false;
        const table = $('#employeesTable').DataTable({
            ajax: 'rh/ajax/list_employees.php',
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            columns: [{
                    data: 'name'
                },
                {
                    data: 'bi'
                },
                {
                    data: 'position'
                },
                {
                    data: 'salary',
                    render: data => `Kz ${parseFloat(data).toLocaleString('pt-AO', { minimumFractionDigits: 2 })}`
                },
                {
                    data: 'status',
                    render: data => {
                        const cor = data === 'ativo' ? 'success' : 'danger';
                        return `<span class="badge bg-${cor} text-uppercase">${data}</span>`;
                    }
                },
                {
                    data: null,
                    render: row => `
                    <button class='btn btn-sm btn-warning editEmployee'
                        data-id='${row.id}'
                        data-name='${row.name}'
                        data-bi='${row.bi}'
                        data-position='${row.position}'
                        data-salary='${row.salary}'
                        data-status='${row.status}'
                        data-document_type='${row.document_type}'
                        data-birth_date='${row.birth_date}'
                        data-contract_type='${row.contract_type}'
                        data-admission_date='${row.admission_date}'
                        data-iban='${row.iban}'
                        data-marital_status='${row.marital_status || ''}'
                        data-academic_level='${row.academic_level || ''}'
                        data-photo_url='${row.photo_url || ''}'
                        data-doc1_url='${row.doc1_url || ''}'
                        data-doc2_url='${row.doc2_url || ''}'
                    >Editar</button>`
                }
            ]
        });

        $('#modalEmployee').on('show.bs.modal', function() {
            const select = $('#positionSelect');

            // Evita recriar se já estiver carregado
            if (select.children('option').length <= 1) {
                $.get('rh/ajax/list_positions.php', function(data) {
                    select.empty();
                    select.append('<option value="">Selecione...</option>');
                    if (data && data.data) {
                        data.data.forEach(pos => {
                            select.append(`<option value="${pos.name}" data-salary="${parseFloat(pos.suggested_salary)}">${pos.name}</option>`);
                        });
                    }
                });
            }
        });



        // Preenche salário sugerido ao selecionar cargo
        $('#positionSelect').on('change', function() {
    if (isEditing) return;
    const salario = $(this).find(':selected').data('salary');
    if (salario) {
        $('input[name=salary]').val(parseFloat(salario).toFixed(2));
    }
});


        // Submit do formulário
        $('#formEmployee').on('submit', function(e) {
            e.preventDefault();

            const form = document.getElementById('formEmployee');
            const fd = new FormData(form);

            $.ajax({
                url: 'rh/ajax/save_employee.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function() {
                    $('#modalEmployee').modal('hide');
                    table.ajax.reload();
                    Swal.fire('Sucesso', 'Funcionário salvo com sucesso!', 'success');
                    $('#editId').remove();
                    // limpa arquivos
                    form.reset();
                    $('#positionSelect').val('').trigger('change');
                }
            });
        });

        $('#btnExportEmployeesPdf').on('click', function() {
            window.open('rh/ajax/export_employees_pdf.php', '_blank');
        });

        // Excluir arquivo (foto/doc1/doc2)
        $('#modalEmployee').on('click', '.delete-emp-file', function(){
            const type = $(this).data('type');
            const employeeId = $('#formEmployee').data('employee_id');
            if(!employeeId){
                return Swal.fire('Erro', 'Funcionário não identificado.', 'error');
            }

            Swal.fire({
                title: 'Excluir arquivo?',
                text: 'Essa ação não pode ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Excluir',
                cancelButtonText: 'Cancelar'
            }).then((r) => {
                if(!r.isConfirmed) return;

                $.post('rh/ajax/delete_employee_file.php', { employee_id: employeeId, type }, function(res){
                    if(res && res.success){
                        // limpa visualmente
                        if(type === 'photo') $('#currentPhoto').html('<span>Nenhum arquivo salvo.</span>');
                        if(type === 'doc1') $('#currentDoc1').html('<span>Nenhum arquivo salvo.</span>');
                        if(type === 'doc2') $('#currentDoc2').html('<span>Nenhum arquivo salvo.</span>');
                        // atualiza a tabela pra refletir
                        table.ajax.reload(null, false);
                        Swal.fire('Ok', 'Arquivo excluído.', 'success');
                    } else {
                        Swal.fire('Erro', res.error || 'Não foi possível excluir.', 'error');
                    }
                }, 'json').fail(function(xhr){
                    Swal.fire('Erro', xhr.responseText || 'Falha ao excluir.', 'error');
                });
            });
        });

        // Editar funcionário
        $('#employeesTable').on('click', '.editEmployee', function() {
            const btn = $(this);

            // Preenche campos
            $('#formEmployee input[name=name]').val(btn.data('name'));
            $('#formEmployee input[name=bi]').val(btn.data('bi'));
            $('#formEmployee input[name=salary]').val(btn.data('salary'));
            $('#formEmployee select[name=status]').val(btn.data('status'));
            $('#formEmployee select[name=document_type]').val(btn.data('document_type'));
            $('#formEmployee input[name=birth_date]').val(btn.data('birth_date'));
            $('#formEmployee input[name=contract_type]').val(btn.data('contract_type'));
            $('#formEmployee input[name=admission_date]').val(btn.data('admission_date'));
            $('#formEmployee input[name=iban]').val(btn.data('iban'));
            $('#formEmployee select[name=marital_status]').val(btn.data('marital_status'));
            $('#formEmployee select[name=academic_level]').val(btn.data('academic_level'));
            // Mostra arquivos existentes (link + deletar). Inputs continuam vazios por segurança.
            function renderFile(containerSel, type, filename){
                const $c = $(containerSel);
                if(!filename){
                    $c.html('<span>Nenhum arquivo salvo.</span>');
                    return;
                }

                const safeName = String(filename).replace(/[^a-zA-Z0-9_\-.]/g, '');
                const isPhoto = (type === 'photo');
                const url = isPhoto
                    ? `assets/img/employees/${safeName}`
                    : `assets/docs/employees/${safeName}`;

                const viewLabel = isPhoto ? 'Ver foto' : 'Ver arquivo';

                $c.html(`
                    <a href="${url}" target="_blank">${viewLabel}</a>
                    <span class="mx-2">|</span>
                    <button type="button" class="btn btn-link p-0 text-danger delete-emp-file" data-type="${type}">Excluir</button>
                `);
            }

            renderFile('#currentPhoto', 'photo', btn.data('photo_url'));
            renderFile('#currentDoc1', 'doc1', btn.data('doc1_url'));
            renderFile('#currentDoc2', 'doc2', btn.data('doc2_url'));


            // Aguarda opções estarem prontas para selecionar o cargo correto
            $('#modalEmployee').off('shown.bs.modal').on('shown.bs.modal', function() {
                $('#formEmployee select[name=position]').val(btn.data('position')).trigger('change');
            });

            // Abre modal
            $('#formEmployee').append(`<input type="hidden" name="id" value="${btn.data('id')}" id="editId">`);
            // guarda id atual para ações de delete
            $('#formEmployee').data('employee_id', btn.data('id'));
            $('#modalEmployee').modal('show');
        });

    });
</script>

<?php require_once '../app/views/footer.php'; ?>
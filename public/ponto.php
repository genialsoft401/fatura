<?php
require_once '../app/views/layout_creation.php';
?>

<main class="main-content">
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Registro de Ponto e Faltas</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPonto">
                            <i class="material-icons-round">add_circle</i>
                            Novo Registro
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Mês</label>
                                <input type="month" id="filtroMes" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Funcionário</label>
                                <select id="filtroFuncionario" class="form-control"></select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="pontoTable" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>Funcionário</th>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Justificativa</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal de Registro -->
<div class="modal fade" id="modalPonto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPonto">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Registro de Ponto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Funcionário</label>
                        <select name="employee_id" class="form-control" required></select>
                    </div>
                    <div class="mb-3">
                        <label>Data</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tipo</label>
                        <select name="type" class="form-control">
                            <option value="presença">Presença</option>
                            <option value="falta">Falta</option>
                            <option value="atestado">Atestado</option>
                            <option value="folga">Folga</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Justificativa (opcional)</label>
                        <textarea name="justification" class="form-control"></textarea>
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

<?php require_once '../app/views/footer.php'; ?>

<script>
    $(document).ready(function() {
        const table = $('#pontoTable').DataTable({
            ajax: {
                url: 'rh/ajax/list_attendance.php',
                data: function(d) {
                    d.mes = $('#filtroMes').val();
                    d.funcionario = $('#filtroFuncionario').val();
                }
            },
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            columns: [{
                    data: 'employee_name'
                },
                {
                    data: 'date'
                },
                {
                    data: 'type'
                },
                {
                    data: 'justification'
                },
                {
                    data: null,
                    render: row => `<button class='btn btn-sm btn-danger deleteRegistro' data-id='${row.id}'>Excluir</button>`
                }
            ]
        });

        $('#filtroMes, #filtroFuncionario').on('change', function() {
            table.ajax.reload();
        });

        $('select[name=employee_id]').select2({
            dropdownParent: $('#modalPonto'),
            width: '100%',
            ajax: {
                url: 'rh/ajax/search_employees.php',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term
                }),
                processResults: data => ({
                    results: data
                })
            }
        });

        $('#filtroFuncionario').select2({
            width: '100%',
            ajax: {
                url: 'rh/ajax/search_employees.php',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term
                }),
                processResults: data => ({
                    results: data
                })
            }
        });

        $('#formPonto').on('submit', function(e) {
            e.preventDefault();
            $.post('rh/ajax/save_attendance.php', $(this).serialize(), function() {
                $('#modalPonto').modal('hide');
                table.ajax.reload();
                Swal.fire('Sucesso', 'Registro salvo com sucesso', 'success');
            });
        });

        $('#pontoTable').on('click', '.deleteRegistro', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Tem certeza?',
                text: 'O registro será removido!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post('rh/ajax/delete_attendance.php', {
                        id
                    }, function() {
                        table.ajax.reload();
                        Swal.fire('Excluído', 'Registro removido com sucesso', 'success');
                    });
                }
            });
        });
    });
</script>
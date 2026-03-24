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
                        <h5 class="mb-0">Férias e Licenças</h5>
                        <button id="addVacationBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVacation">
                            <i class="material-icons-round">event</i>
                            Registrar Período
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Mês de referência</label>
                                <input type="month" id="filter_mes" class="form-control" value="<?= date('Y-m') ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label mb-1">Funcionário</label>
                                <select id="filter_funcionario" class="form-control"></select>
                            </div>
                            <div class="col-md-4 d-flex gap-2 justify-content-end">
                                <button id="btnFiltrarVac" class="btn btn-outline-primary">
                                    <i class="material-icons-round">search</i> Filtrar
                                </button>
                                <button id="btnLimparVac" class="btn btn-outline-secondary">Limpar</button>
                                <button id="btnExportVacPdf" class="btn btn-danger">
                                    <i class="material-icons-round">picture_as_pdf</i> Exportar PDF
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="vacationsTable" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>Funcionário</th>
                                        <th>Tipo</th>
                                        <th>Início</th>
                                        <th>Fim</th>
                                        <th>Status</th>
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

<!-- Modal Registro de Férias -->
<div class="modal fade" id="modalVacation" tabindex="-1" aria-labelledby="modalVacationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formVacation">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVacationLabel">Registrar Período</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Funcionário</label>
                            <select name="employee_id" id="employee_id" class="form-control"></select>
                        </div>
                        <div class="col-md-6">
                            <label>Tipo</label>
                            <select name="type" class="form-control">
                                <option value="Férias">Férias</option>
                                <option value="Licença Médica">Licença Médica</option>
                                <option value="Licença Maternidade">Licença Maternidade</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Início</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Fim</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label>Motivo</label>
                            <textarea name="reason" class="form-control"></textarea>
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

        // Função genérica para lidar com erros de AJAX
        function handleAjaxError(xhr) {
            let errorMessage = 'Ocorreu um erro desconhecido.';
            if (xhr.status === 401 || xhr.status === 403) {
                errorMessage = 'Sua sessão expirou. Por favor, faça login novamente.';
                // Opcional: redirecionar para o login após um tempo
                setTimeout(() => window.location.href = 'login.php', 2000);
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const error = JSON.parse(xhr.responseText);
                    errorMessage = error.message || 'Erro no servidor.';
                } catch (e) {
                    errorMessage = 'Erro ao processar a resposta do servidor.';
                }
            }
            Swal.fire('Erro', errorMessage, 'error');
        }

        const table = $('#vacationsTable').DataTable({
            ajax: {
                url: 'rh/ajax/list_vacations.php',
                type: 'GET',
                data: function(d) {
                    d.mes = $('#filter_mes').val();
                    d.funcionario = $('#filter_funcionario').val();
                },
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    handleAjaxError(xhr);
                }
            },
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
            },
            columns: [{
                    data: 'employee_name'
                },
                {
                    data: 'type'
                },
                {
                    data: 'start_date',
                    render: function(data) {
                        return new Date(data + 'T00:00:00').toLocaleDateString('pt-PT');
                    }
                },
                {
                    data: 'end_date',
                    render: function(data) {
                        return new Date(data + 'T00:00:00').toLocaleDateString('pt-PT');
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        let cor = 'secondary';
                        if (data === 'Aprovado') cor = 'success';
                        else if (data === 'Rejeitado') cor = 'danger';
                        else if (data === 'Pendente') cor = 'warning';
                        return `<span class="badge bg-${cor}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(row) {
                        return `
                            <button class='btn btn-sm btn-warning editVacation' data-id='${row.id}' title="Editar"><i class="material-icons-round">edit</i></button>
                            <button class='btn btn-sm btn-danger deleteVacation' data-id='${row.id}' title="Excluir"><i class="material-icons-round">delete</i></button>
                            <button class='btn btn-sm btn-outline-primary changeStatus' data-id='${row.id}' title="Alterar Status"><i class="material-icons-round">key</i></button>
                        `;
                    }
                }
            ]
        });

        // Select funcionário do modal
        $('#employee_id').select2({
            placeholder: 'Selecione o funcionário',
            dropdownParent: $('#modalVacation'),
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

        // Select funcionário do filtro
        $('#filter_funcionario').select2({
            placeholder: 'Todos os funcionários',
            allowClear: true,
            ajax: {
                url: 'rh/ajax/search_employees.php',
                dataType: 'json',
                delay: 250,
                data: params => ({ term: params.term }),
                processResults: data => ({ results: data })
            }
        });

        // Ações dos filtros
        $('#btnFiltrarVac').on('click', function() {
            table.ajax.reload();
        });

        $('#btnLimparVac').on('click', function() {
            $('#filter_mes').val('<?= date('Y-m') ?>');
            $('#filter_funcionario').val(null).trigger('change');
            table.ajax.reload();
        });

        $('#btnExportVacPdf').on('click', function() {
            const mes = $('#filter_mes').val();
            const funcionario = $('#filter_funcionario').val();

            // Exporta PDF do mês selecionado (período completo do mês)
            const url = `rh/ajax/export_vacations_pdf.php?mes=${encodeURIComponent(mes)}&employee_id=${encodeURIComponent(funcionario || '')}`;
            window.open(url, '_blank');
        });

        // Limpar modal ao clicar para adicionar
        $('#addVacationBtn').on('click', function() {
            $('#formVacation')[0].reset();
            $('#formVacation').find('input[name="id"]').remove();
            $('#modalVacationLabel').text('Registrar Período');
            $('#employee_id').val(null).trigger('change');
        });

        // Editar: buscar dados e preencher o modal
        $('#vacationsTable').on('click', '.editVacation', function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'rh/ajax/get_vacation.php',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#modalVacationLabel').text('Editar Período');
                        $('#formVacation').find('input[name="id"]').remove(); // Limpa ID antigo
                        $('#formVacation').append(`<input type="hidden" name="id" value="${data.id}">`);

                        $('#formVacation select[name=type]').val(data.type);
                        $('#formVacation input[name=start_date]').val(data.start_date);
                        $('#formVacation input[name=end_date]').val(data.end_date);
                        $('#formVacation textarea[name=reason]').val(data.reason);

                        // Preenche o select2 com o funcionário
                        if (data.employee) {
                            const option = new Option(data.employee.text, data.employee.id, true, true);
                            $('#employee_id').append(option).trigger('change');
                        }

                        $('#modalVacation').modal('show');
                    } else {
                        Swal.fire('Erro', response.message, 'error');
                    }
                },
                error: handleAjaxError
            });
        });

        // Submeter formulário (Adicionar/Editar)
        $('#formVacation').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'rh/ajax/save_vacation.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#modalVacation').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Sucesso', response.message, 'success');
                    } else {
                        Swal.fire('Erro', response.message, 'error');
                    }
                },
                error: handleAjaxError
            });
        });

        // Excluir
        $('#vacationsTable').on('click', '.deleteVacation', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser revertida!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'rh/ajax/delete_vacation.php',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                Swal.fire('Excluído!', response.message, 'success');
                            } else {
                                Swal.fire('Erro', response.message, 'error');
                            }
                        },
                        error: handleAjaxError
                    });
                }
            });
        });

        // Alterar Status
        $('#vacationsTable').on('click', '.changeStatus', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Alterar status',
                input: 'select',
                inputOptions: {
                    'Aprovado': 'Aprovado',
                    'Pendente': 'Pendente',
                    'Rejeitado': 'Rejeitado'
                },
                inputPlaceholder: 'Selecione o novo status',
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: 'rh/ajax/update_vacation_status.php',
                        type: 'POST',
                        data: {
                            id: id,
                            status: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                Swal.fire('Atualizado', response.message, 'success');
                            } else {
                                Swal.fire('Erro', response.message, 'error');
                            }
                        },
                        error: handleAjaxError
                    });
                }
            });
        });
    });
</script>
<?php require_once '../app/views/footer.php'; ?>
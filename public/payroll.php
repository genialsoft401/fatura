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

<style>
    /* ===== TABELA ESTILO ===== */
    #payrollTable {
        border-collapse: separate;
        border-spacing: 0 12px;
        width: 100%;
    }

    /* HEADER */
    #payrollTable thead th {
        border: none;
        font-size: 12px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 16px;
        text-align: left;
        border-right: 1px solid #e5e7eb57;
    }

    #payrollTable thead th:last-child {
        border-right: none;
    }

    #payrollTable tbody tr td {
        border-right: 1px solid #e5e7eb57;
    }

    /* ROW */
    #payrollTable tbody tr {
        background: #fff !important;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        text-align: left !important;
    }


    /* HOVER PRO */
    #payrollTable tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    /* CELLS */
    #payrollTable tbody td {
        border: none;
        padding: 18px 16px;
        vertical-align: middle;
        font-size: 0.95rem;
        background: #fff !important;
        text-align: left !important;
    }

    #payrollTable thead td {
        background: #111 !important;
        display: none;
        max-width: 80px !important;
    }

    /* BORDAS ARREDONDADAS */
    #payrollTable tbody td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
        background: #fff !important;
    }

    #payrollTable tbody th {
        text-align: left !important;
    }

    #payrollTable tbody td:last-child {
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
        text-align: right;
        padding-right: 24px;
    }

    /* ===== NOME (PRINCIPAL) ===== */
    #payrollTable tbody td:first-child {
        font-weight: 600;
        color: #111;
    }

    /* SUBINFO */
    #payrollTable tbody td small {
        display: block;
        color: #6b7280;
    }
</style>

<main class="main-content">
    <div class="container-fluid mt-5">
        <div class="mb-3 d-flex align-items-center">
            <input type="month" id="inputMesReferencia" class="form-control w-auto me-2" />
            <button class="btn btn-danger" id="btnExportarPDF">
                <i class="material-icons-round">picture_as_pdf</i> Exportar PDF Geral
            </button>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Folha de Pagamento</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPayroll">
                            <i class="material-icons-round">attach_money</i>
                            Registrar Pagamento
                        </button>
                        <a href="rh/export/folha_export.php" class="btn btn-success">
                            <i class="material-icons-round">download</i> Exportar Folha
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="payrollTable" class="table w-100">
                                <thead>
                                    <tr>
                                        <th>Funcionário</th>
                                        <th>Referente a</th>
                                        <th>Salário Base</th>
                                        <th>Bônus</th>
                                        <th>Adicionais</th>
                                        <th>Descontos</th>
                                        <th>Salário Líquido</th>
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

<!-- Modal Registro de Pagamento -->
<div class="modal fade" id="modalPayroll" tabindex="-1" aria-labelledby="modalPayrollLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formPayroll">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPayrollLabel">Registrar Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Funcionário</label>
                            <select name="employee_id" id="employee_id_payroll" class="form-control"></select>
                        </div>
                        <div class="col-md-4">
                            <label>Referente a (MM/AAAA)</label>
                            <input type="month" name="reference_month" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Salário Base</label>
                            <input type="number" name="base_salary" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Bônus</label>
                            <input type="number" name="bonuses" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label>Subs. alimentação</label>
                            <input type="number" name="food_allowance" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label>Subs. transporte</label>
                            <input type="number" name="transport_allowance" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label>Subsídio de férias</label>
                            <select name="vacation_subsidy_pct" class="form-control">
                                <option value="0">0%</option>
                                <option value="50">50%</option>
                                <option value="100">100%</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Subsídio de 13º</label>
                            <select name="thirteenth_subsidy_pct" class="form-control">
                                <option value="0">0%</option>
                                <option value="50">50%</option>
                                <option value="100">100%</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Comissões/Vendas (bônus)</label>
                            <input type="number" name="commissions" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label>Vendas (referência)</label>
                            <input type="number" name="sales" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label>Descontos</label>
                            <input type="number" name="discounts" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label>Data de Pagamento (opcional)</label>
                            <input type="date" name="payment_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Pendente">Pendente</option>
                                <option value="Pago">Pago</option>
                            </select>
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
<!-- jsPDF PRIMEIRO -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


<!-- AutoTable DEPOIS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<script>
    $(document).ready(function() {
        const table = $('#payrollTable').DataTable({
            ajax: {
                url: 'rh/ajax/list_payroll.php',
                data: function(d) {
                    d.mes = $('#inputMesReferencia').val();
                }
            },
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            columns: [{
                    data: 'employee_name'
                },
                {
                    data: 'reference_month'
                },
                {
                    data: 'base_salary',
                    render: data => `Kz ${parseFloat(data).toLocaleString('pt-AO', { minimumFractionDigits: 2 })}`
                },
                {
                    data: 'bonuses',
                    render: data => `Kz ${parseFloat(data).toLocaleString('pt-AO', { minimumFractionDigits: 2 })}`
                },
                {
                    data: null,
                    render: row => {
                        const totalAdd = (
                            parseFloat(row.food_allowance || 0) +
                            parseFloat(row.transport_allowance || 0) +
                            (parseFloat(row.base_salary || 0) * (parseInt(row.vacation_subsidy_pct || 0) / 100)) +
                            (parseFloat(row.base_salary || 0) * (parseInt(row.thirteenth_subsidy_pct || 0) / 100)) +
                            parseFloat(row.commissions || 0) +
                            parseFloat(row.sales || 0)
                        );
                        return `Kz ${totalAdd.toLocaleString('pt-AO', { minimumFractionDigits: 2 })}`;
                    }
                },
                {
                    data: 'discounts',
                    render: data => `Kz ${parseFloat(data).toLocaleString('pt-AO', { minimumFractionDigits: 2 })}`
                },
                {
                    data: 'net_salary',
                    render: data => `Kz ${parseFloat(data).toLocaleString('pt-AO', { minimumFractionDigits: 2 })}`
                },
                {
                    data: 'status',
                    render: status => `<span class="badge bg-${status === 'Pago' ? 'success' : 'warning'}">${status}</span>`
                },
                {
                    data: null,
                    render: row => `
                        <button class='btn btn-sm text-warning'><i class="bi bi-pencil"></i></button>
                        <button class='btn btn-sm text-danger openReceipt'
                            data-id='${row.id}'><i class="bi bi-file-pdf"></i></button>`
                }
            ]
        });

        $('#inputMesReferencia').on('change', function() {
            table.ajax.reload();
        });

        $('#employee_id_payroll').select2({
            placeholder: 'Selecione o funcionário',
            ajax: {
                url: 'rh/ajax/search_employees.php',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term
                }),
                processResults: data => ({
                    results: data
                }),
                templateResult: function(item) {
                    if (!item.id) return item.text;
                    return `${item.text} - ${item.salary ? 'Salário: Kz ' + parseFloat(item.salary).toLocaleString('pt-AO') : ''}`;
                }
            }
        });
        $('#employee_id_payroll').on('select2:select', function(e) {
            const salario = e.params.data.salary;
            if (salario) {
                $('[name=base_salary]').val(parseFloat(salario).toFixed(2));
            }
        });

        $('#formPayroll').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const base = parseFloat(form.find('[name=base_salary]').val());
            const bonus = parseFloat(form.find('[name=bonuses]').val());
            const discount = parseFloat(form.find('[name=discounts]').val());
            const food = parseFloat(form.find('[name=food_allowance]').val() || 0);
            const transport = parseFloat(form.find('[name=transport_allowance]').val() || 0);
            const vacPct = parseInt(form.find('[name=vacation_subsidy_pct]').val() || 0);
            const thirteenthPct = parseInt(form.find('[name=thirteenth_subsidy_pct]').val() || 0);
            const commissions = parseFloat(form.find('[name=commissions]').val() || 0);

            const vacationSub = base * (vacPct / 100);
            const thirteenthSub = base * (thirteenthPct / 100);
            const net = (base + bonus + food + transport + vacationSub + thirteenthSub + commissions - discount).toFixed(2);
            form.append(`<input type='hidden' name='net_salary' value='${net}' id='temp_net'>`);

            $.post('rh/ajax/save_payroll.php', form.serialize(), function() {
                $('#modalPayroll').modal('hide');
                table.ajax.reload();
                Swal.fire('Sucesso', 'Pagamento registrado com sucesso!', 'success');
                resetPayrollForm();
            });
        });

        $('#payrollTable').on('click', '.btn-warning', function() {
            const row = table.row($(this).parents('tr')).data();
            $('#formPayroll select[name=employee_id]').html(`<option value="${row.employee_id}" selected>${row.employee_name}</option>`);
            $('#formPayroll input[name=reference_month]').val(row.reference_month);
            $('#formPayroll input[name=base_salary]').val(row.base_salary);
            $('#formPayroll input[name=bonuses]').val(row.bonuses);
            $('#formPayroll input[name=food_allowance]').val(row.food_allowance);
            $('#formPayroll input[name=transport_allowance]').val(row.transport_allowance);
            $('#formPayroll select[name=vacation_subsidy_pct]').val(row.vacation_subsidy_pct);
            $('#formPayroll select[name=thirteenth_subsidy_pct]').val(row.thirteenth_subsidy_pct);
            $('#formPayroll input[name=commissions]').val(row.commissions);
            $('#formPayroll input[name=sales]').val(row.sales);
            $('#formPayroll input[name=discounts]').val(row.discounts);
            $('#formPayroll input[name=payment_date]').val(row.payment_date);
            $('#formPayroll select[name=status]').val(row.status);
            $('#formPayroll').append(`<input type="hidden" name="id" value="${row.id}" id="editPayrollId">`);
            $('#modalPayroll').modal('show');
        });

        $('#modalPayroll').on('hidden.bs.modal', function() {
            resetPayrollForm();
        });

        function resetPayrollForm() {
            $('#formPayroll')[0].reset();
            $('#formPayroll select[name=employee_id]').val(null).trigger('change');
            $('#editPayrollId').remove();
            $('#temp_net').remove();
        }

        // Recibo: abre o PDF server-side (com proventos e descontos detalhados)
        $('#payrollTable').on('click', '.openReceipt', function() {
            const id = $(this).data('id');
            window.open(`rh/pdf/recibo.php?id=${id}`, '_blank');
        });

        /* (removido) Gerador client-side antigo via jsPDF, pois não refletia INSS/IRT corretamente */

        // $('#payrollTable').on('click', '.generatePdf', function() { ... })

        // --- (resto do código continua) ---


    });

    $('#btnExportarPDF').on('click', function() {
        const mes = $('#inputMesReferencia').val();
        if (!mes) {
            return Swal.fire('Atenção', 'Selecione o mês de referência!', 'warning');
        }

        window.open(`rh/ajax/export_all_payroll_pdf.php?mes=${mes}`, '_blank');
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mask/1.14.16/jquery.mask.min.js"></script>


<?php require_once '../app/views/footer.php'; ?>
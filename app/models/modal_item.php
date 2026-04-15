<!-- Modal item -->
<style>
    .modal-item {
        background: linear-gradient(135deg, #007abd, #6ea8ff);
        color: #fff;
        border-bottom: none;
    }

    .modal-item .btn-close {
        filter: invert(1);
    }

    .modal-content {
        background: #f5f5f5;
        border-radius: 18px;
        overflow: hidden;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        padding: 0;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        padding: 0px 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #007abd;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.1);
    }

    .form-label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .soft-card {
        background: #fff;
        border-radius: 14px;
        padding: 16px;
        border: 1px solid #eef0f6;
    }

    .btn-primary {
        background: #007abd;
        border: none;
        border-radius: 12px;
    }

    .btn-success {
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
    }

    .btn-outline-secondary {
        border-radius: 12px;
    }

    #gerarCodigo {
        border-radius: 10px;
        width: 100px;
        height: 40px;
        margin-left: -5px;

    }

    #codeInput {
        width: 70%;
    }

    #codeInput_content {
        display: flex;
        gap: 10px;
        align-items: center;
        align-content: center;
        justify-items: center;
        justify-content: center;
    }
</style>

<div class="modal fade" id="itemModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header modal-item">
                <div>
                    <h5 class="modal-title mb-1" style="background: none !important;"><?= t('Adicionar Novo Item') ?></h5>
                    <small class="opacity-75" style="margin-left: -50px !important;">Preencha os dados do produto/serviço</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-2">
                <form id="itemForm">

                    <input type="hidden" value="<?= $_SESSION['user']['company_id'] ?>" name="id_company">

                    <!-- BLOCO 1 -->
                    <div class="soft-card mb-2 mt-4">
                        <div class="row align-items-end g-3">

                            <!-- CÓDIGO -->
                            <div class="col-md-5 col-12">
                                <label class="form-label"><?= t('Código') ?></label>

                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control" id="codigo" name="codigo" required>

                                    <button type="button" id="gerarCodigo" class="btn btn-primary px-3">
                                        Gerar
                                    </button>
                                </div>
                            </div>

                            <!-- DESCRIÇÃO -->
                            <div class="col-md-7 col-12">
                                <label class="form-label"><?= t('Descrição') ?></label>
                                <textarea class="form-control" rows="1" id="descricao" name="descricao"></textarea>
                            </div>

                        </div>
                    </div>


                    <!-- BLOCO 2 -->
                    <div class="soft-card mb-2">
                        <div class="row">

                            <div class="col-md-6 mb-2">
                                <label class="form-label"><?= t('Unidade') ?></label>
                                <select class="form-select" name="unidade">
                                    <option value="servico"><?= t('Serviço') ?></option>
                                    <option value="unidade"><?= t('Unidade') ?></option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label"><?= t('Moeda') ?></label>
                                <select class="form-select" name="currency" required>
                                    <?= currencySelects(); ?>
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- BLOCO 3 -->
                    <div class="soft-card mb-2">
                        <div class="row">

                            <div class="col-md-6 mb-2">
                                <label class="form-label"><?= t('Preço Unitário') ?></label>
                                <input type="number" class="form-control" name="preco" step="0.01" required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label"><?= t('Taxa / IVA') ?></label>
                                <select class="form-select" name="taxa">
                                    <option value="">- Selecione -</option>
                                    <option value="IVA - 14%">IVA - 14%</option>
                                    <option value="IVA - 7%">IVA - 7%</option>
                                    <option value="IVA - 5%">IVA - 5%</option>
                                    <option value="Isento">Isento</option>
                                </select>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-2">
                                <label class="form-label"><?= t('PVP') ?></label>
                                <input type="number" class="form-control" name="pvp" step="0.01" required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label"><?= t('Retenção') ?></label>
                                <select class="form-select" name="retencao">
                                    <option value="nao_aplicar"><?= t('Não Aplicar') ?></option>
                                    <option value="6,5">6,5% - Art. 67.º do CII</option>
                                </select>
                            </div>

                        </div>
                    </div>

                </form>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer px-4 pb-4 border-0 p-0">
                <button class="btn btn-success w-1/5" id="saveItem">
                    <?= t('Salvar Item') ?>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // Função para verificar se o código já existe
    document.getElementById('codigo').addEventListener('blur', function() {
        const codigo = this.value;

        if (codigo) {
            fetch('items/ajax/check_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        codigo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        alert('Este código já existe. Por favor, insira outro.');
                        this.value = '';
                    }
                })
                .catch(error => console.error('Erro:', error));
        }
    });

    // Função para gerar código baseado no nome da empresa
    document.getElementById('gerarCodigo').addEventListener('click', function() {


        fetch('items/ajax/generate_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.generated_code) {
                    document.getElementById('codigo').value = data.generated_code;
                } else {
                    alert('Erro ao gerar o código.');
                }
            })
            .catch(error => console.error('Erro:', error));
    });

    // $('#descricao').trumbowyg({
    //     autogrow: false
    // });
</script>
<!-- Modal item -->
 <style>
 .trumbowyg-box, .trumbowyg-editor { 
    overflow: auto !important;  /* Adiciona barra de rolagem quando necessário */
}


 </style>
<div class="modal fade" id="itemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-item">
                <h5 class="modal-title" id="itemModalLabel"><?= t('Adicionar Novo Item') ?></h5>
                <button type="button" class="btn-close" id="modalCloseButton" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <?php
                    $companies = getUserCompanies($_SESSION['user']['id']);
                    ?>
                            <input type="text" hidden readonly value="<?=$_SESSION['user']['company_id']?>" class="form-control" id="id_company" name="id_company" required>

                    <div class="row">
                        <div class="col-md-10 mb-3">
                            <label for="codigo" class="form-label"><?= t('Código') ?>:</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        <div class="col-md-2 mb-3">  
                            <button type="button" id="gerarCodigo" class="btn btn-primary mt-4">Gerar</button>
                        </div>

                    </div> 

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unidade" class="form-label"><?= t('Unidade') ?>:</label>
                            <select class="form-select" id="unidade" name="unidade">
                                <option value="servico"><?= t('Serviço') ?></option>
                                <option value="unidade"><?= t('Unidade') ?></option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label"><?= t('Moeda Padrão') ?>:</label>
                            <select class="form-select" name="currency" required id="currency">
                                <?= currencySelects(); ?>
                            </select>
                        </div>


                       
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="preco" class="form-label"><?= t('Preço Unitário') ?>:</label>
                            <input type="number" class="form-control" id="preco" name="preco" step="0.01" required>
                        </div>
                        
                       
                        <div class="col-md-6 mb-3">
                            <label for="taxa" class="form-label"><?= t('Taxa/IVA') ?>:</label>
                            <select class="form-select" id="taxa" name="taxa">
                                <option value="14"><?= t('14% - Taxa14') ?></option>
                                <option value="isento"><?= t('Isento') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pvp" class="form-label"><?= t('PVP') ?>:</label>
                            <input type="number" class="form-control" id="pvp" name="pvp" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="retencao" class="form-label"><?= t('Retenção') ?>:</label>
                            <select class="form-select" id="retencao" name="retencao">
                                <option value="nao_aplicar"><?= t('Não Aplicar') ?></option>
                                <option value="aplicar"><?= t('Aplicar') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="descricao" class="form-label"><?= t('Descrição') ?>:</label>
                            <textarea class="form-control" id="descricao" name="descricao" required></textarea>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="saveItem"><?= t('Salvar') ?></button>
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

    $('#descricao').trumbowyg({
        autogrow: false  
    });


</script>
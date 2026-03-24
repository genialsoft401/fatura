<!-- Modal Editar Item -->
<div class="modal fade" id="editItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-item">
                <h5 class="modal-title" id="editItemModalLabel"><?= t('Editar Item') ?></h5>
                <button type="button" class="btn-close" id="editModalCloseButton" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    <input type="hidden" id="edit_id" name="id">

                    <?php
                    $companies = getUserCompanies($_SESSION['user']['id']);
                    ?>
 <select hidden readonly class="form-control" id="edit_id_company" name="id_company" required>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                    <div class="row">
                     

                        <div class="col-md-12 mb-3">
                            <label for="edit_codigo" class="form-label"><?= t('Código') ?>:</label>
                            <input type="text" class="form-control" id="edit_codigo" name="codigo" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_descricao" class="form-label"><?= t('Descrição') ?>:</label>
                            <textarea class="form-control" id="edit_descricao" name="descricao" required></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_unidade" class="form-label"><?= t('Unidade') ?>:</label>
                            <select class="form-select" id="edit_unidade" name="unidade">
                                <option value="service"><?= t('Serviço') ?></option>
                                <option value="unit"><?= t('Unidade') ?></option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_retencao" class="form-label"><?= t('Retenção') ?>:</label>
                            <select class="form-select" id="edit_retencao" name="retencao">
                                <option value="do_not_apply"><?= t('Não Aplicar') ?></option>
                                <option value="apply"><?= t('Aplicar') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_preco" class="form-label"><?= t('Preço Unitário') ?>:</label>
                            <input type="number" class="form-control" id="edit_preco" name="preco" step="0.01" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_taxa" class="form-label"><?= t('Taxa/IVA') ?>:</label>
                            <select class="form-select" id="edit_taxa" name="taxa">
                                <option value="14"><?= t('14% - Taxa14') ?></option>
                                <option value="exempt"><?= t('Isento') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_pvp" class="form-label"><?= t('PVP') ?>:</label>
                            <input type="number" class="form-control" id="edit_pvp" name="pvp" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_currency" class="form-label"><?= t('Moeda') ?>:</label>
                            <select class="form-select" id="edit_currency" name="currency" required>
                                <?= currencySelects(); ?>
                            </select>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="saveEdit"><?= t('Salvar') ?></button>
            </div>
        </div>
    </div>
</div>
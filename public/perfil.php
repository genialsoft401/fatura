<?php
require_once '../app/views/layout_creation.php';
?>

<body>

    <main>
        <div class="container mt-5">

            <h2 class="mb-4">Perfil</h2>

            <!-- Foto de Perfil -->
            <div class="text-center mb-4">
                <div class="position-relative d-inline-block">
                    <img id="profileImage" src="assets/img/profiles/<?= $_SESSION['user']['image'] ?>?t=<?= time() ?>" alt="Foto de Perfil" class="rounded-circle border" style="width: 150px; height: 150px; object-fit: cover;">
                    <input type="file" id="profileInput" class="d-none" accept="image/*">
                    <label for="profileInput" class="btn btn-primary mt-2 d-block">Alterar Foto</label>
                </div>
            </div>

            <!-- Modal de Corte/Enquadramento (Cropper) -->
            <div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajustar foto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <style>
                            /* Crop modal: melhora visual no celular */
                            #cropStage { height: 65vh; min-height: 320px; }
                            @media (max-width: 576px) {
                                #cropStage { height: 60vh; min-height: 360px; }
                                #cropPreviewWrap { display:none; }
                            }
                        </style>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-lg-8">
                                    <div id="cropStage">
                                        <img id="cropImage" alt="Cortar" style="max-width: 100%; display:block;">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4" id="cropPreviewWrap">
                                    <div class="mb-2"><strong>Prévia</strong></div>
                                    <div class="rounded-circle border overflow-hidden" style="width:150px;height:150px;">
                                        <img id="cropPreview" alt="Prévia" style="width:100%;height:100%;object-fit:cover;" />
                                    </div>
                                    <div class="small text-muted mt-2">Dica: arraste para reenquadrar, use scroll/pinch para zoom.</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" id="saveCropBtn" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

            <form id="profileForm">
            <h4 class="mb-4">Dados Pessoais</h4>
            <div class="w-100 row">
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="name" class="form-label"><?= t('Nome') ?>:</label>
                        <input type="text" value="<?= $_SESSION['user']['name'] ?>" class="form-control" id="name" name="name" required>
                    </div>

                </div>
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="username" class="form-label"><?= t('Usuário') ?>:</label>
                        <input type="text" readonly disabled class="form-control" id="username" name="username" value="<?= $_SESSION['user']['username'] ?>" required>
                    </div>

                </div>
            </div>
            <div class="w-100 row">
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="phone" class="form-label"><?= t('Telefone') ?>:</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= $_SESSION['user']['phone'] ?>" required>
                    </div>

                </div>
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="email" class="form-label"><?= t('E-mail') ?>:</label>
                        <input type="text" readonly disabled class="form-control" id="email" name="email" value="<?= $_SESSION['user']['email'] ?>" required>
                    </div>

                </div>
            </div>
            <div class="w-100 row">
                <div class="col-md-6 mb-3">
                    <div id="select-contact-container">
                        <label for="gender-select" class="form-label"><?= t('Gênero') ?>:</label>
                        <select id="gender-select" class="form-select" name="gender">
                            <?php
                            $gender = $_SESSION['user']['gender'] ?? ''; // Pega o gênero do usuário ou uma string vazia
                            if (empty($gender)) {
                                echo '<option value="" selected>' . t('Selecione um Gênero...') . '</option>';
                            } else {
                                echo '<option value="' . htmlspecialchars($gender) . '" selected>' . t($gender) . '</option>';
                            }
                            ?>
                            <option value="Masculino" <?= ($gender === 'Masculino') ? 'selected' : '' ?>><?= t('Masculino') ?></option>
                            <option value="Feminino" <?= ($gender === 'Feminino') ? 'selected' : '' ?>><?= t('Feminino') ?></option>
                            <option value="Outro" <?= ($gender === 'Outro') ? 'selected' : '' ?>><?= t('Outro') ?></option>
                        </select>
                    </div>

                </div>
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="identification" class="form-label"><?= t('Número de Documento') ?>:</label>
                        <input type="text" class="form-control" id="identification" name="identification" value="<?= $_SESSION['user']['identification'] ?>" required>
                    </div>

                </div>
            </div>
            <hr>

            <h4 class="mb-4">Dados de Localização</h4>
            <div class="w-100 row">
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="address" class="form-label"><?= t('Endereço') ?>:</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?= $_SESSION['user']['address'] ?>">
                    </div>

                </div>
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="address_number" class="form-label"><?= t('Número') ?>:</label>
                        <input type="text" class="form-control" id="address_number" name="address_number" value="<?= $_SESSION['user']['address_number'] ?>">
                    </div>

                </div>
            </div>


            <div class=" w-100 row">
                <div class="col-md-6 mb-3">
                    <label for="country" class="form-label"><?= t('País') ?>:</label>
                    <select class="form-select" id="country" name="country" required>
                        <option value=""><?= t('Carregando países') ?>...</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="city" class="form-label"><?= t('Cidade') ?>: </label>
                    <select class="form-select" id="city" name="address_district" required>
                        <option value=""><?= t('Escolha um país primeiro') ?></option>
                    </select>
                </div>
            </div>


            <div class="w-100 row">

                <div class="col-md-6 mb-3">
                    <div id="select-contact-container">
                        <label for="lang-select" class="form-label"><?= t('Idioma Padrão') ?>:</label>
                        <select id="lang-select" class="form-select" name="lang">
                            <?php $idioma = $_SESSION['user']['lang'] ?? ''; ?>
                            <?php if (empty($idioma)) : ?>
                                <option value="" selected><?= t('Selecione um Idioma...') ?></option>
                            <?php endif; ?>

                            <option value="angola" <?= ($idioma === 'angola') ? 'selected' : '' ?>><?= t('Angola') ?></option>
                            <option value="brasil" <?= ($idioma === 'brasil') ? 'selected' : '' ?>><?= t('Brasil') ?></option>
                        </select>
                    </div>
                </div>


            </div>
            <button id="update-profile" class="btn btn-primary"><?= t('Atualizar') ?></button>
            </form>
    </main>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    <script src="perfil/perfil.js"></script>
    <script>
        var selectedCountry = "<?php echo isset($_SESSION['user']['country']) ? $_SESSION['user']['country'] : ''; ?>";
        var selectedCity = "<?php echo isset($_SESSION['user']['address_district']) ? $_SESSION['user']['address_district'] : ''; ?>";
        selectCountry(selectedCountry, selectedCity);
    </script>
    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>
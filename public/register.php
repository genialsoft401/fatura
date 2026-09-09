<?php
require_once '../app/helpers/translation.php';
require_once '../app/config/db.php';
require_once '../app/helpers/functions.php';
require_once '../app/views/head.php';
?>

<style>
    /* ===== Estilo de input "pill" com label sobre a borda (inspirado no design de referência) ===== */
    .field-group {
        position: relative;
        margin-bottom: 1.9rem;
    }

    .field-group>label {
        position: absolute;
        top: -0.6rem;
        left: 1.1rem;
        background: #fff;
        padding: 0 0.45rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #6b7280;
        z-index: 2;
        transition: color .2s ease;
    }

    .field-wrapper {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        border: 1.5px solid #d7dbe0;
        border-radius: 999px;
        padding: 0.7rem 1.1rem;
        background: #fff;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .field-wrapper i,
    .field-wrapper .field-icon {
        font-size: 1rem;
        color: #9aa0a6;
        flex: 0 0 auto;
        transition: color .2s ease;
    }

    .field-wrapper input,
    .field-wrapper select {
        border: 0px solid none;
        outline: 0px solid none;
        background: transparent;
        flex: 1 1 auto;
        font-size: 0.95rem;
        color: #212529;
        min-width: 0;
        padding: 0;
    }

    .field-wrapper select {
        appearance: none;
        -webkit-appearance: none;
    }

    /* Estado ACTIVE (foco) */
    .field-group:focus-within .field-wrapper {
        border-color: var(--blue, #2f9bff);
        box-shadow: 0 0 0 4px rgba(47, 155, 255, 0.12);
    }

    .field-group:focus-within>label,
    .field-group:focus-within .field-wrapper i {
        color: var(--blue, #2f9bff);
    }

    /* Estado FILLED (com valor, sem foco) */
    .field-group.is-filled:not(:focus-within) .field-wrapper {
        border-color: #b9c0c9;
    }

    /* Estado ERROR */
    .field-group.has-error .field-wrapper {
        border-color: #ff4d4f;
        box-shadow: 0 0 0 4px rgba(255, 77, 79, 0.12);
    }

    .field-group.has-error>label,
    .field-group.has-error .field-wrapper i {
        color: #ff4d4f;
    }

    .field-error-msg {
        display: none;
        align-items: center;
        gap: 0.35rem;
        color: #ff4d4f;
        font-size: 0.8rem;
        margin: 0.4rem 0 0 1.1rem;
    }

    .field-group.has-error .field-error-msg {
        display: flex;
    }

    /* Prefixo fixo (usado no campo Website) */
    .field-prefix {
        color: #9aa0a6;
        font-size: 0.95rem;
        flex: 0 0 auto;
        user-select: none;
    }

    /* Regras de senha (mantido, apenas realinhado ao novo wrapper) */
    #passwordRules {
        width: 100%;
        margin-top: 0.5rem;
        border-radius: 12px;
        position: absolute !important;
        z-index: 10;
    }
</style>

<div class="row w-100 mx-0 gx-0"> <!-- Removendo margens laterais -->

    <!-- Coluna da Imagem agora à esquerda -->
    <div class="col-12 col-md-6 d-flex justify-content-center align-items-center vh-100" id="bgRegister">
        <div class="" style="position:absolute;z-index:100;left:2rem;top:3rem">
            <?= gerarDropdownPaises($paises, $paisSelecionado); ?>
        </div>
        <img class="img-fluid" src="assets/img/logo/BXpert-Branca.png" style="max-width: 80%;">
    </div>

    <!-- Coluna do Formulário agora à direita -->
    <div class="col-12 col-sm-6">
        <div class="d-flex flex-column justify-content-center align-items-center min-vh-100 p-2">

            <div class="d-flex flex-column mb-3 text-center">
                <h2 class="fw-bold mb-0"><?= t('Registre-se') ?></h2>
                <span class="text-grey"><?= t('Junte-se ao sistema mais inovador e eficiente do mercado!') ?></span>
            </div>


            <form id="registerForm" class="w-75 mx-auto">
                <h5 class="mt-3 mb-3"><?= t('Dados Pessoais') ?></h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group" data-field="username">
                            <label for="username"><?= t('Usuário') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-person"></i>
                                <input style="border: none; background: none !important;" type="text" id="username" name="username" required>
                            </div>
                            <small id="usernameFeedback" class="field-error-msg"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group" data-field="name">
                            <label for="name"><?= t('Nome Completo') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-person-badge"></i>
                                <input style="border: none; background: none !important;" type="text" id="name" name="name" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group" data-field="email">
                            <label for="email"><?= t('E-mail') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-envelope"></i>
                                <input style="border: none; background: none !important;" type="email" id="email" name="email" required>
                            </div>
                            <small id="emailFeedback" class="field-error-msg"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group" data-field="phone">
                            <label for="phone"><?= t('Telefone') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-telephone"></i>
                                <input style="border: none; background: none !important;" type="text" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group" data-field="password">
                            <label for="password"><?= t('Senha') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-lock"></i>
                                <input style="border: none; background: none !important;" type="password" id="password" name="password" required>
                            </div>
                            <div id="passwordRules" class="position-absolute bg-white border p-2 d-none">
                                <ul class="list-unstyled mb-0">
                                    <li id="rule-length" class="text-danger"><?= t('A senha deve ter no mínimo 6 caracteres') ?></li>
                                    <li id="rule-uppercase" class="text-danger"><?= t('A senha deve conter pelo menos uma letra maiúscula') ?></li>
                                    <li id="rule-lowercase" class="text-danger"><?= t('A senha deve conter pelo menos uma letra minúscula') ?></li>
                                    <li id="rule-special" class="text-danger"><?= t('A senha deve conter pelo menos um caractere especial (!@#$%^&*)') ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group" data-field="gender">
                            <label for="gender"><?= t('Gênero') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-people"></i>
                                <select style="border: none; background: none !important;" id="gender" name="gender" required>
                                    <option value="Masculino"><?= t('Masculino') ?></option>
                                    <option value="Feminino"><?= t('Feminino') ?></option>
                                    <option value="Outro"><?= t('Outro') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mt-3 mb-4"><?= t('Dados da Empresa') ?></h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group" data-field="company_name">
                            <label for="company_name"><?= t('Nome da Empresa') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-building"></i>
                                <input style="border: none; background: none !important;" type="text" id="company_name" name="company_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group" data-field="registration_number">
                            <label for="registration_number"><?= t('CNPJ') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-hash"></i>
                                <input style="border: none; background: none !important;" type="text" id="registration_number" name="registration_number" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group" data-field="company_phone">
                            <label for="company_phone"><?= t('Telefone da Empresa') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-telephone-forward"></i>
                                <input style="border: none; background: none !important;" type="text" id="company_phone" name="company_phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group" data-field="website">
                            <label for="website"><?= t('Website') ?></label>
                            <div class="field-wrapper">
                                <i class="bi bi-globe"></i>
                                <span class="field-prefix">https://</span>
                                <input style="border: none; background: none !important;" type="text" id="website" name="website" placeholder="meusite.com">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100"><?= t('Cadastrar') ?></button>
            </form>

            <div class="text-center mt-3">
                <p><?= t('Já tem conta?') ?> <a href="login.php" class="text-decoration-none" style="color:var(--blue)"><?= t('Clique aqui para logar.') ?></a></p>
            </div>

        </div>
    </div>
</div>

<script>
    // Marca cada .field-group como "preenchido" (estado FILLED) e limpa erro ao digitar
    document.querySelectorAll('.field-group').forEach(function(group) {
        var control = group.querySelector('input, select');
        if (!control) return;

        function syncFilled() {
            if (control.value && control.value.trim() !== '') {
                group.classList.add('is-filled');
            } else {
                group.classList.remove('is-filled');
            }
        }

        syncFilled();
        control.addEventListener('input', function() {
            syncFilled();
            group.classList.remove('has-error');
        });
        control.addEventListener('change', syncFilled);
    });

    // Helper para marcar/desmarcar erro num campo específico (usar no register.js)
    // ex: setFieldError('email', 'E-mail inválido');
    window.setFieldError = function(fieldName, message) {
        var group = document.querySelector('.field-group[data-field="' + fieldName + '"]');
        if (!group) return;
        var msgEl = group.querySelector('.field-error-msg');
        group.classList.add('has-error');
        if (msgEl) msgEl.textContent = message || '';
    };
    window.clearFieldError = function(fieldName) {
        var group = document.querySelector('.field-group[data-field="' + fieldName + '"]');
        if (!group) return;
        group.classList.remove('has-error');
    };

    // Website: só o utilizador escreve a parte depois de https://
    document.getElementById('registerForm').addEventListener('submit', function() {
        var websiteInput = document.getElementById('website');
        if (websiteInput.value && websiteInput.value.trim() !== '') {
            var value = websiteInput.value.trim().replace(/^https?:\/\//i, '');
            websiteInput.value = 'https://' + value;
        }
    });
</script>

<script src="register/register.js?v=1.6"></script>
<?php require_once '../app/views/footer.php'; ?>
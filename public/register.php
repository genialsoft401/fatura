<?php
require_once '../app/helpers/translation.php';
require_once '../app/config/db.php';
require_once '../app/helpers/functions.php';
require_once '../app/views/head.php';
?>
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
        <div class="d-flex flex-column justify-content-center align-items-center min-vh-100 p-3">

            <div class="d-flex flex-column mb-3 text-center">
                <h2 class="fw-bold mb-0"><?= t('Registre-se') ?></h2>
                <span class="text-grey"><?= t('Junte-se ao sistema mais inovador e eficiente do mercado!') ?></span>
            </div>


            <form id="registerForm" class="w-75 mx-auto">
                <h5 class="mt-3"><?= t('Dados Pessoais') ?></h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label"><?= t('Usuário') ?></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <small id="usernameFeedback" class="form-text"></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label"><?= t('Nome Completo') ?></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label"><?= t('E-mail') ?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <small id="emailFeedback" class="text-danger"></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label"><?= t('Telefone') ?></label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label"><?= t('Senha') ?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="passwordRules" class="position-absolute bg-white border p-2 d-none">
                            <ul class="list-unstyled mb-0">
                                <li id="rule-length" class="text-danger"><?= t('A senha deve ter no mínimo 6 caracteres') ?></li>
                                <li id="rule-uppercase" class="text-danger"><?= t('A senha deve conter pelo menos uma letra maiúscula') ?></li>
                                <li id="rule-lowercase" class="text-danger"><?= t('A senha deve conter pelo menos uma letra minúscula') ?></li>
                                <li id="rule-special" class="text-danger"><?= t('A senha deve conter pelo menos um caractere especial (!@#$%^&*)') ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label"><?= t('Gênero') ?></label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="Masculino"><?= t('Masculino') ?></option>
                            <option value="Feminino"><?= t('Feminino') ?></option>
                            <option value="Outro"><?= t('Outro') ?></option>
                        </select>
                    </div>
                </div>

                <h5 class="mt-3"><?= t('Dados da Empresa') ?></h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="company_name" class="form-label"><?= t('Nome da Empresa') ?></label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="registration_number" class="form-label"><?= t('CNPJ') ?></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="company_phone" class="form-label"><?= t('Telefone da Empresa') ?></label>
                        <input type="text" class="form-control" id="company_phone" name="company_phone" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label"><?= t('Website') ?></label>
                        <input type="url" class="form-control" id="website" name="website">
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100"><?= t('Cadastrar') ?></button>
            </form>

            <div class="text-center mt-3">
            <p><?=t('Já tem conta?')?> <a href="login.php" class="text-decoration-none" style="color:var(--blue)"><?=t('Clique aqui para logar.')?></a></p>
        </div>
            

        </div>
    </div>
</div>

<script src="register/register.js"></script>
<?php require_once '../app/views/footer.php'; ?>
<?php
require_once '../app/helpers/translation.php';
require_once '../app/config/db.php';
require_once '../app/helpers/functions.php';
require_once '../app/views/head.php';
?>
<div class="row w-100 mx-0 gx-0">

    <!-- Coluna do formulário -->
    <div class="col-12 col-sm-6">
        <div class="d-flex flex-column justify-content-between align-items-center min-vh-100">
            <div class="w-100 d-md-none d-flex justify-content-center aling-items-center">
                <?= gerarDropdownPaises($paises, $paisSelecionado); ?>
            </div>
            <!-- Título -->
            <div class="d-flex flex-column mb-3 text-center mt-4">
                <h2 class="fw-bold mb-0"><?= t('Acesso') ?></h2>
                <span class="text-grey"><?= t('Conecte-se com a melhor do mercado!') ?></span>
            </div>

            <!-- Erro -->
            <div class="alert alert-danger w-75 text-center d-none" role="alert" id="error-message">
                <?= t('Usuário não localizado') ?>
            </div>

            <!-- Formulário -->
            <form id="loginForm" class="w-100 px-3 d-flex align-items-center justify-content-center">
                <div class="w-100 bg-white shadow-sm rounded p-3" style="max-width: 400px;">
                    <div class="mb-3">
                        <label for="user_email" class="form-label mb-0"><?= t('Usuário ou E-mail') ?></label>
                        <input type="text" class="form-control p-2" id="user_email" name="user_email" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label mb-0"><?= t('Senha') ?></label>
                        <div class="position-relative">
                            <input type="password" class="form-control p-2" id="password" name="password" required>
                            <span class="position-absolute end-0 top-50 translate-middle-y me-2" id="togglePassword" style="cursor: pointer;">
                                <i class="material-icons-outlined">visibility</i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me"><?= t('Lembrar de mim') ?></label>
                        </div>
                        <a href="#" id="forgotPassword" class="text-decoration-none" style="font-size:13px;"><?= t('Esqueci minha senha') ?></a>
                    </div>
                    <div class="w-100 d-flex justify-content-center mt-2">
                        <button id="btnAcessar" class="btn btn-primary bg-blue w-100 p-3"><?= t('Acessar') ?></button>
                    </div>
                </div>
            </form>

            <!-- Google login -->
            <div class="w-100 text-center mt-3">
                <a href="loginGoogle/loginGoogle.php" class="d-flex justify-content-center align-items-center">
                    <button class="gsi-material-button">
                        <div class="gsi-material-button-state"></div>
                        <div class="gsi-material-button-content-wrapper">
                            <div class="gsi-material-button-icon">
                                <!-- Ícone Google -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="display: block;">
                                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                                    <path fill="none" d="M0 0h48v48H0z"></path>
                                </svg>
                            </div>
                            <span class="gsi-material-button-contents"><?= t('Continue com Google') ?></span>
                        </div>
                    </button>
                </a>
            </div>

            <!-- Cadastro -->
            <div class="text-center mt-3">
                <p><?= t('Não tem uma conta?') ?> <a href="register.php" class="text-decoration-none" style="color:var(--blue)"><?= t('Cadastre-se') ?></a></p>
            </div>
            <!-- Header Azul Mobile -->
            <div class="d-block d-md-none w-100" style="background: url('assets/img/fundo-login-vermelho.png') center/cover no-repeat; padding: 2rem 0;">
                <div class="text-center">
                    <img src="assets/img/logo/BXpert-Branca.png" alt="BXpert Logo" style="max-height: 8rem; ">
                </div>
            </div>

        </div>

    </div>


    <div class="col-12 col-md-6 d-none d-md-flex justify-content-center align-items-center vh-100" id="bgLogin">
        <div style="position:absolute;z-index:100;right:2rem;top:3rem">
            <?= gerarDropdownPaises($paises, $paisSelecionado); ?>
        </div>
        <div class="text-center">
            <img src="assets/img/logo/BXpert-Branca.png" alt="BXpert Logo" style="max-height: 20rem;">
        </div>
    </div>
</div>

<script>
    $(".country-option").on("click", function(e) {
        e.preventDefault();
        country = $(this).data("country");
        let flagUrl = $(this).data("flag");
        let countryName = $(this).text().trim();

        $("#selectedFlag").attr("src", flagUrl);
        $("#selectedCountry").text(countryName);
        $("#country").val(country);

        $.post("../app/helpers/translation.php", {
            lang: country
        }, function(response) {
            location.reload();
        });
    });

    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            icon.textContent = 'visibility';
        }
    });
</script>

<script src="./assets/js/jsencrypt.min.js"></script>
<script src="login/login.js"></script>
<?php require_once '../app/views/footer.php'; ?>
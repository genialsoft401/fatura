<header class="p-2 mb-3 border-bottom cor-estoque">



    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between">


        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <!-- Botão hambúrguer -->
            <button id="mobileMenuBtn" class="btn d-lg-none">
                <i class="material-icons-round">menu</i>
            </button>


            <!-- Empresa (ícone + nome ou sigla) -->
            <div class="dropdown">
                <button class="btn btn-transparent dropdown-toggle text-white d-flex align-items-center gap-1"
                    type="button" id="empresaDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="material-icons-round">business</span>
                    <span class="d-none d-sm-inline"><?= $_SESSION['user']['name_company'] ?? 'Empresa' ?></span>
                    <span class="d-inline d-sm-none fw-bold text-uppercase"><?= $_SESSION['user']['acronym'] ?? 'EMP' ?></span>
                </button>
                <ul class="dropdown-menu text-center" id="empresaDropdownMenu" aria-labelledby="empresaDropdown"></ul>
            </div>

            <ul class="dropdown-menu aling-items-center text-center" id="empresaDropdownMenu" aria-labelledby="empresaDropdown" style="font-size: 14px!important;">
                <!-- Empresas serão carregadas aqui via AJAX -->
            </ul>
        </div>


        <?php
        $dateAtual = date('Y-m-d H:i:s');
        $nomeFormatado = formatName($_SESSION['user']['name']);   ?>

        <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-wrap">
            <!-- PERFIL -->
            <div class="perfil-container position-relative">
                <a href="#" class="d-flex align-items-center text-decoration-none link-dark" onclick="togglePopup('perfil-menu')">
                    <img src="assets/img/profiles/<?= $_SESSION['user']['image'] ?>" alt="perfil" width="32" height="32" class="rounded-circle" style="border:.1rem solid #fff">
                    <div class="d-none d-sm-flex flex-column ms-2 align-items-start text-start">
                        <strong class="text-white mb-0" style="line-height: 1;"><?= htmlspecialchars($nomeFormatado) ?></strong>
                        <span class="mt-0" style="font-size: 0.7rem; color: #fff; line-height: 1;"><?= t($_SESSION['user']['role']) ?></span>
                    </div>
                </a>
                <div class="popup-menu text-center mt-2" id="perfil-menu">
                    <a class="popup-item" href="perfil.php"><?= t('Perfil') ?></a>
                    <hr class="popup-divider">
                    <a class="popup-item" href="list_companies.php"><?= t('Gestão de empresas') ?></a>
                    <hr class="popup-divider">
                    <a class="popup-item" href="logout.php"><?= t('Sair') ?></a>
                </div>
            </div>
        </div>


    </div>
</header>
<script>
    function togglePopup(id) {
        let popup = document.getElementById(id);
        let allPopups = document.querySelectorAll('.popup-menu');

        allPopups.forEach(p => {
            if (p.id !== id) {
                p.classList.remove('show');
            }
        });

        popup.classList.toggle('show');
    }

    document.addEventListener("click", function(event) {
        let isClickInside = event.target.closest(".position-relative, .perfil-container");
        if (!isClickInside) {
            document.querySelectorAll('.popup-menu').forEach(p => p.classList.remove('show'));
        }
    });

    function trocarEmpresa(empresaId, name, registration, email) {
        fetch('assets/ajax/change_company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `company_id=${empresaId}&name_company=${encodeURIComponent(name)}&registration_number=${encodeURIComponent(registration)}&email_company=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("empresaDropdown").innerText = name;
                    location.reload();
                } else {
                    alert('Erro ao trocar de empresa');
                }
            })
            .catch(error => console.error('Erro ao trocar de empresa:', error));
    }


    function carregarEmpresas() {
        fetch('assets/ajax/get_companies.php')
            .then(response => response.json())
            .then(data => {
                let dropdown = document.getElementById("empresaDropdownMenu");
                dropdown.innerHTML = ''; // Limpa as opções existentes

                data.forEach(empresa => {
                    let li = document.createElement("li");
                    li.innerHTML = `<a class="dropdown-item" href="#" onclick="trocarEmpresa(${empresa.id}, '${empresa.name}', '${empresa.registration_number}', '${empresa.email}')">
                                ${empresa.name}
                            </a>`;
                    dropdown.appendChild(li);
                });
            })
            .catch(error => console.error('Erro ao carregar empresas:', error));
    }
    carregarEmpresas();
</script>

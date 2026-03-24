<div class="sidebar" id="sidebar">
    <div class="d-flex justify-content-center align-items-center">
        <div class="logo">
            <a href="index.php">
                <img id="sidebarLogo" src="assets/img/logo/BXpert2.png" alt="Logo">
            </a>
        </div>
    </div>

    <ul class="menu ">
        <h3><?= t('Home') ?></h3>
        <li>
            <a href="index.php" class="menu-link">
                <span class="material-icons-round">dashboard</span>
                <span class="text"><?= t('Painel') ?></span>
            </a>
        </li>

        <!-- Fatura -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <div class="menu-item">
                    <span class="material-icons-round">receipt_long</span>
                    <span class="text ms-2"><?= t('Fatura') ?></span>
                </div>
                <span class="material-icons-round dropdown-icon">expand_more</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="create_invoices.php" class="submenu-link">
                        <span class="material-icons-round">add_circle</span>
                        <span class="text ms-2"><?= t('Abertura de Fatura') ?></span>
                    </a>
                </li>
                <li>
                    <a href="list_invoices.php" class="submenu-link">
                        <span class="material-icons-round">list</span>
                        <span class="text ms-2"><?= t('Minhas Faturas') ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <div class="menu-item">
                    <span class="material-icons-round">receipt_long</span>
                    <span class="text ms-2"><?= t('Guias') ?></span>
                </div>
                <span class="material-icons-round dropdown-icon">expand_more</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="guides.php" class="submenu-link">
                        <span class="material-icons-round">add_circle</span>
                        <span class="text ms-2"><?= t('Nova Guida de Transporte') ?></span>
                    </a>
                </li>
                <li>
                    <a href="list_guides.php" class="submenu-link">
                        <span class="material-icons-round">list</span>
                        <span class="text ms-2"><?= t('Minhas Guias') ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Itens -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <div class="menu-item">
                    <span class="material-icons-round">inventory_2</span>
                    <span class="text ms-2"><?= t('Itens') ?></span>
                </div>
                <span class="material-icons-round dropdown-icon">expand_more</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#itemModal" class="submenu-link">
                        <span class="material-icons-round">add_circle</span>
                        <span class="text"><?= t('Novo Item') ?></span>
                    </a>
                </li>
                <li>
                    <a href="items.php" class="submenu-link">
                        <span class="material-icons-round">list</span>
                        <span class="text"><?= t('Meus Itens') ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Empresa/Cliente (antes: Contatos) -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <div class="menu-item">
                    <span class="material-icons-round">contacts</span>
                    <span class="text ms-2"><?= t('Empresa/Cliente') ?></span>
                </div>
                <span class="material-icons-round dropdown-icon">expand_more</span>
            </a>
            <ul class="submenu">
                <li>
                    <a href="register_contact.php">
                        <span class="material-icons-round">add_circle</span>
                        <span class="text"><?= t('Adicionar Empresa/Cliente') ?></span>
                    </a>
                </li>
                <li>
                    <a href="contacts.php" class="submenu-link">
                        <span class="material-icons-round">list</span>
                        <span class="text"><?= t('Empresas/Clientes') ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <hr>
        <h3><?= t('Stock') ?></h3>
        <li>
            <a href="stock.php" class="menu-link">
                <span class="material-icons-round">dashboard</span>
                <span class="text"><?= t('Controle de Stock') ?></span>
            </a>
        </li>
        <hr>

        <!-- RH -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <div class="menu-item">
                    <span class="material-icons-round">badge</span>
                    <span class="text ms-2"><?= t('Recursos Humanos') ?></span>
                </div>
                <span class="material-icons-round dropdown-icon">expand_more</span>
            </a>
            <ul class="submenu">

                <li>
                    <a href="employees.php" class="submenu-link">
                        <span class="material-icons-round">group</span>
                        <span class="text"><?= t('Meus Funcionários') ?></span>
                    </a>
                </li>

                <li>
                    <a href="vacations.php" class="submenu-link">
                        <span class="material-icons-round">event</span>
                        <span class="text"><?= t('Férias / Licenças') ?></span>
                    </a>
                </li>
                <li>
                    <a href="ponto.php" class="submenu-link">
                        <span class="material-icons-round">event</span>
                        <span class="text"><?= t('Registro de Ponto e Faltas') ?></span>
                    </a>
                </li>

                <li>
                    <a href="payroll.php" class="submenu-link">
                        <span class="material-icons-round">receipt_long</span>
                        <span class="text"><?= t('Folha de Pagamento') ?></span>
                    </a>
                </li>

                <li>
                    <a href="positions.php" class="submenu-link">
                        <span class="material-icons-round">receipt_long</span>
                        <span class="text"><?= t('Cargos / Salários') ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <h3><?= t('Perfil') ?></h3>
        <li>
            <a href="perfil.php" class="menu-link">
                <span class="material-icons-round">account_circle</span>
                <span class="text ms-2"><?= t('Perfil') ?></span>
            </a>
        </li>
        <li>
            <a href="list_companies.php" class="menu-link">
                <span class="material-icons-round">business</span>
                <span class="text ms-2"><?= t('Gestão de Empresas') ?></span>
            </a>
        </li>
    </ul>
</div>
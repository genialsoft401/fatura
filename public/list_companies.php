<?php
require_once '../app/views/layout_creation.php';
?>

<body>

    <main>
        <div class="container mt-5">
            <h2 class="mb-4">Gestão de Empresas</h2>
            <div id="companies-list" class="row"></div>
        </div>
    </main>


    <script>
        $(document).ready(function() {
            const regLabel = <?= json_encode(t('CNPJ')) ?>;

            function formatDateAO(iso){
                if(!iso) return '';
                const d = new Date(iso + 'T00:00:00');
                if(isNaN(d.getTime())) return iso;
                return d.toLocaleDateString('pt-PT');
            }

            function loadCompanies() {
                $.ajax({
                    url: "assets/ajax/get_companies.php",
                    method: "GET",
                    dataType: "json",
                    success: function(response) {
                        let companiesHtml = "";
                        if (response.length > 0) {
                            response.forEach(company => {
                                let logoUrl = company.logo_url ? company.logo_url : "assets/img/companies/default.png";

                                companiesHtml += `
                                    <div class="col-md-4">
                                        <div class="card mb-3">
                                            <img src="assets/img/companies/${logoUrl}" class="card-img-top" alt="Logo da ${company.name}" style="height: 150px; object-fit: contain; background: #f8f9fa;">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">${company.name}</h5>
                                                <p class="card-text"><strong>${regLabel}:</strong> ${company.registration_number}</p>
                                               <p class="card-text">
                    <span class="badge ${company.is_active == 1 ? 'bg-success' : 'bg-danger'}">
                        ${company.is_active == 1 ? 'Ativa' : 'Expirada'}
                    </span>
                </p>
                                                <p class="card-text"><strong>Email:</strong> ${company.email}</p>
                                                <p class="card-text"><strong>Plano:</strong> ${company.plan_code || '-'}</p>
                                                <p class="card-text"><strong>Vencimento:</strong> ${formatDateAO(company.plan_expires_at) || '-'}</p>
                                                  <button class="btn  btn-manage" data-id="${company.id}" data-bs-toggle="tooltip" data-bs-placement="top" title="Gestão de Usuários">
                        <i class="material-icons">people</i>
                    </button>
                                               <button class="btn  btn-renew" data-id="${company.id}" data-bs-toggle="tooltip" data-bs-placement="top" title="Renovar">
                        <i class="material-icons">autorenew</i>
                    </button>
                    <button class="btn  btn-edit2" data-id="${company.id}" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
                        <i class="material-icons">edit</i>
                    </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            companiesHtml = `<p class="text-center">Nenhuma empresa encontrada.</p>`;
                        }
                        $("#companies-list").html(companiesHtml);
                    },
                    error: function() {
                        $("#companies-list").html(`<p class="text-danger text-center">Erro ao carregar empresas.</p>`);
                    }
                });
            }

            loadCompanies();


            $(document).on("click", ".btn-edit2", function() {
                let companyId = $(this).data("id");
                window.location.href = `edit_company.php?id=${companyId}`;
            });
            $(document).on("click", ".btn-manage", function() {
                let companyId = $(this).data("id");
                window.location.href = `manage_users.php?company_id=${companyId}`;
            });

            $(document).on("click", ".btn-renew", function() {
                let companyId = $(this).data("id");
                window.location.href = `subscription.php?company_id=${companyId}`;
            });
        });
    </script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>
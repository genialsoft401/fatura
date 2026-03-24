
$(document).ready(function () {
    $('.select2').select2();

    // --- Cropper (Logo da empresa) ---
    let logoCropper = null;
    let logoCropModal = null;
    let croppedLogoBlob = null;

    function ensureLogoModal() {
        if (!logoCropModal) {
            const el = document.getElementById('logoCropModal');
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                throw new Error('Bootstrap/modal não disponível');
            }
            logoCropModal = new bootstrap.Modal(el);
        }
        return logoCropModal;
    }

    function openLogoCropperFromFile(file) {
        const imgEl = document.getElementById('logoCropImage');
        if (!imgEl) return;

        // destroi cropper anterior
        if (logoCropper) {
            logoCropper.destroy();
            logoCropper = null;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            imgEl.src = e.target.result;

            ensureLogoModal().show();

            imgEl.onload = function () {
                logoCropper = new Cropper(imgEl, {
                    // corte livre (sem proporção fixa)
                    aspectRatio: NaN,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    background: false,
                    responsive: true,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: false,
                    minContainerWidth: 320,
                    minContainerHeight: 320,
                });
            };
        };
        reader.readAsDataURL(file);
    }

    function setLogoLoading(isLoading) {
        if (isLoading) {
            $('#companyLogoSpinner').show();
            $('#companyLogo').css('opacity', '0');
        } else {
            $('#companyLogoSpinner').hide();
            $('#companyLogo').css('opacity', '1');
        }
    }

    function setLogoSrc(src) {
        const img = document.getElementById('companyLogo');
        if (!img) return;

        setLogoLoading(true);

        // remove handlers antigos para não duplicar
        img.onload = function () { setLogoLoading(false); };
        img.onerror = function () { setLogoLoading(false); };

        // cache-bust pra evitar quebrar/flash de imagem antiga
        const bust = (src.includes('?') ? '&' : '?') + 't=' + Date.now();
        img.src = src + bust;
    }

    $('#logo').on('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        croppedLogoBlob = null;
        openLogoCropperFromFile(file);
    });

    $('#saveLogoCropBtn').on('click', function () {
        if (!logoCropper) {
            Swal.fire('Erro', 'Nenhuma imagem para cortar.', 'error');
            return;
        }

        const canvas = logoCropper.getCroppedCanvas({
            // tamanho final “seguro” para logo (mantém boa qualidade sem exagerar)
            maxWidth: 1200,
            maxHeight: 1200,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            Swal.fire('Erro', 'Falha ao gerar a imagem.', 'error');
            return;
        }

        canvas.toBlob(function (blob) {
            if (!blob) {
                Swal.fire('Erro', 'Falha ao gerar o arquivo da logo.', 'error');
                return;
            }
            croppedLogoBlob = blob;
            // atualiza a prévia na tela
            const url = URL.createObjectURL(blob);
            setLogoSrc(url);

            ensureLogoModal().hide();
            Swal.fire('Ok', 'Logo ajustada. Agora é só clicar em “Salvar Alterações”.', 'success');
        }, 'image/png', 0.95);
    });


    // Máscara/validação do documento (Angola: NIF)
    const isAngola = (window.APP_LANG || '').toLowerCase() === 'angola';
    if (isAngola) {
        $('#registration_help')
            .text('NIF deve conter apenas números (10 dígitos).')
            .show();

        // força apenas números e limita a 10 dígitos
        $('#registration_number')
            .attr('maxlength', '10')
            .on('input', function () {
                const digits = (this.value || '').replace(/\D/g, '').slice(0, 10);
                this.value = digits;
            });
    }

    let companyId = new URLSearchParams(window.location.search).get('id');
    if (companyId) {
        $.get("assets/ajax/get_company.php", {
            id: companyId
        }, function (response) {
            if (response.success) {
                let selectedCountry = response.data.country || "";
                let selectedCity = response.data.city || "";
                let ddi = [response.data.phone_ddi] || "";

                $.each(response.data, function (key, value) {
                    let field = $('#' + key);
                    if (field.length) {
                        if (field.is('input, textarea')) {
                            field.val(value);
                        } else if (field.is('select')) {
                            field.val(value).trigger('change'); // Para atualizar selects com Select2 ou similares
                            field.select2(); // Reinstancia o Select2
                        } else if (field.is('img')) {
                            field.attr('src', value);
                        }
                    }
                });

                // Tratamento especial para o logo
                setLogoSrc('assets/img/companies/' + response.data.logo_url);

                // Chamar a função selectCountry com os valores carregados
                selectCountry(selectedCountry, selectedCity);
                loadDDI({phone_ddi:ddi});

            } else {
                Swal.fire('Erro', 'Empresa não encontrada', 'error');
            }
        }, 'json');
    }

    $('#editCompanyForm').submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        // Se tiver logo recortada, substitui o upload original
        if (croppedLogoBlob) {
            formData.delete('logo');
            formData.append('logo', croppedLogoBlob, 'logo.png');
        }

        $.ajax({
            url: 'edit_company/ajax/update_company.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire('Sucesso', 'Empresa atualizada com sucesso!', 'success')
                        .then(() => window.location.href = 'list_companies.php');
                } else {
                    Swal.fire('Erro', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Erro', 'Erro ao atualizar empresa', 'error');
            }
        });
    });
});



function selectCountry(selectedCountry = "", selectedCity = "") {
    const username = "israelsouza";
    const countrySelect = $("#country");
    const citySelect = $("#city");

    countrySelect.html('<option value="">Carregando lista de países...</option>').trigger("change");
    citySelect.html('<option value="">Selecione um país primeiro</option>').trigger("change");

    fetch(`https://secure.geonames.org/countryInfoJSON?username=${username}`)
        .then(response => response.json())
        .then(data => {
            if (!data.geonames) throw new Error("API retornou dados inválidos");

            countryMap = {};
            let options = '<option value="">Selecione um país</option>';

            data.geonames.forEach(country => {
                countryMap[country.countryName] = country.geonameId;
                options += `<option value="${country.countryName}">${country.countryName}</option>`;
            });

            countrySelect.html(options).trigger("change");
            countrySelect.select2({
                width: "100%",
                placeholder: "Selecione um país",
                allowClear: false,
                dropdownParent: countrySelect.parent(),
            });

            if (selectedCountry) {
                countrySelect.val(selectedCountry).trigger("change");
                loadCities(selectedCountry, selectedCity);
            }
        })
        .catch(error => {
            console.error("❌ Erro ao carregar países:", error);
            countrySelect.html('<option value="">Erro ao carregar</option>').trigger("change");
        });
}

function loadCities(countryName, selectedCity = "") {
    const citySelect = $("#city");
    const countryId = countryMap[countryName];

    if (!countryId) {
        citySelect.html('<option value="">Selecione um país primeiro</option>').trigger("change");
        return;
    }

    citySelect.html('<option value="">Carregando cidades...</option>').trigger("change");

    fetch(`https://secure.geonames.org/childrenJSON?geonameId=${countryId}&username=israelsouza`)
        .then(response => response.json())
        .then(data => {
            if (!data.geonames) throw new Error("API retornou dados inválidos");

            let options = '<option value="">Selecione uma cidade</option>';
            data.geonames.forEach(city => {
                let isSelected = city.name === selectedCity ? "selected" : "";
                options += `<option value="${city.name}" ${isSelected}>${city.name}</option>`;
            });

            citySelect.html(options).trigger("change");
            citySelect.select2({
                width: "100%",
                placeholder: "Selecione uma cidade",
                allowClear: false,
                dropdownParent: citySelect.parent(),
            });

            if (selectedCity) {
                citySelect.val(selectedCity).trigger("change");
            }
        })
        .catch(error => {
            console.error("❌ Erro ao carregar cidades:", error);
            citySelect.html('<option value="">Erro ao carregar</option>').trigger("change");
        });
}

function loadDDI(selectedDDIs = {}) {
    fetch("assets/ajax/get_countries.php")
        .then(response => response.json())
        .then(data => {
            const ddiOptions = data.map(country => {
                return `<option value="${country.phone}">${country.name} (+${country.phone})</option>`;
            }).join("");

            const ddiFields = ["phone_ddi"];
            ddiFields.forEach(fieldId => {
                const field = $(`#${fieldId}`);
                field.html(`<option value="">Selecione um País</option>` + ddiOptions);

                if (selectedDDIs[fieldId]) {
                    field.val(selectedDDIs[fieldId]).trigger("change");
                }

                field.select2({
                    width: "auto",
                    placeholder: "Selecione um País",
                    allowClear: false,
                    dropdownParent: field.parent(),
                });
            });
        })
        .catch(error => {
            console.error("Erro ao carregar DDIs:", error);
        });
}
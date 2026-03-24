<?php
// AppyPay (Gateway de pagamento)
// IMPORTANTE: não commit/compartilhar client_secret.

// OAuth2 token (v1)
// DEV / Sandbox (TEST)
// (GET/POST com x-www-form-urlencoded)
define('APPYPAY_TOKEN_URL', 'https://login.microsoftonline.com/appypaydev.onmicrosoft.com/oauth2/token');

// Resource (GUID) DEV / Sandbox
define('APPYPAY_RESOURCE', '2aed7612-de64-46b5-9e59-1f48f8902d14');

define('APPYPAY_CLIENT_ID', '0c6a67a1-adc8-44f3-9034-f443c265faad');
define('APPYPAY_CLIENT_SECRET', 'zz68Q~ZBsC-gltz~CCBfL2roy6dHMrDMuOqAsb6t');

// API base (TEST)
define('APPYPAY_API_BASE', 'https://gwy-api-tst.appypay.co.ao/v2.0');

// Payment Methods (copiar do portal AppyPay > Configuração > Webhooks)
// REF_... e GPO_...
define('APPYPAY_METHOD_REF', 'REF_912E7E5E-A75C-45F5-A0DC-E3390F9F8A99');
define('APPYPAY_METHOD_GPO', 'GPO_47DE93C4-23ED-4E26-B369-E666FAA027B6');

// Webhook security (opcional). Se definir, exigimos o token no header X-AppyPay-Webhook-Token
define('APPYPAY_WEBHOOK_TOKEN', '');

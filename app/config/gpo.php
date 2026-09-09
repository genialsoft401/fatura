<?php
// app/config/gpo.php
define('GPO_BASE_URL', getenv('GPO_BASE_URL') ?: 'https://pagamentonline.emis.co.ao/online-payment-gateway/portal');
define('GPO_TOKEN', getenv('GPO_TOKEN') ?: '');       // token fornecido pela EMIS
define('GPO_POS_ID', getenv('GPO_POS_ID') ?: '');     // ID do POS/comerciante
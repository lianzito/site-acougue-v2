<?php
session_start();

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'acougue_db');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false){
    die("ERRO: Não foi possível conectar. " . mysqli_connect_error());
}

mysqli_set_charset($link, "utf8");

// Você pode obter sua chave em: https://www.asaas.com/
define('ASAAS_API_KEY', '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmQyMzU5ZDYyLTQwNTItNDZmOS1hMzJhLTkxM2FlNjcxYmVkODo6JGFhY2hfODIzZDg1YjQtY2Y1ZC00NDcyLWI2ZTQtOGRkOTk4YzY2YmZk');

// Para usar o ambiente de produção, troque 'sandbox.asaas.com' por 'api.asaas.com'
define('ASAAS_API_URL', 'https://sandbox.asaas.com/api/v3');

define('ASAAS_WEBHOOK_SECRET', 'acougue_webhook_2025');

?>
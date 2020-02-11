<?
define('STOP_STATISTICS',	true);
define('NO_AGENT_CHECK',	true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

try {
    $api = new \Api\ApiForms();
    echo $api->run();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
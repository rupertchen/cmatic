<?php
if (isset($_REQUEST['debug'])) {
    header('Content-type: text/html');
} else {
    header('Content-type: text/x-json');
}

require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';

$requestParams = TextUtils::undoMagicQuotes($_REQUEST);

$table = CmaticSchema::getDbName($requestParams['type']);
if (is_null($table)) {
    // TODO: Should probably catch this
    throw new CmaticApiException('Unrecognized type: ' . $requestParams['type']);
}
$conn = PdoHelper::getPdo();
$r = $conn->query(sprintf('select * from %s', $table));
$ret = array();
$ret['records'] = $r->fetchAll(PDO::FETCH_ASSOC);
$conn = null;

echo json_encode($ret);
?>

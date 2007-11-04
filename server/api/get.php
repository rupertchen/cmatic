<?php
if (isset($_REQUEST['debug'])) {
    header('Content-type: text/html');
} else {
    header('Content-type: text/x-json');
}

require_once '../util/Db.php';
require_once '../util/Json.php';

$conn = PdoHelper::getPdo();
$r = $conn->query(sprintf('select * from %s', CmaticSchema::getDbName($_REQUEST['type'])));
$ret = array();
$ret['records'] = $r->fetchAll(PDO::FETCH_ASSOC);

echo Json::encode($ret);
?>

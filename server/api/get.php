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

$type = $requestParams['type'];
$table = CmaticSchema::getTypeDbTable($type);
if (is_null($table)) {
    // TODO: Should probably catch this
    throw new CmaticApiException('Unrecognized type: ' . $requestParams['type']);
}

// Build out the api field names to return
$fieldSelection = array();
foreach (CmaticSchema::getAllFieldsForType($type) as $apiField => $dbColumn) {
    // Quotes to maintain case
    $fieldSelection[] = $dbColumn . ' AS "' . $apiField . '"';
}

// To filter by a specific FK field, both of these parameters
// must be specified.
$filterField = $requestParams['filterField'];
$filterValue = $requestParams['filterValue'];
$filterClause = '';
if (!is_null($filterField) && !is_null($filterValue)) {
    $filterClause = sprintf(' where %s = %d', CmaticSchema::getFieldDbColumn($type, $filterField), $filterValue);
}


$conn = PdoHelper::getPdo();
$r = $conn->query(sprintf('select %s from %s%s', implode(', ', $fieldSelection), $table, $filterClause));
$ret = array();
$ret['records'] = $r->fetchAll(PDO::FETCH_ASSOC);
$conn = null;

echo json_encode($ret);
?>

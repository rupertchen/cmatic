<?php
// TODO: See what is common between this and get.php and refactor.
if (isset($_REQUEST['debug'])) {
    header('Content-type: text/html');
} else {
    header('Content-type: text/x-json');
}

require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';

$requestParams = TextUtils::undoMagicQuotes($_REQUEST);

$typeApiName = $requestParams['type'];
$typeDbTable = CmaticSchema::getTypeDbTable($typeApiName);
if (is_null($typeDbTable)) {
    // TODO: Should probably catch this
    throw new CmaticApiException('Unrecognized type value: ' . $requestParams['type']);
}

// TODO: This could grab a function out of map keyed by name?
// For now it's easy enough to just expect one of these two
$op = $requestParams['op'];
if ($op != 'new' && $op != 'edit' && $op != 'delete') {
    throw new CmaticApiException('Unrecognized op value: ' . $op);
}

$records = json_decode($requestParams['records'], true);
if (is_null($records)) {
    throw new CmaticApiException('Invalid records format: ' . $requestParams['records']);
}

$conn = PdoHelper::getPdo();
$conn->beginTransaction();
if ($op == 'new') {
    // For something new, iterate through the entire row and
    // create two parallel arrays, one for column name, one for value
    foreach ($records as $index => $record) {
        $fieldArray = array();
        $valueArray = array();
        foreach ($record as $fieldApiName => $value) {
            if ($fieldApiName == 'id') {
                throw new CmaticApiException(sprintf('Record %d: Id fields cannot be specified when creating a new record', $index));
            }
            $tmpField = CmaticSchema::getFieldDbColumn($typeApiName, $fieldApiName);
            if (is_null($tmpField)) {
                throw new CmaticApiException(sprintf('Record %d: Unrecognized field on type: %s.%s', $index, $typeApiName, $fieldApiName));
            }
            // TODO: check that dates and timestamps data types work
            $tmpValue = (is_int($value) || is_float($value)) ? $value : '\'' . TextUtils::cleanTextForSql($value) . '\'';
            if (is_null($tmpValue)) {
                throw new CmaticApiException(sprintf('Record %d: Invalid value for %s.%s: $s', $index, $typeApiName, $fieldApiName, $value));
            }
            $fieldArray[] = $tmpField;
            $valueArray[] = $tmpValue;
        }
        $conn->query(sprintf('insert into %s (%s) values (%s)', $typeDbTable,
                implode(',', $fieldArray), implode(',', $valueArray)));
        $r = $conn->query(sprintf('select currval(\'%s\')', CmaticSchema::GetIdSeqName($typeApiName)));
        $ret = $r->fetch(PDO::FETCH_NUM);
        $newId = $ret[0];
    }
} else if ($op == 'edit') {
    foreach ($records as $index => $record) {
        if (!array_key_exists('id', $record)) {
            throw new CmaticApiException(sprintf('Record %d: Id field is required when editing a record', $index));
        }
        $recordId = null;
        $setClause = array();
        foreach ($record as $fieldApiName => $value) {
            if ($fieldApiName == 'id') {
                $recordId = strval($value);
                continue;
            }
            $tmpField = CmaticSchema::getFieldDbColumn($typeApiName, $fieldApiName);
            if (is_null($tmpField)) {
                throw new CmaticApiException(sprintf('Record %d: Unrecognized field on type: %s.%s', $index, $typeApiName, $fieldApiName));
            }

            if (is_null($value)) {
                $tmpValue = 'NULL';
            } else if (is_int($value) || is_float($value)) {
                $tmpValue = $value;
            } else {
                $tmpValue = '\'' . TextUtils::cleanTextForSql($value) . '\'';
            }
            if (is_null($tmpValue)) {
                throw new CmaticApiException(sprintf('Record %d: Invalid value for %s.%s: $s', $index, $typeApiName, $fieldApiName, $value));
            }
            $setClause[] = sprintf('%s = %s', $tmpField, $tmpValue);
        }
        // TODO: Move last_mod into triggers?
        $conn->query(sprintf('update %s set last_mod = now(), %s where %s=%s', $typeDbTable,
                implode(',', $setClause), CmaticSchema::getFieldDbColumn($typeApiName, 'id'), $recordId));
    }
} else if ($op == 'delete') { // Disable deletes it's too scary to allow
    // TODO: There needs to be some checks to make sure not just anything can be deleted.
    // Specifically, we should NEVER be able to delete scoring rows if they have scores.
    // Perhaps this should be set up as a trigger?
    $recordIds = array();
    foreach ($records as $index => $record) {
        $recordIds[] = $record['id'];
    }
    $conn->query(sprintf('delete from %s where %s in (%s)', $typeDbTable,
            CmaticSchema::getFieldDbColumn($typeApiName, 'id'), implode(',', $recordIds)));
}
$conn->commit();
$conn = null;

// What should the response be on a successful set-request?
if (isset($newId)) {
    print "{\"success\": \"true\", newId: $newId}";
} else {
    print '{"success": "true"}';
}
?>

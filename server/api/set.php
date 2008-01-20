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
if ($op != 'new' && $op != 'edit') {
    throw new CmaticApiException('Unrecognized op value: ' . $op);
}

$records = json_decode($requestParams['records'], true);
if (is_null($records)) {
    throw new CmaticApiException('Invalid records format: ' . $requestParams['records']);
}

// TODO: Change these ifs into a function array? Maybe, if it turns out they're simple enough
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
            $tmpValue = (is_int($value) || is_float($value)) ? $value : '\'' . TextUtils::cleanTextForSql($value) . '\'';
            if (is_null($tmpValue)) {
                throw new CmaticApiException(sprintf('Record %d: Invalid value for %s.%s: $s', $index, $typeApiName, $fieldApiName, $value));
            }
            $setClause[] = sprintf('%s = %s', $tmpField, $tmpValue);
        }
        // TODO: Move last_mod into triggers?
        $conn->query(sprintf('update %s set last_mod = now(), %s where %s=%s', $typeDbTable,
                implode(',', $setClause), CmaticSchema::getFieldDbColumn($typeApiName, 'id'), $recordId));
    }
}
$conn->commit();
$conn = null;

// What should the response be on a successful set-request?
?>
{success: 'true'}

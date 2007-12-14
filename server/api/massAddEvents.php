<?php
/**
 * Purpose-built to mass create events based on a cross product of the given parameters.
 * @param {String[]} divisions[]
 * @param {String[]} sexes[]
 * @param {String[]} ageGroups[]
 * @param {String[]} forms[]
 * @param {String} debug (optional)
 *
 *
 * TODO: The event type has some quirks. The code field should really be some sort of
 * trigger in the database (or this server) because it's really a function of the
 * other fields. This isn't enforced at the moment, it should be.
 */
$debug = isset($_REQUEST['debug']);
header('Content-type: text/' . (($debug) ? 'html' : 'x-json'));

require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';


/**
 * Updates all event codes
 * TODO: Put this into CmaticSchema?
 * @param $c {PDO Connection}
 * @param $onlyNulls {boolean} True to only update if the code is currently NULL
 */
function updateEventCodes($c, $onlyNulls) {
    // This snippet of SQL is intended to be run within an update where the
    // event db table is available.
    // This could be better, but in Postgres 8.0, we can't alias the update
    // table. This looks to be changed in 8.2.
    $eventDbTable = CmaticSchema::getTypeDbTable('event');
    $selectEventSql = sprintf('select d.short_name || s.short_name || a.short_name || f.short_name as "event_code"'
        . ' from %s d, %s s, %s a, %s f'
        . ' where %s.division_id = d.division_id'
        . ' and %s.sex_id = s.sex_id'
        . ' and %s.age_group_id = a.age_group_id'
        . ' and %s.form_id = f.form_id',
        CmaticSchema::getTypeDbTable('division'),
        CmaticSchema::getTypeDbTable('sex'),
        CmaticSchema::getTypeDbTable('ageGroup'),
        CmaticSchema::getTypeDbTable('form'),
        $eventDbTable, $eventDbTable, $eventDbTable, $eventDbTable
        );
    $whereClause = $onlyNulls ? '' : ' where event_code is null';
    $c->query(sprintf('update %s set event_code = (%s)%s', CmaticSchema::getTypeDbTable('event'), $selectEventSql, $whereClause));
    return true;
}

$isSuccessful = false;

$requestParams = TextUtils::undoMagicQuotes($_REQUEST);
$divisions = $requestParams['divisions'];
$sexes = $requestParams['sexes'];
$ageGroups = $requestParams['ageGroups'];
$forms = $requestParams['forms'];

try {
    if (empty($divisions)) {
        throw new CmaticApiException('Input "divisions[]" cannot be empty.');
    }
    if (empty($sexes)) {
        throw new CmaticApiException('Input "sexes[]" cannot be empty.');
    }
    if (empty($ageGroups)) {
        throw new CmaticApiException('Input "ageGroups[]" cannot be empty.');
    }
    if (empty($forms)) {
        throw new CmaticApiException('Input "forms[]" cannot be empty.');
    }


    // Obtain all of the existing events
    $conn = PdoHelper::getPdo();
    $r = $conn->query(sprintf('select division_id, sex_id, age_group_id, form_id from %s', CmaticSchema::getTypeDbTable('event')));
    $existingEvents = $r->fetchAll(PDO::FETCH_ASSOC);
    // TODO: code

    // Create a cross-product of all 4 inputs
    // Filtering out those that already exist
    $bar = Array("DUMMY"); // TODO: Throw something in here for now so it's not empty

    // Do the mass update if there is something to do
    if (!empty($bar)) {
        $conn->beginTransaction();
        try {
            // Update all event codes that are NULL
            // This will include all the newly added ones and any that were
            // somehow missed before.
            if (!updateEventCodes($conn, true)) {
                // TODO: Throw cmatic exception about the failed update
                throw new CmaticApiException('Arg, update failed');
            }
            $conn->commit();

            // If we got passed the commit, it's all good, report success
            $isSuccessful = true;
        } catch (Exception $e) {
            $conn->rollBack();
            // This should be in a "finally" block, but PHP doesn't have that
            $conn = null;
            throw $e;
        }
    }
    $conn = null;
} catch (Exception $e) {
    // Silence any exceptions because we don't want it showing unless we're in debug mode
    if ($debug) {
        throw $e;
    }
}

?>
{success: <?php echo ($isSuccessful ? 'true' : 'false'); ?>}

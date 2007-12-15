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
 * Hash the event row to a unique string.
 * Use a concatenation of the division, sex, age, and
 * form ids joined by a '-'. This will be unique as a
 * '-' can never appear as part of and id.
 *
 * @param {assoc. array} $r Represents the event row from the database
 */
function hashEvent($d, $s, $a, $f) {
    return $d . '-' . $s . '-' . $a . '-' . $f;
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
    // TODO: Consider checking for dupes in each parameter as well.


    // Obtain all of the existing events and build up a map for
    // simple dupe checking.
    $eventDbTable = CmaticSchema::getTypeDbTable('event');
    $conn = PdoHelper::getPdo();
    $r = $conn->query(sprintf('select division_id, sex_id, age_group_id, form_id from %s', $eventDbTable));
    $resultSet = $r->fetchAll(PDO::FETCH_ASSOC);
    $existingEvents = array();
    foreach ($resultSet as $row) {
        $existingEvents[hashEvent($row['division_id'], $row['sex_id'], $row['age_group_id'], $row['form_id'])] = true;
    }

    // Create a cross-product of all 4 inputs
    // Filtering out those that already exist
    $newEvents = array();
    foreach ($divisions as $d) {
        foreach ($sexes as $s) {
            foreach ($ageGroups as $a) {
                foreach ($forms as $f) {
                    if (!$existingEvents[hashEvent($d, $s, $a, $f)]) {
                        $newEvents[] = array('d'=>$d, 's'=>$s, 'a'=>$a, 'f'=>$f);
                    }
                }
            }
        }
    }

    // Do the mass update if there is something to do
    if (!empty($newEvents)) {
        $conn->beginTransaction();
        try {
            foreach ($newEvents as $e) {
                // TODO: Consider using binds instead
                $conn->query(sprintf('insert into %s (division_id, sex_id, age_group_id, form_id) values(%d, %d, %d, %d)',
                    $eventDbTable, $e['d'], $e['s'], $e['a'], $e['f']));
            }

            // Update all event codes that are NULL
            // This will include all the newly added ones and any that were
            // somehow missed before.
            if (!CmaticSchema::updateEventCodes($conn, true)) {
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
    } else {
        // Got here because we had nothing to do.
        // We'll consider that a success.
        $isSuccessful = true;
    }
    $conn = null;
} catch (Exception $e) {
    // Silence any exceptions because we don't want it showing unless we're in debug mode
    if ($debug) {
        throw $e;
    }
}

// TODO: need to have a standard response for all API calls
?>
{success: <?php echo ($isSuccessful ? 'true' : 'false'); ?>}

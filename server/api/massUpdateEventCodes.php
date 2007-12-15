<?php
/**
 * Update all of the event codes of all existing events.
 */

$debug = isset($_REQUEST['debug']);
header('Content-type: text/' . (($debug) ? 'html' : 'x-json'));

require_once '../util/Db.php';

$conn = PdoHelper::getPdo();
$isSuccessful = false;
try {
    $conn->beginTransaction();
    CmaticSchema::updateEventCodes($conn, false);
    $conn->commit();
    $isSuccessful = true;
} catch (Exception $e) {
    $conn->rollBack();
    $conn = null;
}
$conn = null;
?>
{success: <?php echo ($isSuccessful ? 'true' : 'false'); ?>}

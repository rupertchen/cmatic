<?php
/**
 * This page is entirely hardcoded. It magically knows all of
 * the things it needs to know about the configuration data.
 */

require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';

$byDivision = array();
$bySex = array();
$byType = array();

$eventTable = CmaticSchema::getTypeDbTable('event');
$conn = PdoHelper::getPdo();
$r = $conn->query("select num_competitors, division_id, sex_id, age_group_id, form_id"
    . " from $eventTable");

$resultSet = $r->fetchAll(PDO::FETCH_ASSOC);
$conn = null;

$ret = array();
foreach ($resultSet as $row) {
    $num = $row['num_competitors'];
    $age = strval($row['age_group_id']);

    if (!isset($byDivision[$age])) {
        $byDivision[$age] = array('1' => 0, '2' => 0, '3' => 0, '4' => 0);
    }
    if (!isset($bySex[$age])) {
        $bySex[$age] = array('1' => 0, '2' => 0, '3' => 0);
    }
    if (!isset($byType[$age])) {
        $byType[$age] = array('t' => 0, 'c' => 0, 'i' => 0);
    }

    $byDivision[$age][strval($row['division_id'])] += $num;
    $bySex[$age][strval($row['sex_id'])] += $num;
    $byType[$age][convertForm($row['form_id'])] += $num;
}

print '<table><thead><th>Age</th>'
    . '<th>N</th><th>B</th><th>I</th><th>A</th>'
    . '<th>N</th><th>M</th><th>F</th>'
    . '<th>T</th><th>C</th><th>I</th>'
    . '</thead><tbody>';

$ageGroups = array(1 => 'Y', 2 => 'C', 3 => 'T', 4 => 'A(W)', 5 => 'S(W)', 6 => 'A(I)', 7 => 'S(I)', 8 => 'N');
foreach ($ageGroups as $ageId => $label) {
    printf('<tr><th>%s</th>'
        . '<td>%d</td><td>%d</td><td>%d</td><td>%d</td>'
        . '<td>%d</td><td>%d</td><td>%d</td>'
        . '<td>%d</td><td>%d</td><td>%d</td>'
        . '</tr>',
        $label,
        $byDivision[$ageId]['1'], $byDivision[$ageId]['2'], $byDivision[$ageId]['3'], $byDivision[$ageId]['4'],
        $bySex[$ageId]['1'], $bySex[$ageId]['2'], $bySex[$ageId]['3'],
        $byType[$ageId]['t'], $byType[$ageId]['c'], $byType[$ageId]['i']);
    $totals[0] += $byDivision[$ageId]['1'];
    $totals[1] += $byDivision[$ageId]['2'];
    $totals[2] += $byDivision[$ageId]['3'];
    $totals[3] += $byDivision[$ageId]['4'];
    $totals[4] += $bySex[$ageId]['1'];
    $totals[5] += $bySex[$ageId]['2'];
    $totals[6] += $bySex[$ageId]['3'];
    $totals[7] += $byType[$ageId]['t'];
    $totals[8] += $byType[$ageId]['c'];
    $totals[9] += $byType[$ageId]['i'];
}

print '<tr><th>Total</th>';
foreach ($totals as $total) {
    print "<td>$total</td>";
}
print '</tr>';

print '</tbody></table>';

function convertForm($formId) {
    if (1 <= $formId && $formId <= 6) {
        return 't';
    } else if (7 <= $formId && $formId <= 18) {
        return 'c';
    } else if (19 <= $formId && $formId <= 32) {
        return 'i';
    }
}
?>
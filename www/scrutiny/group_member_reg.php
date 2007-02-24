<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';


    // Queries
    // Number of registered group forms per competitor
    $q0a = 'SELECT r.competitor_id AS competitor_id, COUNT(r.form_id) AS count'
        . ' FROM cmat_annual.registration r, cmat_enum.form f'
        . ' WHERE r.form_id = f.form_id'
        . ' AND f.is_group = true'
        . ' GROUP BY r.competitor_id';

    // Number of group memberships per competitor
    $q0b = 'SELECT member_id AS competitor_id, COUNT(*) AS count'
        . ' FROM cmat_annual.group_member'
        . ' GROUP BY member_id';

    // Membership forms
    $q1a = 'SELECT g.form_id, gm.member_id'
        . ' FROM cmat_annual."group" g, cmat_annual.group_member gm'
        . ' WHERE g.group_id = gm.group_id';

    // Registered group forms
    $q1b = 'SELECT r.competitor_id, r.form_id'
        . ' FROM cmat_annual.registration r, cmat_enum.form f'
        . ' WHERE r.form_id = f.form_id'
        . ' AND f.is_group = true';

    // Num groups of a form per group member
    $q2 = 'SELECT g.form_id, gm.member_id, count(*) AS count'
        . ' FROM cmat_annual."group" g, cmat_annual.group_member gm'
        . ' WHERE g.group_id = gm.group_id'
        . ' GROUP BY g.form_id, gm.member_id';

    // Groups are associated to group forms
    $q3 = 'SELECT g.group_id, g.form_id'
        . ' FROM cmat_annual."group" g, cmat_enum.form f'
        . ' WHERE g.form_id = f.form_id'
        . ' AND f.is_group = false';


    // Sorted results
    $numRegisteredGroupForms = array();
    $numGroupMemberships = array();
    $groupMemberForms = array();
    $groupFormRegistrations = array();
    $numGroupMembershipsPerForm = array();
    $groupsWithNonGroupForms = array();


    // Database calls
    $conn = Db::connect();

    $r = Db::query($q0a);
    while ($row = Db::fetch_array($r)) {
        $numRegisteredGroupForms[$row['competitor_id']] = $row['count'];
    }
    Db::free_result($r);

    $r = Db::query($q0b);
    while ($row = Db::fetch_array($r)) {
        $numGroupMemberships[$row['competitor_id']] = $row['count'];
    }
    Db::free_result($r);

    $r = Db::query($q1a);
    while ($row = Db::fetch_array($r)) {
        $memberId = $row['member_id'];
        if (!isset($groupMemberForms[$memberId])) {
            $groupMemberForms[$memberId] = array();
        }
        $groupMemberForms[$memberId][] = $row['form_id'];
    }
    Db::free_result($r);

    $r = Db::query($q1b);
    while ($row = Db::fetch_array($r)) {
        $competitorId = $row['competitor_id'];
        if (!isset($groupFormRegistrations[$competitorId])) {
            $groupFormRegistrations[$competitorId] = array();
        }
        $groupFormRegistrations[$competitorId][] = $row['form_id'];
    }
    Db::free_result($r);

    $r = Db::query($q2);
    while ($row = Db::fetch_array($r)) {
        $numGroupMembershipsPerForm[] = $row;
    }
    Db::free_result($r);

    $r = Db::query($q3);
    while ($row = Db::fetch_array($r)) {
        $groupsWithNonGroupForms[] = $row;
    }
    Db::free_result($r);

    Db::close($conn);


    // Checks
    // group form registration count = group memberships count
    $fail0 = array();
    $p0 = 'Competitor %d registered for %d group forms, but a member of %d groups.';
    foreach ($numRegisteredGroupForms as $cId => $regCount) {
        $membershipCount = $numGroupMemberships[$cId];
        if ($membershipCount != $regCount) {
            $fail0[$cId] = sprintf($p0, $cId, $regCount, $membershipCount);
        }
    }
    foreach ($numGroupMemberships as $cId => $membershipCount) {
        $regCount = $numRegisteredGroupForms[$cId];
        if ($membershipCount != $regCount) {
            $fail0[$cId] = sprintf($p0, $cId, $regCount, $membershipCount);
        }
    }

    // group form membership = group member form registration
    $fail1 = array();
    $p1a = 'Competitor %d is in a group for form %d, but not registered for it.';
    $p1b = 'Competitor %d is registered for form %d, but not in a group for it.';
    foreach ($groupMemberForms as $cId => $memberFormIdList) {
        $regFormIdList = $groupFormRegistrations[$cId];
        if (!is_array($regFormIdList)) {
            $regFormIdList = array();
        }
        $diff0 = array_diff($memberFormIdList, $regFormIdList);
        foreach ($diff0 as $k => $fId) {
            $fail1[$cId.'_'.$fId] = sprintf($p1a, $cId, $fId);
        }
        $diff1 = array_diff($regFormIdList, $memberFormIdList);
        foreach ($diff1 as $k => $fId) {
            $fail1[$cId.'_'.$fId] = sprintf($p1b, $cId, $fId);
        }
    }
    foreach ($groupFormRegistrations as $cId => $regFormIdList) {
        $memberFormIdList = $groupMemberForms[$cId];
        if (!is_array($memberFormIdList)) {
            $memberFormIdList = array();
        }
        $diff0 = array_diff($memberFormIdList, $regFormIdList);
        foreach ($diff0 as $k => $fId) {
            $fail1[$cId.'_'.$fId] = sprintf($p1a, $cId, $fId);
        }
        $diff1 = array_diff($regFormIdList, $memberFormIdList);
        foreach ($diff1 as $k => $fId) {
            $fail1[$cId.'_'.$fId] = sprintf($p1b, $cId, $fId);
        }
    }

    // no multiple groups of the same form
    $p2 = 'Competitor %d is in %d groups doing form %d.';
    $fail2 = array();
    foreach ($numGroupMembershipsPerForm as $k => $v) {
        $count = $v['count'];
        if ($v['count'] != 1) {
            $cId = $v['competitor_id'];
            $fId = $v['form_id'];
            $fail2[$cId . '_' . $fId] = sprintf($p2, $cId, $count, $fId);
        }
    }

    // groups must be associated to group forms
    $p3 = 'Group %d is associated to the individual form %d.';
    $fail3 = array();
    foreach ($groupsWithNonGroupForms as $k => $v) {
        $gId = $v['group_id'];
        $fId = $v['form_id'];
        $fail3[$gId.'_'.$fId] = sprintf($p3, $gId, $fId);
    }


    // Functions
    function printFailures($failures) {
        if (count($failures) > 0) {
            echo "<pre>\n";
            foreach ($failures as $k => $v) {
                echo $v;
                echo "\n";
            }
            echo "</pre>\n";
        } else {
            echo "Passed.\n";
        }
    }

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Scrutiny: Check Group Member Registration</title>
  </head>
  <body>
    <h1>Scrutiny: Check Group Member Registration</h1>
    <h2>Competitors should be in as many groups as they are registered for group events.</h2>
<?php printFailures($fail0); ?>
    <h2>All group members should be registered for the group's form.</h2>
<?php printFailures($fail1); ?>
    <h2>A competitor is in no more than one group for each form.</h2>
<?php printFailures($fail2); ?>
    <h2>A group is associated to an individiual form.</h2>
<?php printFailures($fail3); ?>
  </body>
</html>

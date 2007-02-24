<?php
    include '../inc/php_header.inc';

    // Imports
    require_once 'util/Db.php';
    require_once 'util/Json.php';
    require_once 'obj/Group.php';
    require_once 'obj/GroupMember.php';

    // Request parameters
    $groupId = $_REQUEST['g'];

    // Fetch data
    $q0 = null;
    $q1 = null;
    if (strlen($groupId) == 0) {
        // Get all groups
        $q0 = 'SELECT * FROM cmat_annual."group" ORDER BY form_id, name';
        $q1 = 'SELECT gm.*, c.first_name, c.last_name FROM cmat_annual.group_member gm, cmat_annual.competitor c WHERE gm.member_id = c.competitor_id';
    } else {
        // Get a specific group
    }
    $groupSet = array();
    $memberList = array();

    $conn = Db::connect();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $tmp = new Group();
        $tmp->fillFromDbRow($row);
        $groupSet[$row['group_id']] = $tmp;
    }
    Db::free_result($r);

    $r = Db::query($q1);
    while ($row = Db::fetch_array($r)) {
        $tmp = new GroupMember();
        $tmp->fillFromDbRow($row);
        $memberList[] = $tmp;
    }
    Db::free_result($r);

    Db::close($conn);

    // Add members to groups
    foreach ($memberList as $k => $v) {
        $groupSet[$v->d['group_id']]->addMember($v);
    }

    $groupList = array();
    foreach ($groupSet as $k => $v) {
        $groupList[] = $v;
    }

    header('Content-type: text/plain');
    include '../inc/php_footer.inc';

    echo Json::encode(array_map(array('Group', 'getData'), $groupList));
?>

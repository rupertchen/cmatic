<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Competitor Info Mailing List</title>
    </head>
    <body>
<?php
// Table with cmatid, competitor full name, url

require_once '../util/Db.php';

$conn = PdoHelper::getPdo();
$r = $conn->query('select competitor_id, last_name, first_name, email from ' . CmaticSchema::getTypeDbTable('competitor'));
$rs = $r->fetchAll(PDO::FETCH_ASSOC);
$conn = null;

$LIST = array();
foreach ($rs as $row) {
    $LIST[] = array('id' => $row['competitor_id'],
                    'name' => "$row[last_name], $row[first_name]",
                    'email' => $row['email']);
}

function makeUrl($id) {
    $hash = str_rot13(md5($id));
    return "http://www.ocf.berkeley.edu/~calwushu/cmat/16/competitorInfo.php?id=$id&hash=$hash";
}

?>
    <table>
        <thead>
            <tr>
                <th scope="col">CMAT ID</th>
                <th scope="col">Competitor Name</th>
                <th scope="col">Email</th>
                <th scope="col">URL</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($LIST as $c) {?>
            <tr>
                <td><?php print $c['id'] ?></td>
                <td><?php print $c['name']; ?></td>
                <td><?php print $c['email']; ?></td>
                <td><?php print makeUrl($c['id']); ?></td>
            </tr>
<?php } ?>
        </tbody>
    </table>
    </body>
</html>
<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/Json.php';


    // Functions
    /**
     * results - array of competitors' results
     * data - data for a single score to be incorporated into the results
     * formGroupSet - array of arrays of forms (grouped by all around qualification)
     */
    function incorporateResults(&$results, $data, $formGroupSet) {
        $competitorId = $data['competitor_id'];
        $formId = $data['form_id'];
        $finalPlacement = $data['final_placement'];

        // Create storage for competitor's data if necessary
        if (!isset($results[$competitorId])) {
            $results[$competitorId] = array(count($formGroupSet));
            foreach ($formGroupSet as $k => $v) {
                $results[$competitorId][$k] = array();
            }
        }

        // Insert data
        foreach ($formGroupSet as $k => $formGroup) {
            if (in_array($formId, $formGroup)) {
                $results[$competitorId][$k][] = array(getPointValue($finalPlacement), $formId);
            }
        }
    }


    /**
     * All-around results for a single competitor
     */
    function filterHasRequiredForms($results) {
        foreach ($results as $k => $v) {
            if (0 == count($v)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Sort the results of each group of forms
     */
    function sortResultGroup (&$group) {
        foreach ($group as $k => $v) {
            $points[$k] = $v[0];
            $form[$k] = $v[1];
        }
        array_multisort($points, SORT_DESC, $form, SORT_ASC, $group);
    }


    /**
     * Pick out the highest points
     * This isn't very smart. To work, it is assuming that the first
     * groups are more restrictive than the latter ones and that the
     * groups have all been sorted.
     */
    function pickBestPoints(&$x) {
        $usedForms = array();
        foreach ($x as $k0 => $eventGroup) {
            $eventFound = false;
            foreach ($eventGroup as $k1 => $pointEventPair) {
                if (!in_array($pointEventPair[1], $usedForms)) {
                    $x[$k0] = array($pointEventPair);
                    $usedForms[] = $pointEventPair[1];
                    $eventFound = true;
                    break;
                }
            }
            if (!$eventFound) {
                $x[$k0] = array();
            }
        }
    }


    /**
     * Pick the form to use for each group.
     */
    function fixResults(&$x) {
        // Drop those without enough events
        // First time to reduce the set early
        $x = array_filter($x, 'filterHasRequiredForms');

        // Sort events by point-value
        foreach ($x as $k0 => $v0) {
            foreach ($v0 as $k1 => $v1) {
                sortResultGroup($x[$k0][$k1]);
            }
        }

        // Pick out the best scores to use
        foreach ($x as $k => $v) {
            pickBestPoints($x[$k]);
        }

        // Drop those without enough events
        // Second time to remove all those that don't qualify
        $x = array_filter($x, 'filterHasRequiredForms');
    }


    /**
     * Make CSV list of form group set
     */
    function makeList($formGroupSet) {
        $ret = array();
        foreach ($formGroupSet as $k => $v) {
            $ret[] = implode(', ', $v);
        }
        return implode(', ', $ret);
    }


    /**
     * Return the point value of a place
     */
    function getPointValue($place) {
        $ret = 0;
        switch ($place) {
            case 1:
                $ret = 3;
                break;
            case 2:
                $ret = 2;
                break;
            case 3:
                $ret = 1;
                break;
        }
        return $ret;
    }


    /**
     * Print out table-ized results
     */
    function printTableizedResults ($results) {
        echo '<table border="1" cellpadding="1" cellspacing="1" style="border-collapse: separate;"><tbody>';
        foreach ($results as $k => $v) {
            echo '<tr><td>' . $k . '</td>';
            $totalPoints = 0;
            foreach ($v as $k1 => $v1) {
                $totalPoints += $v1[0][0];
                echo '<td>'.Json::encode($v1).'</td>';
            }
            echo '<td>'.$totalPoints.'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }


    // Events
    $nanduEvents = array(
        array(17, 18),
        array(19, 20, 21),
        array(22, 23, 24)
    );
    $contEvents = array(
        array(7, 8),
        array(10, 11, 12),
        array(13, 14, 15),
        array(7, 8, 9, 10, 11, 12, 13, 14, 15, 16)
    );
    $tradEvents = array(
        array(1, 2, 3),
        array(4),
        array(5),
        array(1, 2, 3, 4, 5, 6)
    );
    $intEvents = array(
        array(25, 26, 27, 28, 29, 32),
        array(35, 36, 37, 38),
        array(25, 26, 27, 28, 29, 30, 31, 32, 33, 34),
        array(25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38)
    );
    $teenEvents = array(
        array(1, 2, 3, 7, 8, 9),
        array(4, 10, 11, 12),
        array(5, 13, 14, 15),
        array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16)
    );
    $childEvents = array(
        array(1, 2, 3, 7, 8, 9),
        array(4, 10, 11, 12),
        array(5, 13, 14, 15),
        array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16)
    );
    $seniorEvents = array(
        array(1, 2, 3, 7, 8, 9),
        array(4, 10, 11, 12),
        array(5, 13, 14, 15)
    );

    $qBase = 'SELECT s.competitor_id, s.final_placement, fb.gender_id, fb.form_id'
        . ' FROM cmat_annual.scoring s'
        . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
        . ' WHERE s.cmat_year = 15'
        . ' AND fb.cmat_year = 15'
        . ' AND s.is_dropped = false'
        . ' AND fb.level_id = 3';

    $qNandu = $qBase
        . ' AND fb.age_group_id = 4'
        . ' AND fb.form_id in (' . makeList($nanduEvents) . ')';

    $qCont = $qBase
        . ' AND fb.age_group_id = 4'
        . ' AND fb.form_id in (' . makeList($contEvents) . ')';

    $qTrad = $qBase
        . ' AND fb.age_group_id = 4'
        . ' AND fb.form_id in (' . makeList($tradEvents) . ')';

    $qInt = $qBase
        . ' AND fb.form_id in (' . makeList($intEvents) . ')';

    $qTeen = $qBase
        . ' AND fb.age_group_id = 3'
        . ' AND fb.form_id in (' . makeList($teenEvents) . ')';

    $qChild = $qBase
        . ' AND fb.age_group_id = 2'
        . ' AND fb.form_id in (' . makeList($childEvents) . ')';

    $qSenior = $qBase
        . ' AND fb.age_group_id = 6'
        . ' AND fb.form_id in (' . makeList($seniorEvents) . ')';

    // Male / Female Competitors
    $nanduMF = array(1 => array(), 2 => array());
    $contMF = array(1 => array(), 2 => array());
    $tradMF = array(1 => array(), 2 => array());
    $intMF = array(1 => array(), 2 => array());
    $teenMF = array(1 => array(), 2 => array());
    $childCG = array();
    $seniorCG = array();

    // Sort queries
    $conn = Db::connect();
    $r = Db::query($qNandu);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($nanduMF[$row['gender_id']], $row, $nanduEvents);
    }
    Db::free_result($r);

    $r = Db::query($qCont);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($contMF[$row['gender_id']], $row, $contEvents);
    }
    Db::free_result($r);

    $r = Db::query($qTrad);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($tradMF[$row['gender_id']], $row, $tradEvents);
    }
    Db::free_result($r);

    $r = Db::query($qInt);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($intMF[$row['gender_id']], $row, $intEvents);
    }
    Db::free_result($r);

    $r = Db::query($qTeen);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($teenMF[$row['gender_id']], $row, $teenEvents);
    }
    Db::free_result($r);

    $r = Db::query($qChild);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($childCG, $row, $childEvents);
    }
    Db::free_result($r);

    $r = Db::query($qSenior);
    while ($row = Db::fetch_array($r)) {
        incorporateResults($seniorCG, $row, $seniorEvents);
    }
    Db::free_result($r);

    Db::close($conn);


    // Fixing up results
    fixResults($nanduMF[1]);
    fixResults($nanduMF[2]);
    fixResults($contMF[1]);
    fixResults($contMF[2]);
    fixResults($tradMF[1]);
    fixResults($tradMF[2]);
    fixResults($intMF[1]);
    fixResults($intMF[2]);
    fixResults($teenMF[1]);
    fixResults($teenMF[2]);
    fixResults($childCG);
    fixResults($seniorCG);

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>All-Around Champion Rank</title>
    <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="../css/scoring.css"/>
  </head>
  <body>
    <h1>Nandu: Male</h1>
<?php printTableizedResults($nanduMF[1]); ?>
    <h1>Nandu: Female</h1>
<?php printTableizedResults($nanduMF[2]); ?>
    <h1>Adult Contemporary Wushu: Male</h1>
<?php printTableizedResults($contMF[1]); ?>
    <h1>Adult Contemporary Wushu: Female</h1>
<?php printTableizedResults($contMF[2]); ?>
    <h1>Adult Traditional Wushu: Male</h1>
<?php printTableizedResults($tradMF[1]); ?>
    <h1>Adult Traditional Wushu: Female</h1>
<?php printTableizedResults($tradMF[2]); ?>
    <h1>Internal Wushu: Male</h1>
<?php printTableizedResults($intMF[1]); ?>
    <h1>Internal Wushu: Female</h1>
<?php printTableizedResults($intMF[2]); ?>
    <h1>Teen (13-17) Wushu: Male</h1>
<?php printTableizedResults($teenMF[1]); ?>
    <h1>Teen (13-17) Wushu: Female</h1>
<?php printTableizedResults($teenMF[2]); ?>
    <h1>Child (8-12) Wushu: Combined Gender</h1>
<?php printTableizedResults($childCG); ?>
    <h1>Senior (36+) Wushu: Combined Gender</h1>
<?php printTableizedResults($seniorCG); ?>
  </body>
</html>

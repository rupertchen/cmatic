<?php
    include '../inc/php_header.inc';

    // Request parameters
    $ringId = $_REQUEST['r'];

    // Fail fast
    if (!isset($_REQUEST['r']) || $ringId < 1 || $ringId > 8) {
        echo "No valid ring number (r) given.";
        exit;
    }

    // Page values
    $pageTitle = "Ringside Console: Ring " . $ringId;
    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title><?php echo $pageTitle; ?></title>
    <script type="text/javascript" src="../js/mootools.v1.00.js"></script>
    <script type="text/javascript" src="../js/html.js"></script>
    <script type="text/javascript" src="../js/cmat.js"></script>
    <script type="text/javascript" src="../js/ringside_console.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="../css/scoring.css"/>
  </head>
  <body>
    <div id="consoleHeader">
      <h1><?php echo $pageTitle; ?></h1>
    </div>
    <table id="consoleBodyOuter2" border="0" cellpadding="0" cellspacing="0">
      <tr id="consoleBodyOuter1">
        <td>
          <div id="consoleSidebar">
            <div id="ringConfiguration"></div>
            <div id="ringEventList"></div>
          </div>
        </td>
        <td id="consoleBodyInner">
          <div id="consoleContentArea"></div>
        </td>
      </tr>
    </table>
    <script type="text/javascript">
      // Initial data
      var emptyJudge = {"name" : "<Enter Name>"};
      var initialRingConfig = {
        "ring_id" : <?php echo intval($ringId); ?>,
        "type" : 4,
        "judges" : [emptyJudge, emptyJudge, emptyJudge, emptyJudge, emptyJudge, emptyJudge, emptyJudge],
        "ringLeader" : "<Enter Name>"
      };


      // Create modules
      var rc = new RingConfiguration("ringConfiguration", initialRingConfig);
      var rel = new RingEventList("ringEventList", null);

      // Prep AJAX
      var relAjax = new Json.Remote("../query/get_event_summary_list.php?r=<?php echo $ringId; ?>",
          {"onComplete" : function (x) { rel.setData(x); }});

      // AJAX requests
      relAjax.send();
    </script>
  </body>
</html>

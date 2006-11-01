<?php

class Db {

  function connect() {
    $ret = pg_connect("host=localhost"
                      . " dbname=calwushu user=calwushu"
                      . " password=1wuvnaska")
      or die('Could not connect: ' . pg_last_error());
    return $ret;
  }

  function query($q) {
    $ret = pg_query($q) or die('Query failed: ' . pg_last_error() . "::$q");
    return $ret;
  }

  function fetch_array($result) {
    return pg_fetch_array($result, null, PGSQL_ASSOC);
  }

  function free_result($result) {
    pg_free_result($result);
  }

  function close($conn) {
    pg_close($conn);
  }

}

?>

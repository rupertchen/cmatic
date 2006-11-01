<?php
// Prevent any output for now
ob_start();

/****************************************
 * Constants
 */
// Format strings
define("PAGE_INCLUDE", "page/%s.php");

// Globals
define("HTML_TITLE", "HtmlTitle");
define("BODY_CLASS", "BodyClass");
$HtmlTitle = "";
$BodyClass = "";

// Pages
define("CONTACT_DETAIL_PAGE", "ContactDetail");
define("CREATE_PAGE", "Create");
define("EDIT_PAGE", "Edit");
define("LISTING_PAGE", "Listing");
define("LOOKUP_POPUP_PAGE", "LookupPopup");
define("SCHOOL_DETAIL_PAGE", "SchoolDetail");
define("TEST_PAGE", "Test");

// Cookies
define("LISTING_OBJ_COOKIE", "Listing_Obj");
define("LISTING_TYPE_COOKIE", "Listing_Type");

require_once '../.settings';

// Set include path
ini_set("include_path", sprintf("%s:%s", ini_get("include_path"), CODE_PATH));


// Imports
require_once 'util/Db.php';


// Instructions from GET
$t = $_GET['t'];
$f = $_GET['f'];

$conn = Db::connect();


/****************************************
 * Handle Action
 */
$fPage = null;
if (is_null($f)) {
  // Assume no action
} else {
  require_once sprintf(PAGE_INCLUDE, $f);
  if (CREATE_PAGE == $f) {
    $fPage = new Create(true);
  } else if (EDIT_PAGE == $f) {
    $fPage = new Edit(true);
  }
}


/****************************************
 * Handle Target
 */
$targetPage = null;
if (!is_null($fPage) and $fPage->hasError()) {
  $targetPage = $fPage;
} else {
  // Default view is to show the list
  if (is_null($t)) {
    $t = LISTING_PAGE;
  }
  require_once sprintf(PAGE_INCLUDE, $t);
  if (CREATE_PAGE == $t) {
    $targetPage = new Create();
  } else if (CONTACT_DETAIL_PAGE == $t) {
    $targetPage = new ContactDetail();
  } else if (EDIT_PAGE == $t) {
    $targetPage = new Edit();
  } else if (LISTING_PAGE == $t) {
    $targetPage = new Listing();
  } else if (LOOKUP_POPUP_PAGE == $t) {
    $targetPage = new LookupPopup();
  } else if (SCHOOL_DETAIL_PAGE == $t) {
    $targetPage = new SchoolDetail();
  } else if (TEST_PAGE == $t) {
    $targetPage = new Test();
  }
}
Db::close($conn);

// Begin output
require_once 'inc/header.php';
ob_end_flush();
$targetPage->disp();

if (DEBUG_MODE) {
  $targetPage->dispDebug();

  echo "<hr/><h1>d.php debug</h1>";
  echo sprintf('<h2>$_GET</h2><pre>%s</pre>', print_r($_GET, true));
  echo sprintf('<h2>$_POST</h2><pre>%s</pre>', print_r($_POST, true));

  // TMI Debug
  if (false) {
    echo sprintf('<h2>Target Page</h2><pre>%s</pre>', print_r($targetPage, true));
  }
}

require_once 'inc/footer.php';
?>

<?php
    include '../inc/php_header.inc';

    // Request parameters
    $competitorId = $_REQUEST['c'];

    // Page values
    $isNew = strlen($competitorId) == 0;
    $pageTitle = (($isNew) ? "New" : "Edit") . " Registration: " . $competitorId;

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Competitor Registration</title>
    <script type="text/javascript"></script>
    <script type="text/javascript" src="../js/mootools.v1.00.js"></script>
    <script type="text/javascript" src="../js/html.js"></script>
    <script type="text/javascript" src="../js/cmat.js"></script>
    <script type="text/javascript" src="../js/registration.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/registration.css">
    <style type="text/css">
      table {
        width: 100%;
      }

      td {
        vertical-align: top;
      }
    </style>
  </head>
  <body>
    <h1><?php echo $pageTitle; ?></h1>
    <form action="save_competitor_registration.php" method="post">
      <h2>Competitor Information</h2>
<?php if (!$isNew) {?>
      <input id="competitor_id" type="hidden" name="competitor_id" value="<?php echo $competitorId?>"/>
<?php } ?>
      <table border="1" cellpadding="2" cellspacing="1">
        <tbody>
          <tr>
            <th scope="row">First Name:</th>
            <td><input id="first_name" type="text" name="first_name" size="80" maxlength="80"/></td>
          </tr>
          <tr>
            <th scope="row">Last Name:</th>
            <td><input id="last_name" type="text" name="last_name" size="80" maxlength="80"/></td>
          </tr>
          <tr>
            <th scope="row">Sex:</th>
            <td>
              <input id="gender_id_1" type="radio" name="gender_id" value="1"/><label for="gender_id_1">Male</label>
              <input id="gender_id_2" type="radio" name="gender_id" value="2"/><label for="gender_id_2">Female</label>
            </td>
          </tr>
          <tr>
            <th scope="row">Birthdate (yyyy-mm-dd):</th>
            <td>
              <input id="birthdate_year" type="text" name="birthdate_year" size="4" maxlength="4"/>-<input id="birthdate_month" type="text" name="birthdate_month" size="2" maxlength="2"/>-<input id="birthdate_date" type="text" name="birthdate_date" size="2" maxlength="2"/>
            </td>
          </tr>
          <tr>
            <th scope="row">Age Category:</th>
            <td>
              <input id="age_group_id_1" type="radio" name="age_group_id" value="1"/><label for="age_group_id_1">&lt;7</label>
              <input id="age_group_id_2" type="radio" name="age_group_id" value="2"/><label for="age_group_id_2">8-12</label>
              <input id="age_group_id_3" type="radio" name="age_group_id" value="3"/><label for="age_group_id_3">13-17</label>
              <input id="age_group_id_8" type="radio" name="age_group_id" value="8"/><label for="age_group_id_8">18-35</label>
              <input id="age_group_id_9" type="radio" name="age_group_id" value="9"/><label for="age_group_id_9">36-52</label>
              <input id="age_group_id_10" type="radio" name="age_group_id" value="10"/><label for="age_group_id_10">53+</label>
            </td>
          </tr>
          <tr>
            <th scope="row">Division:</th>
            <td>
              <input id="level_id_1" type="radio" name="level_id" value="1"/><label for="level_id_1">Beginner</label>
              <input id="level_id_2" type="radio" name="level_id" value="2"/><label for="level_id_2">Intermediate</label>
              <input id="level_id_3" type="radio" name="level_id" value="3"/><label for="level_id_3">Advanced</label>
            </td>
          </tr>
          <tr>
            <th scope="row">Registration Date:</th>
            <td>
              <input id="registration_date_id_1" type="radio" name="registration_date_id" value="1"/><label for="registration_date_id_1">Early</label>
              <input id="registration_date_id_2" type="radio" name="registration_date_id" value="2"/><label for="registration_date_id_2">Normal</label>
            </td>
          </tr>
          <tr>
            <th scope="row">Registration Type:</th>
            <td>
              <input id="registration_type_id_1" type="radio" name="registration_type_id" value="1"/><label for="registration_type_id_1">Individual</label>
              <input id="registration_type_id_2" type="radio" name="registration_type_id" value="2"/><label for="registration_type_id_2">Bulk</label>
            </td>
          </tr>
          <tr>
            <th scope="row">Submission Format:</th>
            <td>
              <input id="submission_format_id_1" type="radio" name="submission_format_id" value="1"/><label for="submission_format_id_1">Paper</label>
              <input id="submission_format_id_2" type="radio" name="submission_format_id" value="2"/><label for="submission_format_id_2">E-mail</label>
            </td>
          </tr>
        </tbody>
      </table>
      <h2>Event Registration</h2>
      <table border="1" cellpadding="0" cellspacing="0">
        <tr>
          <td><h3>Traditional Hand Forms</h3><table><tbody id="t_h"></tbody></table></td>
          <td><h3>Traditional Weapons Forms</h3><table><tbody id="t_w"></tbody></table></td>
        </tr>
      </table>
      <table border="1" cellpadding="0" cellspacing="0">
        <tr>
          <td><h3>Contemporary Hand Forms</h3><table><tbody id="c_h"></tbody></table></td>
          <td><h3>Contemporary Weapons Forms</h3><table><tbody id="c_w"></tbody></table></td>
        </tr>
      </table>
      <table border="1" cellpadding="0" cellspacing="0">
        <tr>
          <td><h3>Nandu Hand Forms</h3><table><tbody id="n_h"></tbody></table></td>
          <td><h3>Nandu Short Weapons</h3><table><tbody id="n_sw"></tbody></table></td>
          <td><h3>Nandu Long Weapons</h3><table><tbody id="n_lw"></tbody></table></td>
        </tr>
      </table>
      <table border="1" cellpadding="0" cellspacing="0">
        <tr>
          <td><h3>Internal Hand Forms</h3><table><tbody id="i_h"></tbody></table></td>
          <td><h3>Internal Weapons Forms</h3><table><tbody id="i_w"></tbody></table></td>
        </tr>
      </table>
      <table border="1" cellpadding="0" cellspacing="0">
        <tr>
          <td><h3>Push Hands</h3><table><tbody id="p_h"></tbody></table></td>
        </tr>
      </table>
      <input type="submit" value="Send!"/>
    </form>
    <script type="text/javascript">
      /**
       * This page could be refactored so that all of the
       * tables are created from within JS. Then this method
       * would move into that JS and no longer be on this page.
       */
      function populateForm(data) {
        var rList = {};
        for (var form_id in CMAT.formConversion) {
          rList[form_id] = {"form_id":form_id};
        }

        if (data) {
          // If there's data, populate the form

          // Competitor Information
          $("first_name").value = data.first_name;
          $("last_name").value = data.last_name;
          $("gender_id_" + data.gender_id).checked = true;
          $("birthdate_year").value = data.birthdate.substring(0, 4);
          $("birthdate_month").value = data.birthdate.substring(5, 7);
          $("birthdate_date").value = data.birthdate.substring(8, 10);
          $("age_group_id_" + data.age_group_id).checked = true;
          $("level_id_" + data.level_id).checked = true;
          alert(data.registration_date_id);
          $("registration_date_id_" + data.registration_date_id).checked = true;
          $("registration_type_id_" + data.registration_type_id).checked = true;
          $("submission_format_id_" + data.submission_format_id).checked = true;

          // Event Reg
          for (var i = 0; i < data.registration.length; i++) {
            rList[data.registration[i].form_id] = data.registration[i];
          }
        }

        new FormRegistration("t_h", rList[1]);
        new FormRegistration("t_h", rList[2]);
        new FormRegistration("t_h", rList[3]);

        new FormRegistration("t_w", rList[4]);
        new FormRegistration("t_w", rList[5]);
        new FormRegistration("t_w", rList[6]);

        new FormRegistration("c_h", rList[7]);
        new FormRegistration("c_h", rList[8]);
        new FormRegistration("c_h", rList[9]);

        new FormRegistration("c_w", rList[10]);
        new FormRegistration("c_w", rList[11]);
        new FormRegistration("c_w", rList[12]);
        new FormRegistration("c_w", rList[13]);
        new FormRegistration("c_w", rList[14]);
        new FormRegistration("c_w", rList[15]);
        new FormRegistration("c_w", rList[16]);

        new FormRegistration("n_h", rList[17]);
        new FormRegistration("n_h", rList[18]);

        new FormRegistration("n_sw", rList[19]);
        new FormRegistration("n_sw", rList[20]);
        new FormRegistration("n_sw", rList[21]);

        new FormRegistration("n_lw", rList[22]);
        new FormRegistration("n_lw", rList[23]);
        new FormRegistration("n_lw", rList[24]);

        new FormRegistration("i_h", rList[25]);
        new FormRegistration("i_h", rList[26]);
        new FormRegistration("i_h", rList[27]);
        new FormRegistration("i_h", rList[28]);
        new FormRegistration("i_h", rList[29]);
        new FormRegistration("i_h", rList[30]);
        new FormRegistration("i_h", rList[31]);
        new FormRegistration("i_h", rList[32]);
        new FormRegistration("i_h", rList[33]);
        new FormRegistration("i_h", rList[34]);

        new FormRegistration("i_w", rList[35]);
        new FormRegistration("i_w", rList[36]);
        new FormRegistration("i_w", rList[37]);
        new FormRegistration("i_w", rList[38]);

        new FormRegistration("p_h", rList[43]);
        new FormRegistration("p_h", rList[44]);
        new FormRegistration("p_h", rList[45]);
        new FormRegistration("p_h", rList[46]);
        new FormRegistration("p_h", rList[47]);
        new FormRegistration("p_h", rList[48]);
        new FormRegistration("p_h", rList[49]);
      };

      if (<?php echo ($isNew ? "false" : "true"); ?>) {
        new Json.Remote("../query/get_competitor_list.php?c=<?php echo $competitorId; ?>",
            {"onComplete" : function (x) {populateForm(x[0])}}).send();
      } else {
        populateForm();
      }


    </script>
  </body>
</html>
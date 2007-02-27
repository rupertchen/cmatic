<?php
    include '../inc/php_header.inc';

    $recipient = $_REQUEST['r'];
    $competitorId = $_REQUEST['c'];

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Competitor Confirmation</title>
  </head>
  <body>
    <h1>Competitor Confirmation</h1>
    <form action="mail_confirmation.php" method="post">
      <table>
        <tr>
          <th scope="row"><label for="r">E-mail:</label></th>
          <td><input type="text" name="r" id="r" value="<?php echo $recipient; ?>"/></td>
        </tr>
        <tr>
          <th scope="row"><label for="c">Competitor Id:</label></th>
          <td><input type="text" name="c" id="c" value="<?php echo $competitorId; ?>"/></td>
        </tr>
        <tr>
          <td></td
          <td><input type="submit" name="mail" value="Mail Confirmation!"/></td>
        </tr>
      </table>
    </form>
  </body>
</html>

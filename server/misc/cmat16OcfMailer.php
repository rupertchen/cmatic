<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>CMAT16 OCF Mailer</title>
    </head>
    <body>
        <h1>CMAT16 OCF Mailer</h1>
<?php
$id = intval($_REQUEST['id']);
$email = strval($_REQUEST['email']);
if ($_REQUEST['send'] && $id && $email) {

    $hash = str_rot13(md5($id));
    $url = "http://www.ocf.berkeley.edu/~calwushu/cmat16data/competitorInfo.php?id=$id&hash=$hash";

    $to = $email;
    $headers = 'From: cmat16_registration@calwushu.com' . "\n"
        . 'Reply-To: cmat16_registration@calwushu.com' . "\n";
    $subject = '[CMAT16] Competitor Registration Details';
    $body = <<<EOD

This is an automatically generated e-mail from the CMAT16 Registration Team.
Your registration has been received and entered into our system. Please
double-check your details are correct by going to the following URL.

$url

--
CMAT16 Registration


Announcements:
(*) Competitors in traditional straightsword, broadsword, and southern broadsword
events must use weapons that can support their own weight when rested point down
on the ground. More details can be found at
http://www.ocf.berkeley.edu/~calwushu/cmat/16/competitors.php#rules .

(*) Don't forget to check out Li Qiang's seminar. Details can be found online at
http://www.ocf.berkeley.edu/~calwushu/cmat/16/liQiang.php .

(*) There deadline for purchasing shoutouts has been extended to Saturday, March 22nd!. Get yours today!
Find details at http://www.ocf.berkeley.edu/~calwushu/cmat/16/shoutouts.php .

EOD;


    print "<div style=\"border: 1px solid red; margin: 10px 40px; padding: 5px 30px;\">";
    if (mail($to, $subject, $body, $headers)) {
        print "Email sent to <strong>$email</strong> for competitor <strong>$id</strong>.";
    } else {
        print 'Problem sending email.';
    }
    print "</div>";
}
?>
        <p>
            Input just the numeric id of a competitor.
            For example <strong>&ldquo;CMAT16007&rdquo; becomes just &ldquo;7&rdquo;</strong>, not &ldquo;16007&rdquo; and not &ldquo;007&rdquo;.
        </p>
        <form method="post">
            <label for="id">Id:</label><input type="text" id="id" name="id"/><br/>
            <label for="email">Email:</label><input type="text" id="email" name="email"/><br/>
            <input type="submit" name="send" value="Send!"/>
        </form>
    </body>
</html>

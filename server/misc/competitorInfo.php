<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Competitor Registration Details</title>
        <link rel="stylesheet" type="text/css" href="competitorInfo.css"/>
    </head>
    <body>
<?php
    /*
     * This is a one-off page that can be placed on a webseriver without a
     * live DB. Instead, CompetitorInfoData.class.php should contain all
     * of the relevant data.
     *
     * This page should not be necessary once it is possible to send emails
     * from the registration client.
     */

    // request parameters
    $PARAM_CMAT_ID = 'id';
    $PARAM_HASH = 'hash';

    // This file must be included as it contains
    // all of the data provided.
    require_once 'CompetitorInfoData.class.php';

    $DATA = CompetitorInfoData::get();

    ////////////////////////////////////////
    // Short-circuit quit

    if (empty($DATA)) {
        print 'No data found.';
        exit;
    }

    // Check hash
    $CMAT_ID = $_REQUEST[$PARAM_CMAT_ID];
    if ($_REQUEST[$PARAM_HASH] != str_rot13(md5($CMAT_ID))) {
        print 'Invalid or missing hash.';
        exit;
    }


    ////////////////////////////////////////
    // Convenience functions

    function getEventName($event_id) {
        global $DATA;
        return $DATA['events'][$event_id]['name'];
    }


    function getEventCode($event_id) {
        global $DATA;
        return $DATA['events'][$event_id]['code'];
    }


    ////////////////////////////////////////
    // Begin page output
    $DETAILS = $DATA['competitors'][$CMAT_ID];
?>
        <h1><?php print "$DETAILS[last_name], $DETAILS[first_name] ($CMAT_ID)" ?></h1>
        <div class="section">
            <h2>Competition Info</h2>
            <table>
                <tr><th scope="row" class="fieldName">Last Name:</th><td><?php print $DETAILS['last_name']?></td></tr>
                <tr><th scope="row" class="fieldName">First Name:</th><td><?php print $DETAILS['first_name']?></td></tr>
                <tr><th scope="row" class="fieldName">Sex:</th><td><?php print $DETAILS['sex']?></td></tr>
                <tr><th scope="row" class="fieldName">Age:</th><td><?php print $DETAILS['age']?></td></tr>
                <tr><th scope="row" class="fieldName">Division:</th><td><?php print $DETAILS['division']?></td></tr>
                <tr><th scope="row" class="fieldName">Weight:</th><td><?php print $DETAILS['weight']?></td></tr>
                <tr><th scope="row" class="fieldName">Amount Paid:</th><td><?php print $DETAILS['amount_paid'] ?></td></tr>
            </table>
        </div>
        <div class="section">
            <h2>Contact Info</h2>
            <table>
                <tr><th scope="row" class="fieldName">E-Mail:</th><td><?php print $DETAILS['email'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Primary Phone:</th><td><?php print $DETAILS['phone_1'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Secondary Phone:</th><td><?php print $DETAILS['phone_2'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Street Address:</th><td><?php print $DETAILS['street_address'] ?></td></tr>
                <tr><th scope="row" class="fieldName">City:</th><td><?php print $DETAILS['city'] ?></td></tr>
                <tr><th scope="row" class="fieldName">State / Province:</th><td><?php print $DETAILS['state'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Zip / Postal Code:</th><td><?php print $DETAILS['zip'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Country:</th><td><?php print $DETAILS['country'] ?></td></tr>
                <tr><th scope="row" class="fieldName">School:</th><td><?php print $DETAILS['school'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Coach:</th><td><?php print $DETAILS['instructor'] ?></td></tr>
            </table>
        </div>
        <div class="section">
            <h2>Emergency Contact</h2>
            <table>
                <tr><th scope="row" class="fieldName">Name:</th><td><?php print $DETAILS['emergency_contact_name'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Relation:</th><td><?php print $DETAILS['emergency_contact_relation'] ?></td></tr>
                <tr><th scope="row" class="fieldName">Phone:</th><td><?php print $DETAILS['emergency_contact_phone'] ?></td></tr>
            </table>
        </div>
        <div class="section">
            <h2>Individual Events</h2>
            <table class="eventTable">
                <thead>
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Name</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($DETAILS['individual_events'] as $event_id) { ?>
                <tr>
                    <td><?php print getEventCode($event_id); ?></td>
                    <td><?php print getEventName($event_id); ?></td>
                </tr>
<?php } ?>
                </tbody>
            </table>
        </div>
        <div class="section">
            <h2>Special (Group) Events</h2>
            <table class="eventTable">
                <thead>
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Event Name</th>
                        <th scope="col">Group Name</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($DETAILS['group_events'] as $event_id => $group_name) { ?>
                <tr>
                    <td><?php print getEventCode($event_id); ?></td>
                    <td><?php print getEventName($event_id); ?></td>
                    <td><?php print $group_name?></td>
                </tr>
<?php } ?>
                </tbody>
            </table>
        </div>
    </body>
</html>

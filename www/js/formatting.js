
/*
 * If the number has 10 digits and doesn't start with a
 * "+" (international) or a "1", then format it as
 * "(xxx)xxx-xxxx".
 */
function formatPhoneNumber(phone) {
    var ret = null;

    if (0 == phone.indexOf("+")) {
        // Do not format international numbers
    } else {
        var nonDigitsPattern = /[^0-9]/g;
        var digits = phone.replace(nonDigitsPattern, "");

        if (10 == digits.length
          && "1" != digits.substr(0, 1)) {
            // Format 10-digit numbers that don't start with "1"
            var p1 = digits.substr(0, 3);
            var p2 = digits.substr(3, 3);
            var p3 = digits.substr(6, 4);
            ret =  "(" + p1 + ")" + p2 + "-" + p3;
        }
    }

    return (null != ret) ? ret : phone;
}

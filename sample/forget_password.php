<?php

if (isset($_SESSION['uid']) && isset($_SESSION['token'])) {
    header("Location: index.php");
}

require_once dirname(__FILE__, 2) . '/PHPFastAuth.php';
require_once dirname(__FILE__, 1) . '/config.php';

$countryCodeList = ['+91', '+1', '+12', '+2'];


if (isset($_POST['submit'])) {


    try {
        $auth = new PHPFastAuth($db);
        $key;
        if (isset($_POST['mobile'])) {
            $key = $auth->resetPasswordRequestWithMobile($_POST['mobile']);
        } else {
            $key = $auth->resetPasswordRequestWithEmail($_POST['email']);
        }

        $otpData = $auth->decodeOTP($key);

        $title = '';
        if ($otpData->getType() === 'mobile') {
            $title = urlencode("OTP sent to Mobile No. : " . $otpData->getMobile());
        } else {
            $title = urlencode("OTP sent to Email : " . $otpData->getEmail());
        }
        $content = urlencode("Note: For testing purpose the otp is visible on this page. OTP: " . $otpData->getOTP());
        $redirect = urlencode("verify_otp.php?key=" . urlencode($key));
        header("Location: message.php?title=$title&content=$content&redirect=$redirect");
        die();
    } catch (Exception $e) {
        die($e->getMessage());
    }
}
?>

<!DOCTYPE html>

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>

    <form action="" accept-charset="UTF-8" method="post" onsubmit="return validateForm(event);">
        <label for="emailOrMobile">Email or Mobile Number</label>
        <div id="emailMobileContainer" class="input-block">
            <select name="countryCode" class="input-block cc-block" id="countryCode">
                <?php
                foreach ($countryCodeList as $countryCode) {
                    echo "<option>$countryCode</option>";
                }
                ?>
            </select>
            <input type="text" name="emailOrMobile" id="emailOrMobile" autocomplete="off" class="input-block" style="visibility: hidden;">
        </div>

        <input type="submit" name="submit" value="Get OTP" class="btn">
    </form>

    <script>
        window.onload = () => {
            handleCountryCodeVisibility('countryCode', 'emailOrMobile');
        }

        function validateForm(event) {
            const form = event.target;
            const emailOrMobile = form.emailOrMobile.value;

            var isMobile = false;
            var isError = false;
            handleEmailOrMobile(emailOrMobile, (b) => {
                isMobile = b;
            }, (errorCode, message) => {
                alert(message);
                isError = true;
            });
            if (isError) {
                return false;
            }

            /* eveything is now fine */
            if (isMobile) {
                form.emailOrMobile.name = 'mobile';
                form.mobile.value = form.countryCode.value + emailOrMobile;
            } else {
                form.emailOrMobile.name = 'email';
            }
            form.countryCode.remove();
            return true;
        }
    </script>
    <script src="./js/main.js"></script>
</body>

</html>
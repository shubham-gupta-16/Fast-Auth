<?php
session_start();

$countryCodeList = ['+91', '+1', '+12', '+2'];

if (isset($_SESSION['uid']) && isset($_SESSION['token'])) {
    header("Location: index.php");
}

require_once dirname(__FILE__, 2) . '/PHPFastAuth.php';
require_once dirname(__FILE__, 1) . '/config.php';

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $password = $_POST['password'];

    $key;
    try {
        $auth = new PHPFastAuth($db);

        $signUp = null;
        if (isset($_POST['mobile'])) {
            $signUp = new PHPFastAuth\SignUpWithMobile($_POST['mobile']);
        } else {
            $signUp = new PHPFastAuth\SignUpWithEmail($_POST['email']);
        }
        $signUp->setName($name);
        $signUp->setPassword($password);

        $key = $auth->signUpRequest($signUp);


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
        // header("Location: ./index.php");
    } catch (Exception $e) {
        echo $e->getMessage();
        die();
    }
}

?>

<!DOCTYPE html>

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>

    <form action="" accept-charset="UTF-8" method="post" onsubmit="return validateForm(event);">
        <label for="name">Full Name</label>
        <div>
            <input type="text" name="name" id="name" class="input-block" autofocus="autofocus" autocomplete="off">
        </div>

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

        <label for="password">Password</label>
        <input type="password" name="password" id="password" class="input-block">

        <label for="confirmPassword">Confirm Password</label>
        <input type="password" name="confirmPassword" id="confirmPassword" class="input-block">

        <input type="submit" name="submit" value="Sign up" class="btn">
        <br>
        <span>Already a User? </span><a href="./signin.php">Sign in.</a>
    </form>

    <script>
        window.onload = () => {
            handleCountryCodeVisibility('countryCode', 'emailOrMobile');
        }

        function validateForm(event) {
            const form = event.target;

            const name = form.name.value;
            const emailOrMobile = form.emailOrMobile.value;
            const password = form.password.value;
            const confirmPassword = form.confirmPassword.value;


            if (name === '') {
                alert("Please enter your full name.");
                return false;
            }

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

            if (password === '') {
                alert("Please enter password");
                return false;
            }
            if (password.length < 6) {
                alert("Atleast 6-digit password is required");
                return false;
            }
            if (confirmPassword === '') {
                alert("Please enter confirm password");
                return false;
            }
            if (password !== confirmPassword) {
                alert("Password doesn't match");
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
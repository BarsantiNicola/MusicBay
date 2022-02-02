<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    session_start();
    if( isset( $_SESSION[ 'user-id' ]))
        header('Location: ' . 'store.php', true, 301 );
    ?>
    <meta name="author" content="Barsanti Nicola">
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <title>MusicBay</title>

</head>
<body>
<div class="container" id="container">
    <div class="form-container sign-up-container" id="right-panel">
        <form action="#" id="registration-form">
            <h1>Sign Up</h1>
            <input autocomplete="off" type="text" pattern="[A-Za-z0-9]{8,30}" name="username" placeholder="Username" />
            <input autocomplete="off"  type="text" pattern="[0-9]{10}" name="phone" placeholder="Phone Number" />
            <div class="form-line">
                <input autocomplete="new-password" type="password"  pattern="^[\s\S]{8,30}" name="password" placeholder="Password" />
                <img class="password-strength-show" src="img/strength_1.png" />
            </div>

            <input autocomplete="off" id="passwordRepeater" type="password"  pattern="^[\s\S]{8,30}"  name="password-repeat" placeholder="Repeat Password" />
            <button id="sign-up-request" >Sign Up</button>
            <p id="registration-show" class="response-show"></p>
        </form>

        <div id="otp-right-container">
            <form action="#" id="otp-right-form" class="otp-box">
                <h1>OTP Check</h1>
                <span>An authentication token has been sent to your phone number</span>
                <span>To authorize the request please insert the authentication token</span>
                <div class="mb-6 text-center">
                    <div id="otp-right" class="flex justify-center" aria-autocomplete="none">
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="first" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="second" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="third" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="fourth" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="fifth" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="sixth" maxlength="1" />
                    </div>
                </div>
                <input class="hidden-input" type="hidden" name="username">
                <input class="hidden-input" type="hidden" name="password">
                <input class="hidden-input" type="hidden" name="phone">
                <input class="hidden-input" type="hidden" name="type">
                <input class="hidden-input" type="hidden" name="captcha-id">
                <input class="hidden-input" type="hidden" name="captcha-value">
                <input class="hidden-input" type="hidden" name="otp-id">
                <div>
                    <button class="undo-otp-button">Undo</button>
                    <button class="check-otp-button">Confirm</button>
                </div>
            </form>
        </div>
    </div>
    <div class="form-container sign-in-container" id="left-panel">
        <div id="login-panel">
            <form action="#" id="login-form">
                <h1>Sign in</h1>
                <input autocomplete="off" type="text" pattern="[A-Za-z0-9]{8,30}" name="username" placeholder="Username" />
                <input autocomplete="new-password" pattern="^[\s\S]{8,30}" type="password" name="password" placeholder="Password" />
                <a href="#" id="password-linkout">Forgot your password?</a>
                <button id="signIn-linkout">Sign In</button>
                <p id="login-show" class="response-show"></p>
            </form>
            <form action="#" id="password-form">
                <h1>Change Password</h1>
                <input autocomplete="off" type="text" pattern="[A-Za-z0-9]{8,30}" name="username" placeholder="Username" />
                <div class="form-line">
                    <input autocomplete="new-password" pattern="^[\s\S]{8,30}"  type="password" name="password" placeholder="Password" />
                    <img class="password-strength-show" src="img/strength_1.png" />
                </div>
                <input autocomplete="off" id="passwordRepeaterLeft" pattern="^[\s\S]{8,30}"  type="password" name="password-repeat" placeholder="Repeat Password" />
                <div>
                    <button id="password-back" class="password-back-button">Back</button>
                    <button id="password-request">Request</button>
                </div>
                <p id="password-show" class="response-show"></p>
            </form>
        </div>

        <div id="otp-left-container">
            <form action="#" id="otp-left-form" class="otp-box">
                <h1>One Time Password</h1>
                <span>An authentication token has been sent to your phone number</span>
                <span>To authorize the request please insert the authentication token</span>
                <div class="mb-6 text-center">
                    <div id="otp-left" class="flex justify-center">
                        <input autocomplete="off" class="text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="first-1" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="second-2" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="third-3" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="fourth-4" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="fifth-5" maxlength="1" />
                        <input autocomplete="off" class="m-2 text-center form-control form-control-solid rounded focus:border-blue-400 focus:shadow-outline" type="text" id="sixth-6" maxlength="1" />
                    </div>
                </div>
                <input class="hidden-input" type="hidden" name="type">
                <input class="hidden-input" type="hidden" name="username">
                <input class="hidden-input" type="hidden" name="password">
                <input class="hidden-input" type="hidden" name="captcha-id">
                <input class="hidden-input" type="hidden" name="captcha-value">
                <input class="hidden-input" type="hidden" name="otp-id">
                <div>
                    <button class="undo-otp-button">Back</button>
                    <button class="check-otp-button">Confirm</button>
                </div>
            </form>
        </div>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Welcome Back!</h1>
                <p>To keep connected with us please login with your personal info</p>
                <button class="ghost" id="left-linkout">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Hello, Friend!</h1>
                <p>Enter your personal details and start journey with us</p>
                <button class="ghost" id="right-linkout">Sign Up</button>
            </div>
        </div>
    </div>
</div>
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'></script>
<script src="js/connection.js"></script>
<script src="js/crypto.js"></script>
<script src="js/util.js"></script>
<script src="js/login.js"></script>
<script src="bower_components/zxcvbn/dist/zxcvbn.js"></script>
</body>
</html>
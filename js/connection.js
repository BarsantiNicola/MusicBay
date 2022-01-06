//  Request a captcha to the server. The server will reply with a captcha identificator and
//  a set of MIME images to be putted inside a captcha form
//  Request:
//
//  Response:
//            { 
//              'captcha-id': 'as0ad9j01asdna0d123AFnda5s',
//              'captcha-value': 'dnQK2wEas0912xtA',
//              'captcha-clue' : 'Check first column',
//              'captcha-content': { 
//                                    '00': 'captcha/test00.jpg', '10': 'captcha/test10.jpg', '20': 'captcha/test11.jpg', '30': 'captcha/test30.jpg',
//                                    '01': 'captcha/test00.jpg', '11': 'captcha/test10.jpg', '21': 'captcha/test11.jpg', '31': 'captcha/test30.jpg',
//                                    '02': 'captcha/test00.jpg', '12': 'captcha/test10.jpg', '22': 'captcha/test11.jpg', '32': 'captcha/test30.jpg',
//                                    '03': 'captcha/test00.jpg', '13': 'captcha/test10.jpg', '23': 'captcha/test11.jpg', '33': 'captcha/test30.jpg'
//            }
function getCaptcha(){

    let value = null;
    $.ajax({

        type: "GET",
        url: 'php/captcha_logic.php',
        async: false,
        success: function( data ){
            value = data;
        },
        error: function(){
            value = null;
        }
       });

    if( value !== null )
        return JSON.parse( value.substr( 1 ));
    return null;

}

//  Request an OTP check to the server for the given username. The server will reply with an 
//  OTP identificator(like a requestID but to identify OTPs requests)
function getOTP( username ){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/otp_logic.php',
        data:{ "type": "named", "username": username },
        async: false,
        success: function( data ){
            value = data.substr(1);
        },
        error: function(){
            value = null;
        }
    });


    return value;

}

//  Request an OTP check to the server, username is not necessary cause is known by the server. 
//  The server will reply with an OTP identificator(like a requestID but to identify OTPs requests)
function getUnnamedOTP(){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/otp_logic.php',
        data:{ "type": "unnamed" },
        async: false,
        success: function( data ){
            value = data.substr(1);
        },
        error: function(){
            value = null;
        }
    });

    return value;

}

//  Check user credentials to perform access to the store
function login( username, passwordHash, otpID, otpValue ){

    let value = null;

    $.ajax({

        type: "POST",
        url: 'php/frontend_logic.php',
        data:{ "type": "login", "username": username, "password": passwordHash, "otp-id": otpID, "otp-value": otpValue },
        async: false,
        success: function(){
            value = true;
            showMessage( "success", "login", "Login correctly done, you will redirected..", 500 );
        },
        error: function(){
            value = false;
            showMessage( "error", "login", "Something goes wrong with your request..", 5000 );
        }
    });

    return value;

}

//  Add a new user to the service. The user isn't still able to connect to the storage util OTP verification
//  The added user will be removed after 5m if it'snt activated yet
function registration( username, passwordHash, phone, captchaID, captchaValue ){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/frontend_logic.php',
        data:{
            "type": "registration",
            "username": username,
            "password": passwordHash,
            "phone" : phone,
            "captcha-id": captchaID,
            "captcha-value": captchaValue
        },
        async: false,
        success: function(){
            value = true;
        },
        error: function( data ){
            showMessage( "error", "registration", "Something goes wrong with your request..", 5000 );
            value = false;
        }
    });

    return value;

}

//  Confirm the user registration giving its username and the otp sent to the phone number given during registration
function activateUser( username, otpID, otpValue ){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/frontend_logic.php',
        data:{
            "type": "activation",
            "username": username,
            "otp-id": otpID,
            "otp-value": otpValue
        },
        async: false,
        success: function(){
            showMessage( "success", "activation", "Account activated!", 3000 );
            value = true;
        },
        error: function( data ){
            showMessage( "error", "activation", "Something goes wrong with your request..", 5000 );
            value = false;
        }
    });

    return value;

}

//  Change the user's password
function changePassword( username, passwordHash, captchaID, captchaValue, otpID, otpValue ){

    let value = null;

    $.ajax({

        type: "POST",
        url: 'php/frontend_logic.php',
        data:{
            "type": "change_password",
            "username": username,
            "password": passwordHash,
            "captcha-id" : captchaID,
            "captcha-value": captchaValue,
            "otp-id": otpID,
            "otp-value": otpValue
        },
        async: false,
        success: function(){
            showMessage( "success", "password_change", "Password changed!", 3000 );
            value = true;
        },
        error: function(){
            showMessage( "error", "password_change", "Something goes wrong with your request..", 5000 );
            value = false;
        }
    });

    return value;

}

//  Retrieves songslist data from the server
//  Request:
//
//  Reponse: [{   
//               'songID': 1237891,        
//               'title':  'Churirà',
//               'author': 'Hatsune Miku',
//               'album':  'Miku4U',
//               'demo' :  'demos/asdonais.mp3',
//               'price':  '1.99$'
//             },{...
//
//           }]    
function getData( type, selection, filter, page ){

    return [{ 'songID': '123','title': 'Hand in Hand', 'artist':'Hatsune Miku', 'price': '0.99$', 'song': 'demo/hand-in-hand.mp3', 'img':"pics/hand-in-hand.jpg"}];
}

//  Ma
function buySong( songID, ccn, cvv, name, surname, expire, otpID, otpValue ){

    return otpID === 'asdj02ednasd01223' && otpValue === '123456';
}

function downloadSong( songID ){
    alert("downloading start");
}

function disconnect(){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{ "type": "logout" },
        async: false,
        success: function(){
            value = true;
        },
        error: function(){
            value = false;
        }
    });

    return value;

}
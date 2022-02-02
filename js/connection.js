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
        error: function(){
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
        error: function(){
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

    let value = null;

    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
            "type": type,
            "genre": selection,
            "filter": filter,
            "page" : page
        },
        async: false,
        success: function(data){
            value = JSON.parse( data.substr( 1 ).replaceAll( "\\", ""));
        }
    });

    return value;

}

function addToCart( songID ){

    let value = 0;

    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
            "type": 'add_cart',
            "song-id": songID,
        },
        async: false,
        success: function(data){
            showCart(parseInt( data.substr( 1 )))
        },
    });

    return value;
}

function removeFromCart(songID){

    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
            "type": 'remove_cart',
            "song-id": songID,
        },
        async: false,
        success: function(data){
            showCart(parseInt( data.substr( 1 )))
        }

    });
}

function getCart(){

    let value = [];

    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
            "type": 'get_cart'
        },
        async: false,
        success: function(data){
            value = JSON.parse( data.substr( 1 ));

        }
    });
    return value;
}

function makeOrder( data ){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
            "type": "order",
            "CCN": data['ccn'],
            "CVV": data['cvv' ],
            "name": data['name'],
            "surname":data['surname'],
            "card-expire": data['expire']
        },
        async: false,
        success: function(data){

            value = JSON.parse( data.substr( 1 ));
        },
        error: function(){

            value = null;
        }
    });

    return value;
}

//  Ma
function makePayment( transactionId ){

    let value = null;
    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
               "type": "buy",
               "transactionID" : transactionId   //  just for testing purpose
        },
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

function downloadSong( songID, songTitle ){

    $.ajax({

        type: "POST",
        url: 'php/service_logic.php',
        data:{
            "type": "download",
            "song-id": songID
        },
        async: true,
        success: function(data) {

            let blob=new Blob([data]);
            let link=document.createElement('a');
            link.href=window.URL.createObjectURL(blob);
            link.download=songTitle + ".mp3";
            link.click();
        },
    });

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
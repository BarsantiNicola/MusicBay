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
function getCaptcha( type ){

    $.ajax({

        type: "GET",
        url: 'php/captcha_logic.php',

        success: function(data){

            let response = JSON.parse(data.substr(1));
            let identificator = 'captcha_' + type + '_';

            //  insertion of captcha-id information into the captcha form
            document.getElementById( 'captcha-' + type + '-container' ).querySelectorAll( '.hidden-input' ).forEach( input => {
                if( input.name == 'captcha-id' ) input.value = response[ 'captcha-id' ];
            })
            globalThis.captcha_value = response[ 'captcha-value' ];
            document.getElementById( 'captcha-' + type + '-container' ).getElementsByTagName( 'th' )[0].textContent = response[ 'captcha-clue' ];

            //  displaying the images into the captcha form
            for( let y = 0; y<4; y++ )
                for( let x = 0; x<4; x++ )
                    document.getElementById( identificator + y + "_" + x ).src = response['captcha-content'][x+''+y];
            showCaptchaLoad( false );  //  removing the loading icon

        },
        error: function(xhr, status, error){
            console.error(xhr);
        }
       });

}

//  Request an OTP check to the server for the given username. The server will reply with an 
//  OTP identificator(like a requestID but to identify OTPs requests)
function getOTP( username ){
    return 'asdj02ednasd01223';
}

//  Request an OTP check to the server, username is not necessary cause is known by the server. 
//  The server will reply with an OTP identificator(like a requestID but to identify OTPs requests)
function getUnnamedOTP(){
    return 'asdj02ednasd01223';
}

//  Check user credentials to perform access to the store
function login( username, passwordHash, otpID, otpValue ){
    return otpID == 'asdj02ednasd01223' && otpValue == '123456';
}

//  Add a new user to the service. The user isn't still able to connect to the storage util OTP verification
//  The added user will be removed after 5m if it'snt activated yet
function registration( username, passwordHash, phone, captchaID, captchaValue ){
    return captchaID == 'as0ad9j01asdna0d123AFnda5s' && captchaValue == 'nQKwEa091xtA';
}

//  Confirm the user registration giving its username and the otp sent to the phone number given during registration
function activateUser( username, otpID, otpValue ){
    return otpID == 'asdj02ednasd01223' && otpValue == '123456';
};

//  Change the user's password
function changePassword( username, passwordHash, captchaID, captchaValue, otpID, otpValue ){
    return captchaID == 'as0ad9j01asdna0d123AFnda5s' && captchaValue == 'nQKwEa091xtA' && otpID == 'asdj02ednasd01223' && otpValue == '123456';
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
    return otpID == 'asdj02ednasd01223' && otpValue == '123456';
}

function downloadSong( songID ){
    alert("downloading start");
}

function disconnect(){
    alert('disconnecting');
}
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

    $.ajax({
        type: "GET",
        url: 'weblogic/captcha.php',
        success: function(data){
        console.log(data);
        },
        error: function(xhr, status, error){
        console.error(xhr);
        }
       });

    let wait = new Promise((resolve) => setTimeout(resolve, 5000 ));
    return { 
            'captcha-id': 'as0ad9j01asdna0d123AFnda5s',
            'captcha-value': 'dnQK2wEas0912xtA',
            'captcha-clue' : 'Check first column',
            'captcha-content': {       
                                    '00': 'captcha/uYflWPkwugjJtEix00.jpg', '10': 'captcha/uYflWPkwugjJtEix10.jpg', '20': 'captcha/uYflWPkwugjJtEix20.jpg', '30': 'captcha/uYflWPkwugjJtEix30.jpg', 
                                    '01': 'captcha/uYflWPkwugjJtEix01.jpg', '11': 'captcha/uYflWPkwugjJtEix11.jpg', '21': 'captcha/uYflWPkwugjJtEix21.jpg', '31': 'captcha/uYflWPkwugjJtEix31.jpg', 
                                    '02': 'captcha/uYflWPkwugjJtEix02.jpg', '12': 'captcha/uYflWPkwugjJtEix12.jpg', '22': 'captcha/uYflWPkwugjJtEix22.jpg', '32': 'captcha/uYflWPkwugjJtEix32.jpg', 
                                    '03': 'captcha/uYflWPkwugjJtEix03.jpg', '13': 'captcha/uYflWPkwugjJtEix13.jpg', '23': 'captcha/uYflWPkwugjJtEix23.jpg', '33': 'captcha/uYflWPkwugjJtEix33.jpg', 
            }
        };
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
//  Request a captcha to the server. The server will reply with a captcha identificator and
//  a set of MIME images to be putted inside a captcha form
//  Request:
//
//  Response:
//            { 
//              'captcha-id': 'as0ad9j01asdna0d123AFnda5s',
//              'captcha-value': 'dnQK2wEas0912xtA',
//              'captcha-content': { 
//                                    '00': 'captcha/test00.jpg', '10': 'captcha/test10.jpg', '20': 'captcha/test11.jpg', '30': 'captcha/test30.jpg',
//                                    '01': 'captcha/test00.jpg', '11': 'captcha/test10.jpg', '21': 'captcha/test11.jpg', '31': 'captcha/test30.jpg',
//                                    '02': 'captcha/test00.jpg', '12': 'captcha/test10.jpg', '22': 'captcha/test11.jpg', '32': 'captcha/test30.jpg',
//                                    '03': 'captcha/test00.jpg', '13': 'captcha/test10.jpg', '23': 'captcha/test11.jpg', '33': 'captcha/test30.jpg',
//            }
function getCaptcha(){
    let wait = new Promise((resolve) => setTimeout(resolve, 5000 ));
    return { 
            'captcha-id': 'as0ad9j01asdna0d123AFnda5s',
            'captcha-value': 'dnQK2wEas0912xtA',
            'captcha-content': { 
                                    '00': 'captcha/test00.jpg', '10': 'captcha/test10.jpg', '20': 'captcha/test11.jpg', '30': 'captcha/test30.jpg',
                                    '01': 'captcha/test00.jpg', '11': 'captcha/test10.jpg', '21': 'captcha/test11.jpg', '31': 'captcha/test30.jpg',
                                    '02': 'captcha/test00.jpg', '12': 'captcha/test10.jpg', '22': 'captcha/test11.jpg', '32': 'captcha/test30.jpg',
                                    '03': 'captcha/test00.jpg', '13': 'captcha/test10.jpg', '23': 'captcha/test11.jpg', '33': 'captcha/test30.jpg',
            }
        };
}

//  Request an OTP check to the server for the given username. The server will reply with an 
//  OTP identificator(like a requestID but to identify OTPs requests)
function getOTP( username ){
    return 'asdj02ednasd01223';
}

function login( username, passwordHash, otpID, otpValue ){
    return otpID == 'asdj02ednasd01223' && otpValue == '123456';
}

function registration( username, passwordHash, phone, captchaID, captchaValue ){
    return captchaID == 'as0ad9j01asdna0d123AFnda5s' && captchaValue == 'nQKwEa091xtA';
}

function activateUser( username, otpID, otpValue ){
    return otpID == 'asdj02ednasd01223' && otpValue == '123456';
};

function changePassword( username, passwordHash, captchaID, captchaValue, otpID, otpValue ){
    return captchaID == 'as0ad9j01asdna0d123AFnda5s' && captchaValue == 'nQKwEa091xtA' && otpID == 'asdj02ednasd01223' && otpValue == '123456';
}
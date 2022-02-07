const loginFormInputs = document.getElementById( 'login-form' ).getElementsByTagName( 'input' );                //  login inputs
const passwordFormInputs = document.getElementById( 'password-form').getElementsByTagName( 'input' );           //  password change inputs
const registrationFormInputs = document.getElementById( 'registration-form' ).getElementsByTagName( 'input' );  //  registration inputs
const leftPanel = document.getElementById( 'left-panel' );       //  left panel of the page
const rightPanel = document.getElementById( 'right-panel' );     //  right panel of the page

////  PAGE MANAGEMENT

/**  Shows the primary panel of left and right side
 * @param type Can assume 'left' or 'right'[other inputs will be seen as right]
 */
function showPrimaryPanel( type ){

    if( type === 'left' )
        leftPanel.classList.remove( 'left-panel-active' );
    else    
        rightPanel.classList.remove( 'right-panel-active' );

}

/**  Shows the secondary panel of left and right side
 * @param type Can assume 'left' or 'right'[other inputs will be seen as right]
 */
function showSecondaryPanel( type ){

    if( type === 'left' )
        leftPanel.classList.add( 'left-panel-active' );
    else
        rightPanel.classList.add( 'right-panel-active' );

}

/**
 * Shows the password Change Form on the Left Panel
 */
function setToPassword(){

	document.getElementById( 'left-panel' ).classList.add( 'password-panel-active' );

}

/**
 * Shows the login Form on the Left Panel
 */
function setToLogin(){

	document.getElementById( 'left-panel' ).classList.remove( 'password-panel-active' );

}

//  shows messages on the response outputs.
//   - type: define the color of the message. Can be 'success' or 'error'
//   - request: define the type of form in which print the message. Can be 'login', 'registration', 'activation, 'password_change'
//   - time: seconds after the removing of the message
/**
 * Shows messages on the forms
 * @param type     Can be 'success' or 'error'. Other inputs will be seen as 'error'
 * @param request  Form type. Can be 'login', 'registration', 'activation', 'password_change'
 * @param message  Message to be displayed
 * @param time     Seconds before the message will be automatically removed
 */
function showMessage( type, request, message, time ){

    let tag = null;
    switch( request ){  //  three forms available

        case 'login':
            tag = document.getElementById( 'login-show' );
            break;

        case 'registration': case 'activation':
            tag = document.getElementById( 'registration-show' );
            break;

        case 'password_change':
            tag = document.getElementById( 'password-show' );
            break;

        default:
            return;
    }

    if( type === 'success' ){

        tag.classList.remove( 'error' );  //  clean possible remaining tag
        tag.classList.add( 'success' );

    }else{

        tag.classList.remove( 'success' ); //  clean possible remaining tag
        tag.classList.add( 'error' );

    }

    tag.textContent = message;

    //  automatic remove of error message
    window.setTimeout( function(){
        tag.classList.remove( 'success', 'error' );
        tag.textContent = '';
    }, time )

}

////  INPUTS CHECKS

//  input check used for numerical only inputs
function onlyNumberCheck( event ){ return event.keyCode > 47 && event.keyCode < 58; }

//  input check used for text only inputs
function usernameCheck( event ){ return event.keyCode > 32 && event.keyCode < 128; }

//  evaluation of the score of a password by the zxcvbn module
function passwordEvaluation( input, data, event ){

	const form = input.parentNode;
    let dataText = [];
    for( let d in data )
        dataText.push( data.value );

    if( input.value.length === 1 && event.key === 'Backspace' ){  //  empty input => removing the score section

        form.getElementsByTagName("img")[0].src = "img/strength_1.png";
        form.classList.remove("active-report"); 

    }else{

        // username, telefono
        let score = zxcvbn( input.value, dataText ).score;   //  evaluating password score
	    score = score === 0 ? score + 1 : score;    //  merging level 0 and 1 into 0
	    form.getElementsByTagName( 'img' )[0].src = 'img/strength_' + score + '.png'; //  select image to be showed basing on score
	    form.classList.add( 'active-report' );     //  showing password score

    }
}

//  verification of usernames
function usernameVerification( username ){

    if( username.length === 0 )
        return null;

    let match = /^[a-zA-Z0-9_@#]{5,20}$/;

    if( username.search( match ))
        return null;
    else
        return username;
}

//  verification of phone number
function phoneVerification( number ){

    if( number.length !== 10 || number[0] != 3 )
        return null;

    return number;
}

////  LOGIN FORM MANAGEMENT

//  checks the login form inputs and extract their information
function checkLoginForm(){

    let username = null;
    let password = null;

    //  extraction of login fields
    for( let input of loginFormInputs )
        if( input.name === 'username' )
            username = input.value;
        else if( input.name === 'password' )
            password = input.value;
    
    username = usernameVerification( username );  //  check of username
    password = password.length === 0? null : sha256( password ); //  password needs no checks(will be hashed)

    if( username == null || password == null )    //  if some info missing do nothing
        return null;
    else
        return { 'username': username, 'password': password, 'type':'login' };  //  data to be forwarded to the OTP form

}

//  cleans the login form inputs
function cleanLoginForm(){

    for( let input of loginFormInputs ) input.value = '';

}

////   PASSWORD FORM MANAGEMENT

//  checks the password change form inputs and extract their information
function checkPasswordForm(){

    let username = null;
    let oldPassword = null;
    let password = null;
    let password2 = null;

    //  extraction of change password form fields
    for( let input of passwordFormInputs )
        switch( input.name ){

            case 'username':
                username = input.value;
                break;

            case 'password':
                password = input.value;
                break;

            case 'old-password':
                oldPassword = input.value;
                break;

            case 'password-repeat':
                password2 = input.value;
                break;

            default:
                break;            
        }
    
    if( password !== password2 )   //  check password and repeated password are equal
        return null;

    username = usernameVerification( username );                //  check of username 
    password = password.length === 0? null : sha256( password );   //  password needs no checks(will be hashed)
    oldPassword = password.length === 0? null: sha256( oldPassword );

    if( username === null || oldPassword === null || password === null )  //  if some info missing do nothing
        return null;
    else
        return { 'username': username, 'old-password': oldPassword, 'password': password, 'type':'password-change' }; //  data to be forwarded to the otp form

}

//  cleans the password change form inputs
function cleanPasswordForm(){
    
    for( let input of passwordFormInputs ) input.value = '';   //  inputs cleaning

    //  clean password score
    const form = document.getElementById( 'password-form' ).getElementsByClassName( 'form-line' )[0];
    form.getElementsByTagName("img")[0].src = "img/strength_1.png";
    form.classList.remove("active-report"); 
    
}


////  SIGN UP form management

//  checks the registration form inputs and extract their information
function checkRegistrationForm(){

    let username = null;
    let password = null;
    let password2 = null;
    let phone = null;

    for( let input of registrationFormInputs )
        switch( input.name ){

            case 'username':
                username = input.value;
                break;

            case 'password':
                password = input.value;
                break;

            case 'password-repeat':
                password2 = input.value;
                break;
            
            case 'phone':
                phone = input.value;
                break;

            default: break;        
        }

    username = usernameVerification( username );     //  check of username 
    password = password.length === 0? null : sha256( password );  //  password needs no checks(will be hashed)
    password2 = password.length === 0? null : sha256( password2 );  //  password needs no checks(will be hashed)
    phone = phoneVerification( phone );

    if( username == null ) {
        showMessage('error', 'registration', 'Username must be between 5 to 20 characters and can contain only letters and digits', 3000 );
        return null;
    }

    if( password == null ){
        showMessage( 'error', 'registration', 'A password must be set', 3000 );
        return null;
    }

    if( password2 === null || password !== password2 ) {  //  check password and repeated password are equal
        showMessage( 'error', 'registration', 'Passwords must be equal', 3000 );
        return null;
    }

    if( phone == null ){
        showMessage( 'error', 'registration', 'You must insert a valid mobile phone number', 3000 );
        return null;
    }

    return { 'username': username, 'password': password, 'phone': phone, 'type': 'registration' };

}

//  cleans the registration form inputs
function cleanRegistrationForm(){

    for( let input of registrationFormInputs ) input.value = '';   //  inputs cleaning

    //  clean password score
    const form = document.getElementById( 'registration-form' ).getElementsByClassName( 'form-line' )[0];
    form.getElementsByTagName("img")[0].src = "img/strength_1.png";
    form.classList.remove("active-report"); 

}


////  OTP form management

const leftOTPInputs = document.getElementById( 'otp-left' ).querySelectorAll( '.hidden-input' );    //  left otp inputs
const rightOTPInputs = document.getElementById( 'otp-right' ).querySelectorAll( '.hidden-input' );  //  right otp inputs

//  store information into the hidden fields of the otp form and show it
function showOtp( type, data ){

    if( type == 'right' ){
        if( !registration( data['username'], data['password'], data['phone'])){
            showPrimaryPanel( type );
            return;
        }
    }

    let result = getOTP( data['username'] );  //  request to the server of an OTP verification
    if( result == null ){
        showPrimaryPanel( type );
        cleanOTPforms();
        return;
    }


    document.getElementById( 'otp-'+type+'-form' ).querySelectorAll( '.hidden-input' ).forEach( input => {
        switch( input.name ){

            case 'username':
                input.value = data[ 'username' ];
                break;

            case 'password':
                input.value = data[ 'password' ];
                break;

            case 'old-password':
                input.value = data[ 'old-password' ];
                break;

            case 'type':
                input.value = data[ 'type' ];
                break;

            case 'phone':
                input.value = data[ 'phone' ];
                break;

            case 'otp-id':
                input.value = result;
                break;

            default:
                break;

        }
    });

    document.getElementById( 'otp-' + type +'-container' ).classList.add( 'active-otp-form' );  //  enable CSS transition to show OTP form

}

//  checks all the field of the otp are valid
function checkOTPpanel( type ){

    let otpInputs = document.getElementById( 'otp-' + type + '-form' ).getElementsByClassName( 'form-control' );
    for( let input of otpInputs )
        if( input.value.length !== 1 )
            return false;
            
    return true;

}
//  Extracts the data from the otp form and return it as a dictionary
function otpDataExtraction( type ){

    let otpForm = document.getElementById( 'otp-'+type+'-form' );
    let otpInputs = otpForm.getElementsByClassName( 'form-control' );
    let dataInputs = otpForm.getElementsByClassName( 'hidden-input' );
	
	let otpValue = '';
	let data = {};

    for( let input of otpInputs )
        otpValue = otpValue + input.value;
	   
    data['otp-value'] = otpValue;

    for( let input of dataInputs )
        switch( input.name ){

            case 'username':
                data[ 'username' ] = input.value;
                break;
                
            case 'password':
                data[ 'password' ] = input.value;
                break;

            case 'old-password':
                data[ 'old-password' ] = input.value;
                break;

            case 'type':
                data['type'] = input.value;
                break;
            
            case 'phone':
                data[ 'phone' ] = input.value;
                break;

            case 'otp-id':
                data[ 'otp-id' ] = input.value;
                break;
            
            default: 
                break;

        }

    return data;

}

//  cleans the otp form inputs
function cleanOTPforms(){

    document.querySelectorAll( '.form-control' ).forEach( input => input.value = '' );  //  cleaning the otp inputs
    document.querySelectorAll( '.otp-box > .hidden-input' ).forEach( input => input.value = '' );  //  cleaning the stored otp information

}





const loginFormInputs = document.getElementById( 'login-form' ).getElementsByTagName( 'input' );                //  login inputs
const passwordFormInputs = document.getElementById( 'password-form').getElementsByTagName( 'input' );           //  password change inputs
const registrationFormInputs = document.getElementById( 'registration-form' ).getElementsByTagName( 'input' );  //  registration inputs
const leftPanel = document.getElementById( 'left-panel' );       //  left panel of the page
const rightPanel = document.getElementById( 'right-panel' );     //  right panel of the page

////  PAGE MANAGEMENT

//  Shows the primary panel of left and right side
function showPrimaryPanel( type ){

    if( type == 'left' )
        leftPanel.classList.remove( 'left-panel-active' );
    else    
        rightPanel.classList.remove( 'right-panel-active' );

}

//  Shows the secondary panel of left and right side
function showSecondaryPanel( type ){
    if( type == 'left' ){
        leftPanel.classList.add( 'left-panel-active' );
    }else
        rightPanel.classList.add( 'right-panel-active' );   
}

//  shows password change form on left side
function setToPassword(){

	const loginPanel = document.getElementById( 'left-panel' ).classList.add( 'password-panel-active' );

}

//  shows login form on left side
function setToLogin(){

	const loginPanel = document.getElementById( 'left-panel' ).classList.remove( 'password-panel-active' );
}


////  INPUTS CHECKS

//  input check used for numerical only inputs
function onlyNumberCheck( event ){
	return event.keyCode > 47 && event.keyCode < 58;
}

//  input check used for text only inputs
function usernameCheck( event ){
	return event.keyCode > 32 && event.keyCode < 128;
}

//  evaluation of the score of a password by the zxcvbn module
function passwordEvaluation( input, event ){

	const form = input.parentNode;
    
    if( input.value.length == 1 && event.key == 'Backspace' ){  //  empty input => removing the score section

        form.getElementsByTagName("img")[0].src = "img/strength_0.png";
        form.classList.remove("active-report"); 

    }else{

        let score = zxcvbn( input.value ).score;   //  evaluating password score
	    score = score == 0 ? score + 1 : score;    //  merging level 0 and 1 into 0
	    form.getElementsByTagName( 'img' )[0].src = 'img/strength_' + score + '.png'; //  select image to be showed basing on score
	    form.classList.add( 'active-report' );     //  showing password score

    }
}

//  verification of usernames
function usernameVerification( username ){

    if( username.length == 0 )
        return null;

    for( let i = 0; i<username.length; i++ )
        if( username[i]<33 || username[i] > 127 )
            return null;

    return username;        
}

//  verification of phone number
function phoneVerification( number ){

    if( number.length != 10 || number[0] != 3 ) 
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
        if( input.name == 'username' )
            username = input.value;
        else if( input.name == 'password' )
            password = input.value;
    
    username = usernameVerification( username );  //  check of username
    password = password.length == 0? null : sha256(password); //  password needs no checks(will be hashed) 

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

            case 'password-repeat':
                password2 = input.value;
                break;

            default:
                break;            
        }
    
    if( password != password2 )   //  check password and repeated password are equal
        return null;

    username = usernameVerification( username );                //  check of username 
    password = password.length == 0? null : sha256(password);   //  password needs no checks(will be hashed)  

    if( username == null || password == null )  //  if some info missing do nothing
        return null;
    else
        return { 'username': username, 'password': password, 'type':'password-change' }; //  data to be forwarded to the Captcha form

}

//  cleans the password change form inputs
function cleanPasswordForm(){
    
    for( let input of passwordFormInputs ) input.value = '';   //  inputs cleaning

    //  clean password score
    const form = document.getElementById( 'password-form' ).getElementsByClassName( 'form-line' )[0];
    form.getElementsByTagName("img")[0].src = "img/strength_0.png";
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
     
    if( password != password2 )  //  check password and repeated password are equal
        return null;
        
    username = usernameVerification( username );     //  check of username 
    password = password.length == 0? null : sha256(password);  //  password needs no checks(will be hashed)  
    phone = phoneVerification( phone );
       
    if( username == null || password == null || phone == null )   //  if some info missing do nothing
        return null;
    else
        return { "username": username, "password": password, "phone": phone };

}

//  cleans the registration form inputs
function cleanRegistrationForm(){

    for( let input of registrationFormInputs ) input.value = '';   //  inputs cleaning

    //  clean password score
    const form = document.getElementById( 'registration-form' ).getElementsByClassName( 'form-line' )[0];
    form.getElementsByTagName("img")[0].src = "img/strength_0.png";
    form.classList.remove("active-report"); 

}


////  CAPTCHA form management

let captcha_value = new Array(16);  //  global captcha value from which derive the auth key applying captcha_mask
let captcha_mask = '';   //  global mask to be applied on the captcha form[shared by both captcha forms]

//  Loads reaction on images of components of the captchas
function CaptchaLoad(){
	
	const captchas = document.getElementsByClassName( 'captcha_subimg' );

	globalThis.captcha_mask = new Array( 16 );  
	globalThis.captcha_value = '';

	for( let i = 0; i<captchas.length; i++ )
		captchas[i].addEventListener( 'click', function(event){

			globalThis.captcha_mask[i%16] = globalThis.captcha_mask[i%16] == 0? 1 : 0; //  clicking will generate a mask to be applied on the captcha key
			if( this.classList.contains( 'active-captcha' ))  //  showing image clicked[binary logic]
				this.classList.remove( 'active-captcha' );
			else
				this.classList.add( 'active-captcha' );

		});
	
}

//  Generates the captcha key starting from a captcha_value and a mask to be applied on
//  Only character corresponding to a 0 value mask will be concatenated to generate the authentication key
function generateCaptchaValue(){

	let value ="";
	for( let i = 0; i<globalThis.captcha_mask.length; i++ ) 
        if( globalThis.captcha_mask[i] == 0 ){      //  more secure removing the selected box not viceversa(higher key value length)
            value = value + globalThis.captcha_value[i];
            globalThis.captcha_mask[i] = 0;         //  immediately dropping all the info needed to recover the key[low low low security improval but better]
        }
    
	return value;
}

//  Shows the captcha form and inizialize its data
function showCaptcha( type, data ){

    let captchaForm = document.getElementById( 'captcha-' + type + '-container' );
    
    captchaForm.querySelectorAll( '.hidden-input' ).forEach( input => { 
        switch( input.name ){

            case 'username':
                input.value = data[ 'username' ];
                break;
                
            case 'password':
                input.value = data[ 'password' ];
                break;
                
            case 'type':
                input.value = data[ 'type' ];
                break;
                
            case 'phone':
                input.value = data[ 'phone' ];
                break;    

            default:
                break;  

        } 
    });

    document.getElementById( 'otp-' + type +'-container' ).classList.remove( 'active-otp-form' );  // can be present from previous executions
    captchaForm.classList.add( 'active-otp-form' );  //  enable CSS transition to show Captcha Form

}

//  cleans the captcha form inputs
function cleanCaptchaForms(){

	captcha_value = '';  //  resetting the captcha value
    for( let i = 0; i< captcha_mask.length; i++ ) captcha_mask[i] = 0;  //  resetting captcha_mask

    //  resetting captcha images
	document.querySelectorAll( '.captcha_subimg' ).forEach(( captcha ) => { 
		captcha.classList.remove( 'active-captcha' );
		captcha.src = '';
	});

    //  resetting stored information
	document.querySelectorAll( '.captcha-box > .hidden-input' ).forEach( input => input.value = '' );

}

////  OTP form management

const leftOTPInputs = document.getElementById( 'otp-left' ).querySelectorAll( '.hidden-input' );    //  left otp inputs
const rightOTPInputs = document.getElementById( 'otp-right' ).querySelectorAll( '.hidden-input' );  //  right otp inputs

//  store information into the hidden fields of the otp form and show it
function showOtp( type, data ){

    document.getElementById( 'otp-'+type+'-form' ).querySelectorAll( '.hidden-input' ).forEach( input => { 
        switch( input.name ){

            case 'username':
                input.value = data[ 'username' ];
                break;
                
            case 'password':
                input.value = data[ 'password' ];
                break;
                
            case 'type':
                input.value = data[ 'type' ];
                break;
            
            case 'phone':
                input.value = data[ 'phone' ];
                break;

            case 'captcha-id':
                input.value = data[ 'captcha-id' ];
                break;
                
            case 'captcha-value':
                input.value = data[ 'captcha-value' ];
                break;    

            default:
                break;  

            } 
        });
    
    document.getElementById( 'otp-' + type +'-container' ).classList.add( 'active-otp-form' );  //  enable CSS transition to show OTP form

} 

//  cleans the otp form inputs
function cleanOTPforms(){

    document.querySelectorAll( '.form-control' ).forEach( input => input.value = '' );  //  cleaning the otp inputs
    document.querySelectorAll( '.otp-box > .hidden-input' ).forEach( input => input.value = '' );  //  cleaning the stored otp information

}





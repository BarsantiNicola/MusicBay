const signUpButtonReq = document.getElementById('signUpReq');     //  button for registration form request
const container = document.getElementById('container');           //  forms container
const loginPanel = document.getElementById('left-panel');         //  login forms container
const registrationPanel = document.getElementById('right-panel'); //  registration forms container


//  FILE CONTAINING THE CONTROL FLOW OF THE LOGIN PAGE(reaction for buttons, page reactions)
//  ALL THE USED FUNCTIONS ARE STORED INFO THE utils.js, crypto.js AND connection.js FILES

//  wait function for animation improvement
function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}

////  [PAGE LOADING]

document.addEventListener( 'DOMContentLoaded', function(){

	cleanRegistrationForm();
	cleanLoginForm();
	cleanPasswordForm();
	cleanOTPforms();
	PasswordEvaluationLoad();
	OTPLoad();

},  false );


////  [PAGE FLOW]
////  Control flow between the sections of the page


//   MAIN PAGE CONTROL LINKS

const leftLinkOut = document.getElementById( 'left-linkout' );     //  button for moving from the left-page side to the right one
const rightLinkOut = document.getElementById( 'right-linkout' );   //  button for moving from the right-page side to the left one 

////  Moves to the right side of the page when displaying the left side
//
//  Launch Domain: Registration Form, Left OTP form
//  Actions: 
//            - if showing secondary page moves to the primary page
//            - clean secondary page
//            - traslation to left side
rightLinkOut.addEventListener( 'click', (event) => {

	event.preventDefault();	

	if( loginPanel.classList.contains( 'left-panel-active' )){  //  if secondary page displayed
		
		showPrimaryPanel( 'left' );     //  moving to the primary page
		cleanOTPforms();                //  clean eventual pendent information on otp

		sleep(600).then(() => {         //  give some time to conclude the previous animation(changing primary page)
			container.classList.add( 'right-panel-active' );
		});
		
	}else
		container.classList.add( 'right-panel-active' );
	
	sleep(1500).then(()=>{  //  give some time to complete the animation(preventing user to see code activity)
		cleanLoginForm();     //  cleaning eventual login information
		cleanPasswordForm();  //  cleaning eventual password change information	
		setToLogin();         //  resetting the page to login(user maybe was seeing password change form)
	})

});

////  Moves to the left side of the page when displaying the right side
//
//  Launch Domain: Login Form, Password Change Form, Right OTP form
//  Actions: 
//            - if showing secondary page moves to the primary page
//            - clean secondary page
//            - traslation to right side
leftLinkOut.addEventListener( 'click', (event) => {

	event.preventDefault();	
	
	if( registrationPanel.classList.contains( 'right-panel-active' )){   //  if secondary page displayed
		
		showPrimaryPanel( 'right' );       //  moving to the primary page
		cleanOTPforms();                   //  clean eventual pendent information on otp

		sleep(600).then(() => {            //  give some time to conclude the previous animation(changing primary page)
    		container.classList.remove( 'right-panel-active' ); //  clean eventual pendent information on registration form
		});
		
	}else
		container.classList.remove( 'right-panel-active' );	

	sleep(1500).then(() => {               //  give some time to complete the animation(preventing user to see code activity)
		cleanRegistrationForm();
	})	
});


//   SIGN-IN FORM CONTROL FLOW

const signInLinkOut = document.getElementById( 'signIn-linkout' );  	//  button for submit login -> OTP form
const passwordLinkOut = document.getElementById( 'password-linkout' );  //  button for moving to the change password page -> Change Password Form

////  Click on Login Submit form
//
//  Launch Domain: Login Form
//  Actions:
//  		  - checks login information[username,password]
//  		  - execute hash on [password]
//  		  - move to otp form
//  		  - clean login form
signInLinkOut.addEventListener( 'click', (event) => {

	event.preventDefault();
	let credentials = checkLoginForm(); //  returns [username, password_hash, 'login'] in case all information valid

	if( credentials == null )
		return;

	showOtp( 'left', credentials );  //  otp configuration(request of OTP + show form)
	showSecondaryPanel( 'left' );    //  move page to print OTP(OPT loading in background)
	cleanLoginForm();		         //  clean login form inputs

});

////  Click on Forget password button
// 
//   Launch Domain: Login Form
//   Actions:
//			  - changes displayed form to password form
//            - clean login form 
passwordLinkOut.addEventListener( 'click', (event) => {

	event.preventDefault();
	setToPassword();          //  change the displayed page to the password change one
	cleanLoginForm();         //  clean login form inputs 

});


//   PASSWORD FORM CONTROL FLOW

const passwordRequestButton = document.getElementById( 'password-request' );   //  button for request password change
const passwordBackButton = document.getElementById( 'password-back' );     //  button for undo password change

////   Click on Request on the Password Form
//
//   Launch Domain: Password Change Form
//   Actions:
//			   - checks password form information[username,password]
//			   - execute hash on password
//			   - move to otp form
passwordRequestButton.addEventListener( 'click', (event) => {

	event.preventDefault();

	let credentials = checkPasswordForm();   //  get credentials from the form and check them. In case of invalid credentials returns null

	if( credentials == null )
		return;

	showOtp( 'left', credentials );
	showSecondaryPanel( 'left' );         //  scroll the page to make the otp visible
	cleanPasswordForm();                  //  clean the password form's inputs

});

////   Click on Back Button on the Password Form
//
//   Launch Domain: Password Change Form
//   Actions:
//			   - move to login form
//             - clean password form
passwordBackButton.addEventListener( 'click', (event) => {

	event.preventDefault();

	setToLogin();              //  set the displayed page to the login one
	cleanPasswordForm();       //  clean the password form's inputs

});

//   OTP FORMs CONTROL FLOW

const otpUndoButtons =  document.querySelectorAll( '.undo-otp-button' );    // OTP undo buttons[both OTP forms] 
const otpCheckButtons = document.querySelectorAll( '.check-otp-button' );   // OTP Check buttons[both OTP forms]

////   Click on Undo Button on the OTP Forms
//
//   Launch Domain: Left OTP Form, Right OTP Form
//   Actions:
//             - clean stored information
//             - move to primary panel
otpUndoButtons.forEach( button => button.addEventListener('click', function(event){

	event.preventDefault();

	let panel = this.parentNode.parentNode.parentNode.parentNode;  //  useful to obtain the side of the page
	showPrimaryPanel( panel.id.replace( '-panel', ''));            //  display primary page of 'left' or 'right'

	sleep(500).then(() => {   //  giving some time for the form to be hidden by the animation
		cleanOTPforms();      //  cleaning otp stored information
	});

}));

////   Click on Confirm Button on the OTP Forms
//
//   Launch Domain: Left OTP Form, Right OTP Form
//   Actions:
//             - get stored information
//             - clean stored information
//             - make request to the web-server
//             - move to primary panel
otpCheckButtons.forEach( button => button.addEventListener('click', function(event){

	event.preventDefault();

	let side = this.parentNode.parentNode.parentNode.parentNode.id.replace( '-panel', '' ); //  useful to obtain the side of the page

	if( !checkOTPpanel( side ))
		return;

	let data = otpDataExtraction( side );
	switch( data['type'] ){
		case 'login':
			if( login( data['username'], data['password'], data['otp-id'], data['otp-value'] ))
				window.location.href = 'store.php';
			break;
		
		case 'password-change':
			changePassword( data['username'], data['old-password'], data['password'], data['otp-id'], data['otp-value']);
			break;
		
		case 'registration':
			activateUser( data['username'], data['otp-id'], data['otp-value']);
			break;
		
		default: break;	

	}
		
	showPrimaryPanel( side );            //  display primary page of 'left' or 'right'

	sleep(500).then(() => {    //  giving some time for the form to be hidden by the animation
		cleanOTPforms();       //  cleaning otp stored information
	});

}));


//   SIGNUP FORM CONTROL FLOW

const signUpRequest = document.getElementById( 'sign-up-request' );

////   Click on SignUp Button on the Registration From
//
//   Launch Domain: SignUp Form
//   Actions:
//             - check registration form
//             - show secondary page
//             - clean registration form
signUpRequest.addEventListener('click', (event) => {
	
	event.preventDefault();

	let data = checkRegistrationForm();   //  extraction and checking of the registration form inputs

	if( data != null ) {
		showOtp('right', data);
		showSecondaryPanel('right');  //  moving to the secondary page
		cleanRegistrationForm();        //  cleaning the registration form inputs
	}
});


////  BUTTONS AND FUNCTIONALITIES LOAD MANAGEMENT

//  OTP MANAGEMENT

////  General function to load OTP input management on all the page's inputs
function OTPLoad(){

	launchSingleOTP( document.querySelectorAll( '#otp-left > *[id]' ));  //  login/password_change OTP input
	launchSingleOTP( document.querySelectorAll( '#otp-right > *[id]' )); //  registration OTP input

}

//  Function to load OTP management on form's inputs
function launchSingleOTP( inputs ){

	for( let i = 0; i < inputs.length; i++ ){

		inputs[i].value = '';  

		//  each input of the otp is managed by a different eventListener
    	inputs[i].addEventListener( 'keydown', function( event ){
	
			//  DEL button for moving left on the OTP removing data
      		if( event.key === 'Backspace' ) {

        		inputs[i].value = '';
        		if( i > 0 )  
          			inputs[ i - 1] .focus();
				event.preventDefault();  //  prevents the drop of the previous element on focus

      		}else{

        		if( i === inputs.length - 1 && inputs[i].value !== '' )
          			return true; // do nothing but not preventing fast switch on confirm button

				//  only numbers admitted
				if( event.keyCode > 47 && event.keyCode < 58 ) {
          	
					inputs[i].value = event.key;
        			if( i !== inputs.length -1 )
        				inputs[ i + 1 ].focus();
      				event.preventDefault();  //  prevents doubling of the value on the next element on focus
       			
				}else 
         			event.preventDefault();  //  prevents the insertion of the value
        				
      		}
    	});
	}
}


////  PASSWORD EVALUATION 

//  General function to load Password Protection Analysis on all the involved inputs
function PasswordEvaluationLoad(){

	singlePasswordEvaluationLoad( document.getElementById( 'registration-form' ).querySelectorAll( 'input' ), 1 );
	singlePasswordEvaluationLoad( document.getElementById( 'password-form' ).querySelectorAll( 'input' ), 0 );

}

//  Function to start listener for input and password analysis from an input using zxcvbn
function singlePasswordEvaluationLoad(inputs, type){

	let data = [];
	for( let i = 0; i< inputs.length; i++ ){
		switch( inputs[i].name ){
			case "username":
				data.push( inputs[i] );
				break;
			case "phone":
				data.push( inputs[i] );
				break;
			default:
				break;
		}
	}

	for( let i = 0; i < inputs.length; i++ ){

		if( inputs[i].name === "password")
			inputs[i].addEventListener('keydown', function( event ){

				// input management(delete current input and move backward on the inputs)
				if( event.key === "Backspace" && inputs[i].value === '' && i>0 ){
					inputs[i-1].focus();
					event.preventDefault();
					return;
				}else
					if( event.key === "Backspace" )  //  just deleting input, leaving the management to event.default
						return;

				passwordEvaluation( inputs[i], data, event );  //  checking password with zxcvbn

				if( event.key === "Enter" ){  //  input management(move forward on inputs or on the button)
					if( i === inputs.length -1 ){
						if( type === 0 )
							passwordRequestButton.focus();
						else
							signUpButtonReq.focus();
					}else
						inputs[i+1].focus();

					event.preventDefault();
				}
				
			});
		else	
			inputs[i].addEventListener('keydown', function(event){

				// input management(delete current input and move backward on the inputs)
				if( event.key === "Backspace" && inputs[i].value === '' && i>0 ){
					inputs[i-1].focus();
					event.preventDefault();
					return;
				}else
					if( event.key === "Backspace" )  //  just deleting input, leaving the management to event.default
						return;

				if( event.key === "Enter" ){  //  input management(move forward on inputs or on the button)
					if( i === inputs.length -1 ){
						if( type === 0 )
							passwordRequestButton.focus();
						else
						signUpButtonReq.focus();
					}else
						inputs[i+1].focus();	
					event.preventDefault();
					return;
				}
				if( inputs[i].name === "phone" && !onlyNumberCheck(event) ||
						inputs[i].name === "username" && !usernameCheck(event))
					event.preventDefault();
			});
	}
}




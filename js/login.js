const signUpButton = document.getElementById('signUp');                  //  button for registration form transition
const signInButton = document.getElementById('signIn');                  //  button for login form transition
const signUpButtonReq = document.getElementById('signUpReq');            //  button for registration form request
const signInButtonReq = document.getElementById('signInReq'); 		 //  button for login form request
const signUpButtonConfirm = document.getElementById('signUpConfirm');    //  button for login request OTP confirmation
const signInButtonConfirm = document.getElementById('signInConfirm');    //  button for registration request OTP confirmation
const signUpButtonUndo = document.getElementById('signUpUndo');          //  button for login request undo
const signInButtonUndo = document.getElementById('signInUndo');          //  button for registration request undo
const passwordButtonUndo = document.getElementById('signUpUndoHidden');
const container = document.getElementById('container');                  //  forms container
const loginPanel = document.getElementById('login-panel');               //  login forms container
const loginFormPanel = document.getElementById('sign-form-panel');
const registrationPanel = document.getElementById('registration-panel'); //  registration forms container
const passwordButton = document.getElementById('change-password');

//  wait function for animation improve
function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}

function setToPasswordChange(){
	loginFormPanel.getElementsByTagName("h1")[0].innerHTML = "Change Password";
	loginFormPanel.getElementsByTagName("button")[0].textContent = "Change It";
	loginFormPanel.getElementsByTagName("a")[0].style.display = "none";
	loginFormPanel.getElementsByTagName("button")[0].style.display = "inline";
}

function setToLoginChange(){
	loginFormPanel.getElementsByTagName("h1")[0].innerHTML = "Sign In";
	loginFormPanel.getElementsByTagName("button")[0].textContent = "Sign In";
	loginFormPanel.getElementsByTagName("a")[0].style.display = "inline";
	loginFormPanel.getElementsByTagName("button")[0].style.display = "none";
}

//  PAGE LOADING

document.addEventListener('DOMContentLoaded', function(){ 
	OTPLoad();
	PasswordEvaluationLoad();
	signUpClear();
	OTPClear();
}, false);

//  BUTTONS REACTIONS

signUpButton.addEventListener('click', () => {
	if( loginPanel.classList.contains("login-panel-active")){
		
		loginPanel.classList.remove("login-panel-active");
		sleep(600).then(() => {
			container.classList.add("right-panel-active");
		});
		
	}else
		container.classList.add("right-panel-active");
	
});

signInButton.addEventListener('click', () => {
	setToLoginChange();
	if( registrationPanel.classList.contains("registration-panel-active")){
	
		registrationPanel.classList.remove("registration-panel-active");
		sleep(600).then(() => {
    			container.classList.remove("right-panel-active");
		});
		
	}else
		container.classList.remove("right-panel-active");
	
});

signInButtonUndo.addEventListener('click', () => {
	loginPanel.classList.remove("login-panel-active");
});

signUpButtonUndo.addEventListener('click', () => {
	registrationPanel.classList.remove("registration-panel-active");
});

signUpButtonReq.addEventListener('click', (event) => {
	if(signUpCheck())
		registrationPanel.classList.add("registration-panel-active");	
	event.preventDefault();
});

signInButtonReq.addEventListener('click', () => {
	loginPanel.classList.add("login-panel-active");
});

signUpButtonConfirm.addEventListener('click', () => {
	registrationPanel.classList.remove("registration-panel-active");
});

signInButtonConfirm.addEventListener('click', () => {
	loginPanel.classList.remove("login-panel-active");
});

passwordButton.addEventListener('click', () => {
	setToPasswordChange();
});

passwordButtonUndo.addEventListener('click', () => {
	setToLoginChange();
});


//  OTP MANAGEMENT

//  Function for OTP input management. Launches a set of listeners for each digit of the OTP input
//  Permits to check the input and realize the overall behavior of the OTP presentation
//
//     - inputs: the list of input elements composing the OTP
//
function launchSingleOTP( inputs ){

	for( let i = 0; i < inputs.length; i++ ){

		inputs[i].value = '';  

		//  each input of the otp is managed by a different eventListener
    	inputs[i].addEventListener( 'keydown', function( event ){
	
			//  DEL button for moving left on the OTP removing data
      		if( event.key === "Backspace" ) {

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

function OTPClear(){

	document.querySelectorAll( '#otp > *[id]' ).forEach( elem => elem.value = '' )
	document.querySelectorAll( '#otp2 > *[id]' ).forEach( elem => elem.value = '' )

}

function OTPLoad(){

	launchSingleOTP( document.querySelectorAll( '#otp > *[id]' ));  //  login/password_change OTP input
	launchSingleOTP( document.querySelectorAll( '#otp2 > *[id]' )); //  registration OTP input

}

function PasswordEvaluationLoad(){
	
	const inputs = document.getElementById("registration-form").querySelectorAll('input');
	for( let i = 0; i < inputs.length; i++ ){
		if( inputs[i].name == "password")
			inputs[i].addEventListener('keydown', function(event){

				if( event.key === "Backspace" && inputs[i].value == '' && i>0 ){
					inputs[i-1].focus();
					event.preventDefault();
					return;
				}
				passwordEvaluation(inputs[i]);	
				if( event.key === "Enter" ){
					if( i == inputs.length -1 )
						signUpButtonReq.focus();
					else
						inputs[i+1].focus();

					event.preventDefault();
					return;
				}
				
			});
		else	
			inputs[i].addEventListener('keydown', function(event){
				if( event.key === "Backspace" && inputs[i].value == '' && i>0 ){
					inputs[i-1].focus();
					event.preventDefault();
					return;
				}else
					if( event.key === "Backspace" )
						return;

				if( event.key === "Enter" ){
					if( i == inputs.length -1 )
						signUpButtonReq.focus();
					else
						inputs[i+1].focus();	
					event.preventDefault();
					return;
				}
				if( inputs[i].name == "phone" && !onlyNumberCheck(event) ||
						inputs[i].name == "username" && !usernameCheck(event))
					event.preventDefault();
			});
	}
}

function passwordEvaluation( input ){

	const form = document.getElementById("registration-form");
	let score = zxcvbn(input.value).score;
	score = score == 0 ? score+1: score;
	form.getElementsByTagName("img")[0].src = "img/strength_"+score+".png";
	form.getElementsByClassName("form-line")[0].classList.add("active-report");

}

function signUpClear(){
	document.getElementById("registration-form").querySelectorAll('input').forEach( input => input.value = '' );
}

function onlyNumberCheck(event){
	return event.keyCode > 47 && event.keyCode < 58;
}

function usernameCheck(event){
	return event.keyCode > 32 && event.keyCode < 128;
}

function signUpCheck(){
	const inputs = document.getElementById("registration-form").querySelectorAll('input');
	const passwordRepeated = document.getElementById("passwordRepeater").value;

	for( let i = 0; i<inputs.length; i++ ){
		if( inputs[i].value.length == 0 ) return false;
		switch( inputs[i].name ){
			case "password":
				if( inputs[i].value != passwordRepeated ){
					//  SET ERROR ON PAGE
					return false;
				}
				break;

			case "phone":
				let number = inputs[i].value;
				if( number.length != 10 || number[0]!='3'){
					//  SET ERROR ON PAGE
					return false;
				}	
				break;
			default:
				break;	
		}
	}
	return true;

}



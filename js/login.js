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

signUpButtonReq.addEventListener('click', () => {
	registrationPanel.classList.add("registration-panel-active");
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

function OTPInput() {
  const inputs = document.querySelectorAll('#otp > *[id]');
  for (let i = 0; i < inputs.length; i++) {
    inputs[i].addEventListener('keydown', function(event) {
      alert("event " + event.key);	
      if (event.key === "Backspace" && i > 1) {
        inputs[i].value = '';
        if (i > 2)
          inputs[i - 1].focus();
          
      } else {
        if (i === inputs.length - 1 && inputs[i].value !== '') {
          return true;
        } else if (event.keyCode > 47 && event.keyCode < 58) {
          inputs[i].value = event.key;
          inputs[i].textContent = event.key;
          if (i !== inputs.length - 1)
            inputs[i + 1].focus();
          event.preventDefault();
        } else if (event.keyCode > 64 && event.keyCode < 91) {
          inputs[i].value = String.fromCharCode(event.keyCode);
          if (i !== inputs.length - 1)
            inputs[i + 1].focus();
          event.preventDefault();
        }
      }
    });
  }
}

OTPInput();


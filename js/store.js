const storeBox = document.getElementById( 'store-box' );
const paymentPanel = document.getElementById( 'pay-panel' );
const otpForm = document.getElementById( 'otp-form' );
const otpMsg = document.getElementById( 'otp-msg' );
const appContainer = document.getElementById( 'container' );
const popup = document.getElementsByClassName( 'payment-popup' )[0];
const paymentForm = document.getElementsByClassName( 'payment-form' )[0];

setTimeout(function(){
    document.body.classList.remove( 'preload' );
},1000);


////  [PAGE LOADING]

document.addEventListener( 'DOMContentLoaded', function(){

	loadOTP();

},  false );


function showLoadPage( state ){
    if( state )
        storeBox.classList.remove("load");
    else
        storeBox.classList.add("load");    
}

////   PAYMENT FORM MANAGEMENT

function showPopup( songID ){

    paymentForm.getElementsByClassName( '.hidden-input' )[0].value = songID;
    popup.classList.remove( 'disable' );

}

function hidePopup(){

    popup.classList.add( 'disable' ); 
    cleanPaymentForm();

}

document.getElementById( 'retry-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    hidePopup();
    cleanPaymentForm();

})

document.getElementById( 'pay-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    let data = checkPaymentForm();
    if( data == null )
        return;
    
    showOTP( data );
    cleanPaymentForm();

})

document.querySelector('.input-ccn').addEventListener('input', function() { //Using input event for instant effect
    let text=this.value                                                      //Get the value
    text=text.replace(/\D/g,'')                                              //Remove illegal characters
    if(text.length>3) text=text.replace(/.{4}/,'$&-')                        //Add hyphen at pos.4
    if(text.length>7) text=text.replace(/.{9}/,'$&-')                        //Add hyphen at pos.8
    if(text.length>7) text=text.replace(/.{14}/,'$&-')                        //Add hyphen at pos.8
    this.value=text;                                                         //Set the new text
});

document.querySelector('.input-cvv').addEventListener('input', function() { //Using input event for instant effect
    let text=this.value                                                      //Get the value
    text=text.replace(/\D/g,'')                                              //Remove illegal characters
    this.value=text;                                                         //Set the new text
});

function checkPaymentForm(){

    let data = {};
    let inputs =  paymentForm.getElementsByTagName( 'input' );

    for( let input of inputs )
        switch( input.name ){
            case 'ccn':
                data['ccn'] = checkCCN( input.value );
                if( data['cnn'] == null ) return null;
                break;

            case 'cvv':
                data['cvv'] = checkCVV( input.value );
                if( data['cvv'] == null ) return null;
                break;

            case 'name':
                if( input.value.length > 0 )
                    data['name'] = input.value;
                else
                    return null;    
                break;

            case 'surname':
                if( input.value.length > 0 )
                    data['surname'] = input.value;
                else
                    return null;    
                break;

            case 'expire':
                data['expire'] = input.value;
                break;

            case 'songID':
                if( input.value.length > 0 )
                    data['songID'] = input.value;
                else
                    return null; 
                break;

            default:
                data[input.name] = input.value;                        
        }

    return data;
}

function checkCCN( value ){

    if( value.length != 19 ) return null;
    value = value.replaceAll( '-', '' );
    if( /^\d+$/.test( value ) )
        return value;
    return null;  

}

function checkCVV( value ){
    
    if( value.length != 3 ) return null;
    return /^\d+$/.test( value )? value : null;

}

function cleanPaymentForm(){
    paymentForm.querySelectorAll( 'input' ).forEach( input => input.value = '' );
}

////  OTP MANAGEMENT

////   Click on Undo Button on the OTP Form
//
//   Actions:
//             - clean stored information
//             - move to primary panel
document.getElementById( 'undo-otp-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    hideOTP();

})

////   Click on Confirm Button on the OTP Form
//
//   Actions:
//             - get the stored information
//             - make request to the server
//             - after reply show a message
//             - after 5s close the payment panel
//             - hide OTP form
document.getElementById( 'check-otp-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    let data = checkOTPform();

    if( data == null ){
        showOTPerror( 'All the OTP fields must be present');
        return;
    }
    if( buySong( data['ccn'], data['cvv'], data['name'], data['surname'], data['expire'])){
        showOTPok();
        setTimeout( function(){
            hideOTP();
            hidePaymentPanel();
        }, 3000 );
    }else{    
        showOTPerror( 'Error, invalid OTP' );
        setTimeout( function(){
            hideOTP();
        }, 2000 );
    }
})

//  Function to load OTP management on form's inputs
function loadOTP(){

    let inputs = otpForm.querySelectorAll( '.form-control' );
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

//  Shows the OTP panel and put the given data into the form hidden-inputs
function showOTP( data ){

    paymentPanel.querySelectorAll( '.hidden-input' ).forEach( input => input.value = input.name != 'otp-id'? data[input.name] : getUnnamedOTP());
    paymentPanel.classList.add( 'active-otp-form' );

}

function showOTPok(){
    cleanOTPmessage();
    otpMsg.textContent = 'Request correctly done';
    otpMsg.classList.add( 'ok' );
}

function cleanOTPmessage(){
    otpMsg.classList.remove( 'ok' );
    otpMsg.classList.remove( 'error' );
}

function showOTPerror( message ){
    cleanOTPmessage();
    otpMsg.textContent = message;
    otpMsg.classList.add( 'error' );
}

//  Checks the validity of the OTP input. In case of success it will return the OTP code otherwise null
function checkOTPform(){

    let otpValue = '';
    let inputs = otpForm.getElementsByClassName( 'form-control' );
    for( let input of inputs )
        if( input.value.length != '1' ) return null;
        else otpValue = otpValue + input.value;
    return otpValue;
}

//  Cleans all the stored information and hide the OTP panel restoring the payment form
function hideOTP(){
    
    document.getElementById( 'hidden-pay-songID' ).value = document.getElementById( 'hidden-otp-songID' ).value;
    paymentPanel.classList.remove( 'active-otp-form' );
    cleanOTPform();

}

//  Cleans all the store information into the OTP panel
function cleanOTPform(){

    otpForm.querySelectorAll( 'input' ).forEach( input => input.value = '' );

}

function hidePaymentPanel(){

}
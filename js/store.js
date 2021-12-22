const storeBox = document.getElementById( 'store-box' );             //  music container
const paymentPanel = document.getElementById( 'pay-panel' );         //  payment panel containing payment form and otp
const otpForm = document.getElementById( 'otp-form' );               //  otp form container
const otpMsg = document.getElementById( 'otp-msg' );                 //  response message inserted into the otp form
const appContainer = document.getElementById( 'container' );         //  container of the web application
const popup = document.getElementsByClassName( 'payment-popup' )[0]; //  secondary popup containing the payment panel
const paymentForm = document.getElementsByClassName( 'payment-form' )[0];   //  payment form inside popup

//  prevents animations to be displayed after a page load
setTimeout( function(){
    document.body.classList.remove( 'preload' );  //  with preload active => animation-times = 0
} , 1000);  //  time necessary to the scroll animation to be completed


////  [PAGE LOADING]


let requestSemaphore = false;  //  semaphore for access control on music loading(only one request at a time)

document.addEventListener( 'DOMContentLoaded', function(){
    requestSemaphore = true;     //  cleaning all the resources
    cleanMusicContainer();
    cleanFilters();
    cleanOTPform();
    cleanPaymentForm();
	loadOTP();                   //  loading of otp event handlers on otp form
    makeDefaultSearch();         //  putting buyed songs into the home folder
    showLoadPage( false );       //  display the home folder removing the loading panel
    requestSemaphore = false;    //  enable requests to the server
},  false );

////   Click on logout button 
//
//   Actions:
//             - notify the server to drop the session
document.getElementById( 'exit-button' ).addEventListener( 'click', function(event){
    disconnect();
})

//  shows a popup containing the payment and otp forms
function showPopup( songID ){

    paymentForm.getElementsByClassName( 'hidden-input' )[0].value = songID;
    popup.classList.remove( 'disable' );

}

//  removes the popup displaying the main page
function hidePopup(){

    popup.classList.add( 'disable' ); 
    cleanPaymentForm();

}

//  shows a loading panel on the music box for hide the music-box displayment
function showLoadPage( state ){
    if( state )
        storeBox.classList.remove( 'load' );
    else
        storeBox.classList.add( 'load' );    
}


////   [SONG PANEL MANAGEMENT]

const musicContainer = document.getElementById( 'music-container' );   //  container in which deploy songs
const filterContainer = document.getElementById( 'filter-container' ); //  container for the filtered song search


////   Click on home button on the filter container
//
//   Actions:
//             - check semaphore and in case undo request
//             - show loading panel
//             - clean the listed songs and stored information
//             - store newly search information
//             - execute default song search
//             - load songs on music container
//             - remove loading panel
document.getElementById( 'home-search' ).addEventListener( 'click', function(event){

    event.preventDefault();
    if( requestSemaphore ) return; //  one request a time
    
    requestSemaphore = true;       // <-  MUTUAL EXCLUSION
    showLoadPage( true );          //  hide music box
    setTimeout( function(){

        cleanMusicContainer();     //  clean listed songs and stored information
        makeDefaultSearch();       //  execute request to server and load retrieved data
        showLoadPage( false );     //  remove loading page
        requestSemaphore = false;  //  <- MUTUAL EXCLUSION

    }, 1500 );   //  required delay to give to the loading page animation enough time to complete

});

////   Click on search button on the filter container
//
//   Actions:
//             - check semaphore and in case undo request
//             - show loading panel
//             - extract filter inputs
//             - clean the listed songs and stored information
//             - store newly search information
//             - execute song search
//             - load songs on music container
//             - remove loading panel
document.getElementById( 'filtred-search' ).addEventListener( 'click', function(event){

    event.preventDefault();
    if( requestSemaphore ) return;  //  one request a time

    requestSemaphore = true;        //  <- MUTUAL EXCLUSION
    showLoadPage( true );           //  hide music box
    setTimeout( function(){

        cleanMusicContainer();      //  clean listed songs and stored information
        makeSearch();               //  execute request to server and load retrieved data
        showLoadPage( false );      //  remove loading page
        requestSemaphore = false;   //  <- MUTUAL EXCLUSION

    }, 1500 );   //  required delay to give to the loading page animation enough time to complete
});

////   Click on left arrow on the music container to move forward the song list
//
//   Actions:
//             - check semaphore and in case undo request
//             - show loading panel
//             - extract stored information
//             - clean the listed songs and stored information
//             - save updated search information
//             - execute song search
//             - load songs on music container
//             - remove loading panel
document.getElementById( 'left-arrow' ).addEventListener( 'click', function(event){

    event.preventDefault();
    if( requestSemaphore ) return;   //  one request a time

    requestSemaphore = true;         //  <- MUTUAL EXCLUSION
    showLoadPage( true );            //  hide music box
    setTimeout( function(){

        let data = extractContainerData();   //  extracting stored info
        cleanMusicContainer();       //  clean listed songs and stored information
        storeMusicData( data['type'], data['selection'], data['filter'], ''+(parseInt(data['page']) -1 ));  //  update stored info
        extendSearch();              //  execute request to server and load retrieved data
        showLoadPage(false);         //  remove loading page
        requestSemaphore = false;    //  <- MUTUAL EXCLUSION

    }, 1500 );   //  required delay to give to the loading page animation enough time to complete
});

////   Click on right arrow on the music container to move forward the song list
//
//   Actions:
//             - check semaphore and in case undo request
//             - show loading panel
//             - extract stored information
//             - clean the listed songs and stored information
//             - save updated search information
//             - execute song search
//             - load songs on music container
//             - remove loading panel
document.getElementById( 'right-arrow' ).addEventListener( 'click', function(event){

    event.preventDefault();
    if( requestSemaphore ) return;   //  one request a time

    requestSemaphore = true;         //  <- MUTUAL EXCLUSION
    showLoadPage( true );            //  hide music box
    setTimeout( function(){

        let data = extractContainerData();   //  extracting stored info
        cleanMusicContainer();               //  clean listed songs and stored information
        storeMusicData( data['type'], data['selection'], data['filter'], ''+(parseInt(data['page']) +1 ));  //  update stored info
        extendSearch();              //  execute request to server and load retrieved data
        showLoadPage( false );       //  remove loading page
        requestSemaphore = false;    //  <- MUTUAL EXCLUSION

    }, 1500 );   //  required delay to give to the loading page animation enough time to complete

});

//  extracts search inputs values
function extractSearchData(){

    let data = {};
    filterContainer.querySelectorAll( '.input-filter' ).forEach( input => {
        switch( input.tagName ){

            case 'input': case 'INPUT':
                data['filter'] = input.value;
                break;
            
            case 'select': case 'SELECT':
                data['selection'] = input.options[input.selectedIndex].value;
                break;
            default:
                break;        
        }
    });
    
    return data;
}

//  cleans all the search inputs
function cleanFilters(){
    filterContainer.querySelectorAll( '.input-filter' ).forEach( input => {
        switch( input.tagName ){
            case 'input': case 'INPUT':
                input.value = '';
                break;
            
            case 'select': case 'SELECT':
                input.selectedIndex = 0;
                break;
            default:
                break;        
        }
    });
}

//  extract stored information from the music container
function extractContainerData(){

    let data = {};
    musicContainer.querySelectorAll( '.hidden-input' ).forEach( input => data[input.name] = input.value );
    
    return data;
}

//  stores given information into the music container
function storeMusicData( type, selection, filter, page ){
    musicContainer.querySelectorAll( '.hidden-input' ).forEach( input => {
        switch( input.name ){
            case 'page':
                input.value = page;
                break;

            case 'type':
                input.value = type;
                break;
                
            case 'selection':
                input.value = selection;
                break;
                
            case 'filter':
                input.value = filter;
                break;
            default:
                break;        
        }
    })
}

//  executes a default search(buyed songs)
function makeDefaultSearch(){

    storeMusicData( 'default_search', '', '', '0' );            //  store search information
    let response = getData( 'default_search', '', '', '0' );   //  getting response from server
    loadSongs('0', response );   //  loading songs

}

//  executes a filtered search
function makeSearch(){

    let data = extractSearchData(); //  get data from the search inputs
    storeMusicData( 'search', data['selection'], data['filter'], '0' );  //  store search information
    let response = getData( 'search', data['selection'], data['filter'], '0' );  //  getting response from server
    loadSongs( '0', response );  //  loading songs
    
}

//  executes an extension of a search(display another page of the previous search)
function extendSearch(){

    let data = extractContainerData();   //  get data from the stored information
    let response = getData( data['type'], data['selection'], data['filter'], data['page'] );  //  execute next request(page already updated)
    loadSongs( data['page'], response );  //  loading songs

}

//  loads a list of songs into the music container
function loadSongs( page, songs ){

    if( page == '0' )  //  on page 0 we not display left-arrow
        document.getElementById( 'left-arrow' ).style.opacity = 0;
    else
        document.getElementById( 'left-arrow' ).style.opacity = 1;

    songs.forEach( song => addSong( song['songID'], song['title'], song['artist'],  song['price'], song['song'], song['img'] ));

}

//  adds a song into the music container by generating dynamically its html code
//  song html structure:
//
//  <div class="song-info">
//      <label class="label-song-title">Title:<span class="song-title">test</span></label>
//      <label class="label-song-artist">Artist:<span class="song-artist">artist</span></label>
//      <label class="label-song-album">Album:<span class="song-album">album</span></label>
//      <div class="label-song-music">
//            <audio controls="" autoplay="" src="">Your browser does not support the audio element</audio>
//      </div>
//      <button class="buy-button">Buy</button>
//      <label class="label-song-price"><span class="song-price">10$</span></label>
//  </div>
function addSong( songID, title, artist, songPrice, song, img ){

    let songBox = document.createElement( 'div' );
    songBox.classList.add( 'song-box' );
    songBox.style.backgroundImage = 'url( ' + img + ')';

    let songInfo = document.createElement( 'div' );
    songInfo.classList.add( 'song-info' );
    let songMusic = document.createElement( 'div' );
    songMusic.classList.add( 'label-song-music' );

    let button = document.createElement( 'button' );
    button.classList.add( 'buy-button' );
    button.textContent = 'Buy';
    button.addEventListener( 'click', function( event ){
        event.preventDefault();
        showPopup( songID );
    });

    let price = document.createElement( 'label' );
    price.classList.add( 'label-song-price' );

    let priceSpan = document.createElement( 'span' );
    priceSpan.classList.add( 'song-price' );
    priceSpan.textContent = songPrice;
    price.appendChild( priceSpan );

    for( let i = 0; i<3; i++ ){

        let label = document.createElement( 'label' );
        let span = document.createElement( 'span' );
        let classType;
        let content;
        let contentLabel;
        switch( i ){
            case 0:
                classType = 'song-title';
                content = title;
                contentLabel = 'Title:';
                break;
            
            case 1:
                classType = 'song-artist';
                content = artist;
                contentLabel = 'Artist:';
                break; 
            case 3:
                    
        }
        span.textContent = content;
        span.classList.add( classType );
        label.textContent = contentLabel;
        label.appendChild( span );
        label.classList.add( 'label-' + classType );
        songInfo.appendChild( label );

    }

    songMusic.innerHTML = '<audio controls>Your browser does not support the audio element</audio>';
    songMusic.getElementsByTagName( 'audio' )[0].src = song;
    songInfo.appendChild( songMusic );

    if( songPrice.length > 0 ){
        songInfo.appendChild( button );
        songInfo.appendChild( price );
    }else{
        let download = document.createElement( 'button' );
        download.textContent = 'Download';
        download.classList.add( 'download-button' );
        download.addEventListener( 'click', function(event){
            downloadSong( songID );
        })
        songInfo.appendChild( download );
    }

    songBox.appendChild( songInfo );
    musicContainer.appendChild( songBox );

}

//  cleans the stored information and removes all the listed songs
function cleanMusicContainer(){

    document.getElementById( 'page-num' ).value = '';
    var elements = musicContainer.getElementsByClassName( 'song-box' );

    while (elements[0])
        musicContainer.removeChild( elements[0] );

    musicContainer.querySelectorAll( '.hidden-input' ).forEach( input => input.value = '' );    

}


////   PAYMENT FORM MANAGEMENT

////   Click on Undo Button on the payment form
//
//   Actions:
//             - remove the payment popup restoring the main page
//             - clean the payment form inputs and stored information
document.getElementById( 'retry-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    hidePopup();            //  hiding the popup containing the payment form
    cleanPaymentForm();     //  when hide clean the inputs and stored information

})

////   Click on Pay Button on the payment form
//
//   Actions:
//             - check all the inputs are valid
//             - change popup page to the opt form one
//             - clean the payment form inputs and stored information
document.getElementById( 'pay-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    let data = checkPaymentForm();
    if( data == null )
        return;
    
    showOTP( data );
    cleanPaymentForm();

})

//   Dynamic behaviour of ccn input, checks values are digits and autoinsert bars every 4 digits
document.querySelector('.input-ccn').addEventListener( 'input', function() { 

    let text=this.value;                                        
    text=text.replace(/\D/g,'');    // remove illegal characters
    if(text.length>3) text=text.replace(/.{4}/,'$&-');   // Add - at pos.5
    if(text.length>7) text=text.replace(/.{9}/,'$&-');   // Add - at pos.10
    if(text.length>7) text=text.replace(/.{14}/,'$&-');  // Add - at pos.15
    this.value=text; 

});

//   Dynamic behaviour of cvv input, checks values are digits
document.querySelector('.input-cvv').addEventListener( 'input', function() { 

    let text=this.value;                                                     
    text=text.replace(/\D/g,'');  //Remove illegal characters
    this.value=text; 

});

//  checks payment form inputs and extract their values. Returns null in case at least one input isn't valid
function checkPaymentForm(){

    let data = {};
    let inputs =  paymentForm.getElementsByTagName( 'input' );
    for( let input of inputs )
        switch( input.name ){

            case 'ccn':
                data['ccn'] = checkCCN( input.value );
                if( data['ccn'] == null ) return null;
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
                break;                     
        }

    return data;
}

//  checks the ccn is valid. Returns the ccn or null in case is invalid
function checkCCN( value ){

    if( value.length != 19 ) return null;  //  ccn must be 19 char(plus -)
    value = value.replaceAll( '-', '' );   //  removing the bars
    if( /^\d+$/.test( value ))             //  verify all chars are numbers
        return value;  
    
    return null;  

}

//  checks the cvv is valid. Returns the cvv or null in case is invalid
function checkCVV( value ){

    if( value.length != 3 ) return null;
    return /^\d+$/.test( value )? value : null;

}

//  cleans the payment form inputs
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
    let otp = checkOTPform();

    if( otp == null ){
        showOTPerror( 'All the OTP fields must be present');
        return;
    }

    let data = extractOTPinfo();
    
    if( buySong( data['songID'], data['ccn'], data['cvv'], data['name'], data['surname'], data['expire'], data['otp-id'], otp)){
        showOTPok();
        setTimeout( function(){
            hideOTP();
            hidePopup();
        }, 2000 );
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

//  shows otp response OK into the otp form
function showOTPok(){
    cleanOTPmessage();
    otpMsg.textContent = 'Request correctly done';
    otpMsg.classList.add( 'ok' );
}

//  shows otp response ERROR into the otp form
function showOTPerror( message ){
    cleanOTPmessage();
    otpMsg.textContent = message;
    otpMsg.classList.add( 'error' );
}

//  removes the displayed message
function cleanOTPmessage(){
    otpMsg.classList.remove( 'ok' );
    otpMsg.classList.remove( 'error' );
    otpMsg.textContent = '';
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

//  Extracts the stored information into the otp form
function extractOTPinfo(){

    let data = {};
    otpForm.querySelectorAll( '.hidden-input' ).forEach( input => data[input.name] = input.value );
    return data;
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
    cleanOTPmessage();
}
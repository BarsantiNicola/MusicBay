const storeBox = document.getElementById( 'store-box' );             //  music container
const paymentPanel = document.getElementById( 'pay-panel' );         //  payment panel containing payment form and otp
const otpForm = document.getElementById( 'otp-form' );               //  otp form container
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
    makeDefaultSearch();         //  putting buyed songs into the home folder
    showLoadPage( false );       //  display the home folder removing the loading panel
    requestSemaphore = false;    //  enable requests to the server
},  false );

////   Click on logout button 
//
//   Actions:
//             - notify the server to drop the session
document.getElementById( 'exit-button' ).addEventListener( 'click', function(){
    disconnect();
})

//  shows a popup containing the payment and otp forms
function showPopup(){

    popup.classList.remove( 'disable' );
    let data = getCart();
    for( let row of data )
        addCartRow( row );
}

//  removes the popup displaying the main page
function hidePopup(){

    extendSearch();
    popup.classList.add( 'disable' );
    cleanCartTable();

}

function addCartRow( row ){

    let table = document.getElementsByClassName( "responsive-table" )[0];
    let table_row = document.createElement( "li" );
    table_row.classList.add( "table-row" );
    let title = document.createElement( "div" );
    title.classList.add("col");
    title.classList.add("col-1");
    let artist = document.createElement( "div" );
    artist.classList.add("col");
    artist.classList.add("col-2");
    let price = document.createElement( "div" );
    price.classList.add("col");
    price.classList.add("col-3");
    let button = document.createElement( "button" );

    button.classList.add("remove-cart-button");

    title.textContent = row[ 'title' ];
    artist.textContent = row[ 'artist' ];
    price.textContent = row[ 'price' ];
    button.textContent = "Remove";
    button.addEventListener( 'click', function( event ){

        event.preventDefault();
        removeFromCart( row[ 'song-id' ]);
        removeCartRow( row[ 'title' ]);
    })

    table_row.appendChild( title );
    table_row.appendChild( artist );
    table_row.appendChild( price );
    table_row.appendChild( button );
    table.appendChild( table_row );
}

function removeCartRow( title ){
    document.querySelectorAll( ".table-row" ).forEach( (row)=> {
        if( row.getElementsByClassName( "col-1")[0].textContent == title )
            document.getElementsByClassName( "responsive-table" )[0].removeChild( row );
    })
}

function cleanCartTable(){
    let table = document.getElementsByClassName( "responsive-table" )[0];
    table.querySelectorAll( ".table-row" ).forEach( (row) => table.removeChild( row ));
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

document.getElementById( 'shopping-cart' ).addEventListener( 'click', function( event ){

    event.preventDefault();
    const data = document.getElementById( 'cart-show' );
    if( !data.classList.contains( 'disabled' ))
        showPopup(1 );
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

function showCart( value ){

    const cart = document.getElementById( 'cart-show' );
    const icon = document.getElementsByClassName('fa-shopping-cart' )[0];
    if( value > 0 ){
        cart.textContent = value;
        cart.classList.remove( "disabled" );
        icon.classList.add( "active" );
    }else{
        cart.textContent = '';
        cart.classList.add( "disabled" );
        icon.classList.remove( "active" );
    }
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

    if( page === '0' )  //  on page 0 we not display left-arrow
        document.getElementById( 'left-arrow' ).style.opacity = 0;
    else
        document.getElementById( 'left-arrow' ).style.opacity = 1;

    for( let song of songs )
        addSong( song['songID'], song['title'], song['artist'],  song['price'], song['song'], song['img'] );

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
    songBox.style.backgroundImage = 'url( pics/' + img + ')';

    let songInfo = document.createElement( 'div' );
    songInfo.classList.add( 'song-info' );
    let songMusic = document.createElement( 'div' );
    songMusic.classList.add( 'label-song-music' );

    let button = document.createElement( 'button' );
    button.classList.add( 'buy-button' );
    button.textContent = 'Add to Cart';
    button.addEventListener( 'click', function( event ){
        event.preventDefault();
        addToCart( songID );
    });

    let price = document.createElement( 'label' );
    price.classList.add( 'label-song-price' );

    let priceSpan = document.createElement( 'span' );
    priceSpan.classList.add( 'song-price' );
    if( songPrice !== undefined && songPrice != null )
        priceSpan.textContent = songPrice;
    else
        priceSpan.textContent = '';
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
    songMusic.getElementsByTagName( 'audio' )[0].src = "demo/"+song;
    songInfo.appendChild( songMusic );

    if( songPrice !== undefined && songPrice != null && songPrice.length > 0 ){
        songInfo.appendChild( button );
        songInfo.appendChild( price );
    }else{
        let download = document.createElement( 'button' );
        download.textContent = 'Download';
        download.classList.add( 'download-button' );
        download.addEventListener( 'click', function(){
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

})

////   Click on Pay Button on the payment form
//
//   Actions:
//             - check all the inputs are valid
//             - change popup page to the opt form one
//             - clean the payment form inputs and stored information
document.getElementById( 'pay-button' ).addEventListener( 'click', function(event){

    event.preventDefault();
    makePayment();
    cleanCartTable();
    showCart(0);
    hidePopup();


})




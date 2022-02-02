<!DOCTYPE html>
<html>
	<head>
        <?php
            //if( !isset( $_SESSION[ 'user-id' ]))
                //header('Location: ' . 'index.php', true, 301 );
        ?>
        <meta name="author" content="Barsanti Nicola">
    	<meta charset="UTF-8">
		<link rel="icon" type="image/png" href="img/logo.png">
		<link rel="stylesheet" type="text/css" href="css/store.css">
        <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    	<title>MusicBay</title>
	</head>

	<body class="preload">
        <header>
        </header>
        <div class="payment-popup disable">
            <div id="pay-panel" class="payment-panel">
                <div class="payment-form">
                    <div class="table-container">
                        <ul class="responsive-table">
                            <li class="table-header">
                                <div class="col-head col-1">Title</div>
                                <div class="col-head col-2">Artist</div>
                                <div class="col-head col-3">Price</div>
                                <div class="col-head col-4"> </div>
                            </li>

                    </div>
                    <form class="payment-form-2">
                        <h1>Insert Payment Information</h1>
                        <label class="label-ccn">Creditcard No.
                            <div class="inputs-field">
                                <input class="input-ccn" name="ccn" type="tel" autocomplete="cc-number" maxlength="19" placeholder="XXXX-XXXX-XXXX-XXXX">
                                <input class="input-cvv" name="cvv" type="tel" maxlength=3 placeholder="CVV">
                            </div>
                        </label>
                        <label class="label-credentials">Name on Card
                            <div class="inputs-field">
                                <input class="input-name" name="name" type="text" placeholder="Name">
                                <input class="input-surname" name="surname" type="text" placeholder="Surname">
                            </div>
                        </label>
                        <label class="label-expire">Card Expire
                            <div class="inputs-field">
                                <input class="input-expire" name="expire" type="date" class="center" date-date-format="YY/MM/DD">
                            </div>
                        </label>
                        <input id="hidden-pay-songID" class="hidden-input" type="hidden" name="songID">
                    </form>


                </div>
                <div class="payment-buttons">
                    <button class="payment-button" id="retry-button">Retry</button>
                    <button class="payment-button" id="pay-button">Pay</button>
                </div>
                <div class="confirm-panel">
                    <input type="hidden" class="hidden-input" id="transaction-id">
                    <div class="confirm">
                        <h1>Confirm your request</h1>
                        <p>Clicking on confirm button for complete your order:</p>
                        <p id="order-total">Total: 5$</p>
                        <div>
                            <button class="payment-button" id="retry-confirm-button">Retry</button>
                            <button class="payment-button" id="confirm-button">Confirm</button>
                        </div>
                    </div>
                    <div class="result">
                        <img id="result-pic">
                        <p id="result"></p>
                    </div>

                </div>
            </div>
        </div>
        <div class="container">
            <div class="header-panel">
                <i id="home-search" class="fa fa-home"></i>
                <form id="filter-container">
                    <select class='input-filter'>
                        <option></option>
                        <option>Rock</option>
                        <option>Pop</option>
                        <option>Metal</option>
                        <option>Punk</option>
                    </select>
                    <label>
                        Search
                        <input class='input-filter' type="text">
                        <i id="filtred-search" class="fa fa-search"></i>
                        
                    </label>

                </form>
                <div class="logout">
                    <?php
                        if( !isset( $_SESSION[ 'cart' ]) || sizeof( $_SESSION[ 'cart' ]) == 0 ){
                            echo '<text id="cart-show" class="disabled"></text>';
                            echo '<a><i  id="shopping-cart" class="fa fa-shopping-cart"></i></a>';
                        }else{
                            echo '<text id="cart-show">'.sizeof( $_SESSION[ 'cart' ]) . '</text>';
                            echo '<a><i id="shopping-cart" class="fa fa-shopping-cart active"></i></a>';
                        }
                    ?>
                    <a id="exit-button" href=index.php><i class="fa fa-times-circle"></i></a>
                </div>

            </div>
            <div id="store-box" class="store-container">
                <div class="overlay-container">
                    <div class="loader">
                        <div>
                            <img src="img/store_loader.gif">
                        </div>
                        <div class="info-container">
                                <span> (っ◔◡◔)っ</span>
                                <div class="inner-text">
                                    <h1>Just a sec!</h1>
                                    <p>We are processing your request. Be patient, it will take few seconds</p>
                                </div>
                        </div>

                    </div>
                </div>

                <div class="music-box">
                    <i id="left-arrow" class="fa fa-arrow-left"></i>
                    <div id="music-container">
                        <input class="hidden-input" type="hidden" name="page" id="page-num" value='0'>
                        <input class="hidden-input" type="hidden" name="selection">
                        <input class="hidden-input" type="hidden" name="type">
                        <input class="hidden-input" type="hidden" name="filter">
                    </div>
                    <i id="right-arrow" class="fa fa-arrow-right"></i>
                </div>
            </div>
        </div>
		<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'></script>
        <script src="js/crypto.js"></script>
        <script src="js/connection.js"></script>  
        <script src="js/store.js"></script>  
	</body>
</html>		

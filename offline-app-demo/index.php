<!DOCTYPE html>
<html>
    <head>
        <title>App</title>

        <!-- Need this for Android with Crosswalk -->
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">

        <meta http-equiv="Content-Security-Policy" content="default-src 'self' *; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' 'unsafe-eval' *">

        <link rel="stylesheet" type="text/css" href="style.css">

        <script type="text/javascript" src="lib/jquery-2.1.4.min.js"></script>

        <!-- <script type="text/javascript" src="connection.js"></script> -->
        <!-- <script type="text/javascript" src="init.js"></script> -->
        <script type="text/javascript" src="cache.js"></script>


    </head>
    <body>
        <div id="online" data-iframe="http://APPPURL.com/?appp=2">
            <!-- the iframe is dynamically created here after we check offline/online connection -->
        </div>
        <div id="offline-footer">
            <div id="offline-msg">Lost connection, functionality is limited.</div>
            <button class="button button-positive" id="go-offline">Offline Mode</button>
            <button class="button button-positive" id="close-offline">Close</button>
        </div>

        <!-- <script type="text/javascript" src="cordova.js"></script> -->
    </body>
</html>

var appp_settings = {
	app_offline_toggle: null
};

// Resources that need to load before initializing scripts
var remote_resources = {
	themejs: { status: ''}, // custom.js
	appp_pg: { status: '', src: 'wp-content/plugins/apppresser/js/apppresser2-plugins.js' }
};

function onLoad() {
	document.addEventListener("deviceready", onDeviceReady, false);
	window.addEventListener("message", receiveMessage, false);

	// These listen for a connection change, toggleCon is in offline.js
	document.addEventListener("online", appTop.conn.events.collect_e, false);
	document.addEventListener("offline", appTop.conn.events.collect_e, false);

	jQuery('#go-online.btn').on('click', function() {window.location = '../index.html';});
	jQuery('#close-online.btn').on('click', function() {appTop.conn.closeOfflineModal();});
}

function onDeviceReady() {
	console.log('device is ready');

	if ( navigator.splashscreen ) {
		navigator.splashscreen.hide();
	}

	// start the offline check now
	appInit( 'device_ready' );
}

function receiveMessage(e) {
	console.log(e.data);

	if( e.data === 'native_transition_left' || e.data === 'native_transition_right' ) {
		if( typeof fireTransition !== 'undefined' )
			fireTransition(e.data);
	}

	if( e.data === 'load_ajax_content_done' || e.data === 'site_loaded' ) {
		// Run appInit on ajax content load. It would be prudent to check referrer domain here, and return if not the one we want.
		console.debug('receiveMessage: calling appInit()');
		appInit('site_loaded');
	}

	if( e.data === 'remote_pg_loaded' ) {
		appInit('remote_pg_loaded');	
	}

}
/**
 * Handles either 1) loading offline files, 2) online resources or 3) initializing our phonegap scripts
 * There are three case:
 * device_ready + offline: load offline files
 * device_ready + online: load online files
 * site_loaded: initialize our phonegap scripts
 */
function appInit( status ) {
	// Device is ready, fire off our functions

	var debug = (jQuery('#online').data('debugging'));

	appp_settings.offline_fade_millisec = 1000;

	if( status == 'device_ready' ) {
		
		// OFFLINE
		if( appTop.conn.offlineCheck() == 'offline' ) {
			appTop.conn.offlineInit();
		} 

		// ONLINE: Load remote resources
		else {

			// phonegap js (apppresser-plugins.js)
			if( remote_resources.appp_pg.status === '' ) {
				remote_resources.appp_pg.status = 'requested';
				appTop.conn.addRemoteScripts();
			}


			// iframe (apptheme/js/custom.js)
			if( remote_resources.themejs.status === '' ) {
				remote_resources.themejs.status = 'requested';
				appTop.conn.createIframe();
			}

		}
	}

	// ONLINE: Theme's custom.js 'site_loaded' and apppresser-plugins2.js 'remote_pg_loaded'
	else if( status == 'site_loaded' || status == 'remote_pg_loaded' ) {

		if( status == 'site_loaded' ) {
			remote_resources.themejs.status = 'received';
		}

		if( status == 'remote_pg_loaded' ) {
			remote_resources.appp_pg.status = 'received';
		}

		if( remote_resources.themejs.status == 'received' && remote_resources.appp_pg.status == 'received' ) {
			appTop.conn.addCordovaAddonScripts();
			appTop.remote.init(debug);

			// Let developers know when it's time to init any custom code.
			document.dispatchEvent( new Event('apppinit') );

		}
	}
}

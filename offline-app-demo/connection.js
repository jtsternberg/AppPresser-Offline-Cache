// initiate appTop var if it hasn't been
window.appTop = typeof window.appTop !== 'undefined' ? window.appTop : {};

appTop.conn = {

	debugging: false,
	timeout_id: 0,
	timeout_length: 800, // milliseconds to pause before prompting
	current_mode: '', // online or offline
	iframe_exists: false,

	cacheSelectors: function() {
		appTop.conn.goOffline = document.getElementById('go-offline');
		appTop.conn.goOnline = document.getElementById('go-online');
		appTop.conn.closeOffline = document.getElementById('close-offline');
		appTop.conn.closeOnline = document.getElementById('close-online');
		appTop.conn.offlineFooter = document.getElementById('offline-footer');
		appTop.conn.onlineFooter = document.getElementById('online-footer');
	},

	init: function( debug ) {

		appTop.conn.cacheSelectors();

		if( debug ) {
			appTop.conn.debugging = true;
		}

		// only on index.html
		if( appTop.conn.closeOffline ) {
			appTop.conn.closeOffline.addEventListener('click', function() { appTop.conn.closeOfflineModal(); });
		}
		if( appTop.conn.closeOnline ) {
			appTop.conn.closeOnline.addEventListener('click', function() { appTop.conn.closeOnlineModal(); });
		}
		if( appTop.conn.goOffline ) {
			appTop.conn.goOffline.addEventListener('click', function() { window.location = 'offline/offline.html'; });
		}
		if( appTop.conn.goOnline ) {
			appTop.conn.goOnline.addEventListener('click', function() { window.location = '../index.html'; });
		}
	},

	/**
	 * 
	 */
	offlineMode: function(buttonIndex) {
		if( buttonIndex == 1 ) {
			document.getElementById("online").style.display = "none";
			document.getElementById("offline").style.display = "block";
		}
	},

	/**
	 * Initialize the offline activity
	 */
	offlineInit: function() {

		appp_settings.app_offline_toggle = ( localStorage.app_offline_toggle ) ? true : false;

		// load offline.html
		appTop.conn.toggleStatus();
	},

	/**
	 *	Transition from offline to online
	 */
	onlineMode: function(buttonIndex) {
		this.closeOfflineModal();
	},

	/**
	 * After confirming app is online, the iframe can then be created.
	 * 
	 */
	createIframe: function() {
		if( navigator.network.connection.type == Connection.NONE && 
			! appTop.conn.iframe_exists) {
			// wait
		}

		if( appTop.conn.getCurrentMode() == 'online' && appTop.conn.iframe_exists == false ) {
			appTop.conn.iframe_exists = true;

			// Random string forces cache to break
			var d = new Date();
			var n = d.getMilliseconds();

			jQuery('<iframe>', {
			   src: jQuery('#online').data('iframe') + '&' + n,
			   id:  'myApp',
			   frameborder: 0,
			   scrolling: 'no'
			   }).appendTo('#online');
		}

		// now wait for postMessage (site_loaded) from theme's /js/custom.js
	},

	/**
	 *	A
	 */
	addRemoteScripts: function() {
		var dev = ( jQuery('#online').data('debug-lib') && jQuery('#online').data('debug-lib') === true ) ? '.dev' : '';
		var apppresserjs = remote_resources.appp_pg.src.replace('.js', dev+'.js');
		var src = jQuery('#online').data('iframe').split('?')[0] + apppresserjs;

		if( typeof appTop.remote === 'undefined' ) {
			jQuery('<script>', {
				src: src,
				id:  'remote-init',
			}).appendTo('head');
		}
	},

	/**
	 * Add js files added to the admin settings 'Cordova Add on' tab
	 * and append them to the index.html <head>
	 */
	addCordovaAddonScripts: function() {

		var iframewin = document.getElementById('myApp').contentWindow.window;

		if( typeof iframewin.appp_remote_addon_js !== 'undefined' && window.location.href.indexOf('index.html') > 0) {

			r_src = iframewin.appp_remote_addon_js;

			for (var i = 0; i < r_src.length; i++) {
				if( appTop.conn.get_remote_script_status( r_src[i] ) == 200 ) {
					jQuery('<script>', {
						src: r_src[i],
					}).appendTo('head');
				}	
			};
		}
	},

	events: {

		/**
		 * Collects only the last offline/online event in a X second timeframe
		 * before sending it on to toggleStatus()
		 */
		collect_e: function(e) {

			appTop.conn.debug.log("connection event", e);
			appTop.conn.debug.log("connection type", navigator.network.connection.type);

			if ( !e || !e.type || typeof appTop.conn.timeout_id === "number") {
				appTop.conn.debug.log("cancel timeout", appTop.conn.timeout_id);
				appTop.conn.events.cancel_timeout();
				appTop.conn.timeout_id = window.setTimeout(function() { appTop.conn.toggleStatus(); }, appTop.conn.timeout_length);
			} else {
				appTop.conn.current_mode = e.type;
				appTop.conn.timeout_id = window.setTimeout(function() { appTop.conn.toggleStatus(); }, appTop.conn.timeout_length);
			}
		},

		cancel_timeout: function() {
			window.clearTimeout(appTop.conn.timeout_id);
			appTop.conn.timeout_id = undefined;
		},


	},

	getCurrentMode: function() {
		if( ! appTop.conn.current_mode ) {
			appTop.conn.current_mode = ( navigator.network.connection.type == Connection.NONE ) ? 'offline' : 'online';
		}

		return appTop.conn.current_mode;
	},

	openOfflineModal: function() {

		appTop.conn.cacheSelectors();

		if( appp_settings.app_offline_toggle === false ) {
			return;
		}

		if(appp_settings.app_offline_toggle) {

			if( appTop.conn.goOffline ) {
				appTop.conn.goOffline.style.display = 'inline-block';
			} else if ( goOnline ) {
				goOnline.style.display = 'inline-block';
			}
		} else {
			appTop.conn.goOffline.style.display = 'none';
		}

		// if the offline modal fades away automatically, we don't need the close button
		if(appp_settings.app_offline_toggle) {
			appTop.conn.closeOffline.style.display = 'inline-block';
		} else {
			appTop.conn.closeOffline.style.display = 'none';
		}
		
		appTop.conn.offlineFooter.classList.add('fade-in');
	},

	closeOfflineModal: function() {

		appTop.conn.cacheSelectors();

		appTop.conn.offlineFooter.classList.remove('fade-in');
	},

	closeOnlineModal: function() {

		appTop.conn.cacheSelectors();

		appTop.conn.onlineFooter.classList.remove('fade-in');
	},

	openOnlineModal: function() {

		// cache selectors and attach click events
		appTop.conn.init();

		appTop.conn.onlineFooter.classList.add('fade-in');
	},

	/**
	 * Handles redirects between index.html and offline.html and
	 * displays the prompting to the user
	 */
	toggleStatus: function() {

		appTop.conn.debug.log('Toggle Connection', navigator.network.connection.type);

		appTop.conn.events.cancel_timeout();

		appTop.conn.debug.log(appTop.conn.current_mode);

		if( appTop.conn.getCurrentMode() == 'offline' ) {
			if( appTop.conn.iframe_exists ) {
				appTop.conn.offlinePrompt();
			} else if ( window.location.href.indexOf('offline.html') < 0 ) {
				// just started the app -- offline
				window.location = 'offline/offline.html';
			} else {
				// already offline
			}
		} else if( appTop.conn.getCurrentMode() == 'online' ) {
			if( ! appTop.conn.iframe_exists ) {
				if ( window.location.href.indexOf('offline.html') > 0 ) {
					if( appp_settings.app_offline_toggle ) {
						this.onlinePrompt();
					} else {
						window.location = '../index.html';
					}
				}
			} else {
				// already looking at the iframe:
				// just gracefully close the modal without user action
				this.closeOfflineModal();
			}
			// TODO: where's a better place to put this or do we even need it anymore?
			//appTop.conn.onlineTransition();
		} else {
			appTop.conn.log('Current mode', appTop.conn.current_mode);
			appTop.conn.debug.log('Toggle Connection', navigator.network.connection.type);
		}
	},

	get_remote_script_status: function( url ) {
		var http = new XMLHttpRequest();
		http.open('HEAD', url, false);
		http.send();
		return http.status;
	},

	/**
	 * Online network connection confirmed, prompt the user with a modal in the offline.html file.
	 */
	onlinePrompt: function() {
		this.openOnlineModal();
	},

	/**
	 * Offline network connection confirmed, prompt the user with a modal in the offline.html file.
	 */
	offlinePrompt: function() {
		// show the offline-footer div
		this.openOfflineModal();
	},

	/**
	 * Deprecated
	 *
	 * Used when the iframe already exists, but the site doesn't contain the apppCore
	 * It will refresh the iframe in an attempt to reload the page
	 */
	onlineTransition: function() {
		// document.getElementById('offline-footer').style.opacity = 0;

		var iWin = document.getElementById('myApp').contentWindow.window;

		if( typeof iWin != 'object' || typeof iWin.apppCore != 'object' ) {
			if( appTop.conn.online_reset == 0 ) {
				// only do this once, careful not to create an infite loop
				appTop.conn.online_reset = 1;
				document.getElementById('myApp').src = document.getElementById('myApp').src;
			}
		}
	},

	/**
	 * Check for a network connection before requesting remote resources
	 */
	offlineCheck: function() {
		appTop.conn.debug.log("connection type" + navigator.network.connection.type);

		if(navigator.network.connection.type == Connection.NONE) {
			appTop.conn.current_mode = 'offline';
			appTop.conn.online_reset = 0;
		} else {
			appTop.conn.current_mode = 'online';
		}

		return appTop.conn.current_mode;
	},

	debug: {
		log: function() {
			if(appTop.conn.debugging) {
				console.log.apply(console, arguments);
			}
		}
	}

};


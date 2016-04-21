window.apppCache = window.apppCache || {};

( function( window, document, $, app, undefined ) {
	'use strict';

	app.restEndpoint = 'appp-offline/v1/static-pages';
	app.restOptions = '?base64_images&remove_scripts&remove_stylesheets';
	app.base64_images = true;
	// Whether we should check for cache to populate page. Likely only when offline.
	app.replaceThis = app.replaceThis || false;
	app.pages = {};

	app.init = function() {

		console.log('localStorage',localStorage);

		if ( window.location.hash ) {
			return app.getCachedPage( window.location.hash );
		}

		if ( app.getQueryVar( 'resturl' ) ) {
			app.resturl = app.getQueryVar( 'resturl' );
			app.cache( 'resturl', app.resturl );
		} else {
			app.resturl = app.cacheGet( 'resturl' );
		}

		if ( ! app.resturl ) {
			return;
		}

		app.resturl += app.restEndpoint;

		// Get
		$.get( app.resturl, function( json ) {
			$.each( json, app.cachePage );
		} );
	};

	app.getCachedPage = function( hash ) {
		if ( ! app.replaceThis ) {
			return;
		}

		var page_slug = hash.substr(1);
		var html = app.cacheGet( page_slug );
		if ( html ) {
			console.log('using cached html for: cache-'+ page_slug);
			app.replaceHtml( html );
		} else {
			return console.error( 'no cache found for: '+ page_slug );
		}

		var scripts = app.cacheGet( 'scripts-'+ page_slug );

		if ( scripts ) {
			scripts = scripts.split(',');
			app.replaceScripts( scripts );
		}
	};

	app.cachePage = function( index, page ) {
		var pageURL = app.resturl + '/' + page.id;

		if ( app.base64_images ) {
		  pageURL += app.restOptions;
		}

		$.get( pageURL, app.getCallback );
	};

	app.getCallback = function( page ) {
		if ( page.images && ! app.base64_images ) {
			page = app.replaceImages( page );
		}

		app.pages[ page.slug ] = page.html;
		app.cache( page.slug, page.html );

		if ( app.replaceThis ) {
			app.replaceHtml( page.html );
		}

		if ( page.stylesheets ) {
			app.replaceAllStylesheets( page, app.maybeReplaceScripts );
		} else {
			app.maybeReplaceScripts( page );
		}
	};

	app.replaceAllStylesheets = function( page, cb ) {
		page.stylesheets = $.map( page.stylesheets, function( stylesheet ) {
			return stylesheet.href;
		});
		app.replaceStylesheets( page.stylesheets, page.slug, function() {
			cb( page );
		} );
	};

	app.maybeReplaceScripts = function( page ) {
		if ( page.scripts ) {
			page.scripts = $.map( page.scripts, function( script ) {
				return script.src;
			});
			app.replaceScripts( page.scripts, page.slug );
		}
	};

	app.replaceImages = function( page ) {
		console.log( 'replacing images' );

		$.each( page.images, function( key, value ) {
			page.html = page.html.replace( new RegExp( key, "g" ), value );
		} );

		return page;
	};

	app.replaceStylesheets = function( stylesheets, page_slug, callback ) {
		if ( app.replaceThis && ! $( 'body' ).length ) {
			return setTimeout( function() {
				app.replaceStylesheets( stylesheets, page_slug );
			}, 500 );
		}

		var total = stylesheets.length;
		var done = 0;

		var cssLoadCB = function( response ) {
			var css = response[0];

			var style = '<style data-url="'+ css.url + '" type="text/css" media="screen">'+ css.data +'</style>';

			if ( app.replaceThis ) {
				$( 'body' ).append( style );
			} else {
				var regex = new RegExp( '</body>', 'm' );
				var before = app.pages[ page_slug ];
				app.pages[ page_slug ] = before.replace( regex, style + '</body>' );
			}

			if ( ++done >= total && page_slug ) {

				var html = app.replaceThis ? document.documentElement.outerHTML : app.pages[ page_slug ];
				app.cache( page_slug, html );

				callback();
			}
		};

		for ( var i = 0; i < total; i++ ) {
			basket.require({ url: stylesheets[ i ], execute: false }).then( cssLoadCB );
		}
	};

	app.replaceScripts = function( scripts, page_slug ) {
		console.log( 'replacing scripts' );

		var cache = '';

		for ( var i = 0; i <= scripts.length - 1; i++ ) {
			basket.require({ url: scripts[i], execute: app.replaceThis });

			cache += scripts[i] + ',';
		}

		if ( page_slug ) {
			app.cache( 'scripts-'+ page_slug, cache );
		}
	};

	app.getQueryVar = function( variable ) {
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var pair = vars[i].split("=");
			if ( pair[0] == variable ){
				return pair[1];
			}
		}

		return false;
	};

	app.replaceHtml = function( html ) {
		document.open();
		document.write( html );
		document.close();

		console.log('replaced html');
	};

	app.cacheGet = function( key ) {
		console.log( 'fetching cache: cache-'+ key );
		return localStorage.getItem( 'cache-'+ key );
	};

	app.cache = function( key, value ) {
		try {
			console.log( 'caching to: cache-'+ key );
			window.localStorage.setItem( 'cache-'+ key, value );
		}
		catch (e) {
			console.warn( 'Storage failed: ' + e );
		}
	};

	$( app.init );

} )( window, document, jQuery, window.apppCache );

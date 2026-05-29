/**
 * Easy G Maps - admin settings page (API key test).
 *
 * The Google Maps API key is expected to be HTTP-referrer-restricted, so it can
 * only be validated from the browser (the server has no whitelisted referrer).
 * This loads the Maps JS API with the entered key, catches auth failures, then
 * probes the Geocoding and Places APIs and reports each one.
 */
(function () {
	'use strict';

	var GOOGLE_URL = 'https://maps.googleapis.com/maps/api/js';

	var state = {
		loading: false,
		loadedKey: null,
		callbackFired: false,
		authFailed: false,
		timer: null,
	};

	function cfg() {
		return window.egmAdmin || { sampleAddress: 'Trafalgar Square, London, UK', i18n: {} };
	}

	function t( key, fallback ) {
		var strings = cfg().i18n || {};
		return strings[ key ] || fallback;
	}

	function tick( ok ) {
		return ok ? '✅ ' : '❌ ';
	}

	function clearTimer() {
		if ( state.timer ) {
			window.clearTimeout( state.timer );
			state.timer = null;
		}
	}

	function setBusy( busy ) {
		var button = document.querySelector( '.egm-test-key' );
		var spinner = document.querySelector( '.egm-settings .egm-spinner' );

		if ( button ) {
			button.disabled = busy;
		}
		if ( spinner ) {
			spinner.classList.toggle( 'is-active', busy );
		}
	}

	function render( lines, status ) {
		var box = document.getElementById( 'egm-test-result' );
		if ( ! box ) {
			return;
		}

		var noticeClass = 'notice-info';
		if ( 'success' === status ) {
			noticeClass = 'notice-success';
		} else if ( 'error' === status ) {
			noticeClass = 'notice-error';
		}

		var wrap = document.createElement( 'div' );
		wrap.className = 'notice ' + noticeClass;

		lines.forEach( function ( line ) {
			var p = document.createElement( 'p' );
			p.className = 'egm-test-line';
			p.textContent = line;
			wrap.appendChild( p );
		} );

		box.innerHTML = '';
		box.appendChild( wrap );
	}

	function settle() {
		state.loading = false;
		clearTimer();
		setBusy( false );
	}

	function probeGeocoding( done ) {
		try {
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { address: cfg().sampleAddress }, function ( results, status ) {
				if ( 'OK' === status || 'ZERO_RESULTS' === status ) {
					done( true, t( 'geoOk', 'Geocoding API: working' ) );
				} else if ( 'REQUEST_DENIED' === status ) {
					done( false, t( 'geoDenied', 'Geocoding API: request denied.' ) );
				} else {
					done( false, t( 'geoErr', 'Geocoding API: error' ) + ' (' + status + ')' );
				}
			} );
		} catch ( err ) {
			done( false, t( 'geoErr', 'Geocoding API: error' ) );
		}
	}

	function probePlaces( done ) {
		try {
			if ( ! google.maps.places ) {
				done( false, t( 'placesErr', 'Places API: error' ) );
				return;
			}

			var service = new google.maps.places.PlacesService( document.createElement( 'div' ) );
			service.findPlaceFromQuery(
				{ query: cfg().sampleAddress, fields: [ 'place_id' ] },
				function ( results, status ) {
					var statuses = google.maps.places.PlacesServiceStatus;
					if ( status === statuses.OK || status === statuses.ZERO_RESULTS ) {
						done( true, t( 'placesOk', 'Places API: working' ) );
					} else if ( status === statuses.REQUEST_DENIED ) {
						done( false, t( 'placesDenied', 'Places API: request denied.' ) );
					} else {
						done( false, t( 'placesErr', 'Places API: error' ) + ' (' + status + ')' );
					}
				}
			);
		} catch ( err ) {
			done( false, t( 'placesErr', 'Places API: error' ) );
		}
	}

	function runProbes( staleKey ) {
		var lines = [];

		if ( staleKey ) {
			lines.push( t( 'staleKey', 'Note: Google Maps was already loaded with an earlier key. Reload the page to test a changed key.' ) );
		}

		lines.push( tick( true ) + t( 'mapsOk', 'Maps JavaScript API: working' ) );

		probeGeocoding( function ( geoOk, geoMsg ) {
			lines.push( tick( geoOk ) + geoMsg );

			probePlaces( function ( placesOk, placesMsg ) {
				lines.push( tick( placesOk ) + placesMsg );
				settle();

				// Do not clobber a hard auth failure that fired during probing.
				if ( state.authFailed ) {
					return;
				}

				var allOk = geoOk && placesOk && ! staleKey;
				render( lines, allOk ? 'success' : 'info' );
			} );
		} );
	}

	function onApiReady() {
		if ( state.authFailed ) {
			return;
		}

		clearTimer();

		// Force a real map render so referrer/auth problems surface via
		// gm_authFailure, in addition to the per-API probes below.
		try {
			var probe = document.createElement( 'div' );
			probe.style.cssText = 'position:absolute;left:-9999px;width:1px;height:1px;';
			document.body.appendChild( probe );
			new google.maps.Map( probe, { center: { lat: 51.5074, lng: -0.1278 }, zoom: 8 } );
		} catch ( err ) {
			// Ignore - the probes below still run.
		}

		runProbes( false );
	}

	function loadApi( key ) {
		state.loadedKey = key;
		state.callbackFired = false;
		state.authFailed = false;
		state.loading = true;

		window.gm_authFailure = function () {
			state.authFailed = true;
			settle();
			render(
				[
					tick( false ) + t( 'authFail', 'Authentication failed - the key is invalid, or this domain is not allowed by the key\'s HTTP referrer restrictions.' ),
					t( 'authHint', 'Check the key and its referrer restrictions in Google Cloud Console, then reload this page and test again.' ),
				],
				'error'
			);
		};

		window.egmKeyTestCallback = function () {
			state.callbackFired = true;
			onApiReady();
		};

		var script = document.createElement( 'script' );
		script.src = GOOGLE_URL +
			'?key=' + encodeURIComponent( key ) +
			'&libraries=places' +
			'&callback=egmKeyTestCallback' +
			'&loading=async';
		script.async = true;
		script.onerror = function () {
			if ( state.callbackFired || state.authFailed ) {
				return;
			}
			settle();
			render( [ tick( false ) + t( 'loadError', 'Could not load the Google Maps script. Check your network connection and the key.' ) ], 'error' );
		};

		state.timer = window.setTimeout( function () {
			if ( state.callbackFired || state.authFailed ) {
				return;
			}
			settle();
			render( [ tick( false ) + t( 'timeout', 'Timed out waiting for Google Maps. Check the key, its referrer restrictions, and your network.' ) ], 'error' );
		}, 8000 );

		document.head.appendChild( script );
	}

	function runTest() {
		if ( state.loading ) {
			return;
		}

		var input = document.querySelector( '.egm-api-key' );
		var key = input ? input.value.trim() : '';

		if ( ! key ) {
			render( [ t( 'noKey', 'Enter an API key first, then click Test key.' ) ], 'error' );
			return;
		}

		setBusy( true );
		render( [ t( 'testing', 'Loading Google Maps and checking APIs...' ) ], 'info' );

		// The Maps JS API can only be bootstrapped once per page load. If it is
		// already present, probe the loaded instance and warn if the key changed.
		if ( window.google && window.google.maps ) {
			var stale = null !== state.loadedKey && state.loadedKey !== key;
			runProbes( stale );
			return;
		}

		loadApi( key );
	}

	function init() {
		var button = document.querySelector( '.egm-test-key' );
		if ( ! button ) {
			return;
		}

		button.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			runTest();
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}());

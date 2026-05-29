/**
 * Easy G Maps - front-end map rendering.
 *
 * Reads the map config from each .egm-map container's data-egm-map attribute,
 * resolves marker positions (lat/lng, Google Place ID, or address), and builds
 * the map. Google Maps calls window.egmInitMaps once its API has loaded; a
 * MutationObserver also picks up maps added after load (AJAX, block editor).
 */
(function () {
	'use strict';

	function parseConfig( el ) {
		try {
			return JSON.parse( el.getAttribute( 'data-egm-map' ) || '{}' );
		} catch ( err ) {
			return {};
		}
	}

	function escapeHtml( text ) {
		var div = document.createElement( 'div' );
		div.textContent = text;
		return div.innerHTML;
	}

	function geocode( geocoder, request ) {
		return new Promise( function ( resolve ) {
			geocoder.geocode( request, function ( results, status ) {
				resolve( ( 'OK' === status && results && results[0] ) ? results[0].geometry.location : null );
			} );
		} );
	}

	function placeLocation( service, placeId ) {
		return new Promise( function ( resolve ) {
			service.getDetails( { placeId: placeId, fields: [ 'geometry' ] }, function ( place, status ) {
				var ok = google.maps.places && status === google.maps.places.PlacesServiceStatus.OK;
				resolve( ( ok && place && place.geometry ) ? place.geometry.location : null );
			} );
		} );
	}

	function resolvePosition( marker, geocoder, service ) {
		if ( 'number' === typeof marker.lat && 'number' === typeof marker.lng ) {
			return Promise.resolve( new google.maps.LatLng( marker.lat, marker.lng ) );
		}
		if ( marker.placeId && service ) {
			return placeLocation( service, marker.placeId );
		}
		if ( marker.address ) {
			return geocode( geocoder, { address: marker.address } );
		}
		return Promise.resolve( null );
	}

	function buildInfoContent( marker ) {
		var html = '<div class="egm-info">';
		if ( marker.title ) {
			html += '<strong>' + escapeHtml( marker.title ) + '</strong>';
		}
		if ( marker.text ) {
			html += '<span>' + escapeHtml( marker.text ) + '</span>';
		}
		html += '</div>';
		return html;
	}

	function buildMap( el ) {
		if ( el.dataset.egmInit ) {
			return;
		}
		el.dataset.egmInit = '1';

		var config = parseConfig( el );
		var options = config.options || {};
		var markers = Array.isArray( config.markers ) ? config.markers : [];

		var geocoder = new google.maps.Geocoder();
		var service = google.maps.places
			? new google.maps.places.PlacesService( document.createElement( 'div' ) )
			: null;

		var lookups = markers.map( function ( marker ) {
			return resolvePosition( marker, geocoder, service ).then( function ( position ) {
				return position ? { marker: marker, position: position } : null;
			} );
		} );

		Promise.all( lookups ).then( function ( resolved ) {
			resolved = resolved.filter( Boolean );

			var center = options.center || null;
			if ( ! center && resolved.length ) {
				center = resolved[0].position;
			}
			if ( ! center ) {
				center = { lat: 0, lng: 0 };
			}

			var map = new google.maps.Map( el, {
				center: center,
				zoom: options.zoom || 14,
				mapTypeId: options.mapTypeId || 'roadmap',
			} );

			var bounds = new google.maps.LatLngBounds();
			var infoWindow = new google.maps.InfoWindow();

			resolved.forEach( function ( item ) {
				var pin = new google.maps.Marker( {
					map: map,
					position: item.position,
					title: item.marker.title || '',
				} );
				bounds.extend( item.position );

				if ( item.marker.title || item.marker.text ) {
					pin.addListener( 'click', function () {
						infoWindow.setContent( buildInfoContent( item.marker ) );
						infoWindow.open( map, pin );
					} );
				}
			} );

			if ( resolved.length > 1 ) {
				map.fitBounds( bounds );
			}
		} );
	}

	function initAll() {
		if ( ! ( window.google && window.google.maps ) ) {
			return;
		}
		var maps = document.querySelectorAll( '.egm-map' );
		for ( var i = 0; i < maps.length; i++ ) {
			buildMap( maps[ i ] );
		}
	}

	function observe() {
		if ( window.egmObserver || ! window.MutationObserver ) {
			return;
		}
		window.egmObserver = new MutationObserver( function () {
			initAll();
		} );
		window.egmObserver.observe( document.body, { childList: true, subtree: true } );
	}

	// Google Maps calls this when the API has loaded.
	window.egmInitMaps = function () {
		initAll();
		observe();
	};

	// If the API is already present (e.g. re-enqueued in the editor), init now.
	if ( window.google && window.google.maps ) {
		window.egmInitMaps();
	}
}());

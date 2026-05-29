/**
 * Easy G Maps - editor block (no build step).
 *
 * A dynamic block: edit() shows sidebar controls plus a live ServerSideRender
 * preview (which reuses the PHP renderer), and save() returns null so the
 * server renders the final output.
 */
( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	'use strict';

	var el = element.createElement;
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var Notice = components.Notice;
	var ServerSideRender = serverSideRender;
	var __ = i18n.__;

	var hasKey = !! ( window.egmBlock && window.egmBlock.hasKey );

	registerBlockType( 'easy-g-maps/map', {
		apiVersion: 2,
		title: __( 'Easy G Map', 'easy-g-maps' ),
		description: __( 'Embed a Google Map by address, coordinates, or Place ID.', 'easy-g-maps' ),
		icon: 'location-alt',
		category: 'embed',
		attributes: {
			mapName: { type: 'string', default: '' },
			address: { type: 'string', default: '' },
			lat: { type: 'string', default: '' },
			lng: { type: 'string', default: '' },
			placeId: { type: 'string', default: '' },
			zoom: { type: 'number', default: 14 },
			height: { type: 'string', default: '400px' },
			mapType: { type: 'string', default: 'roadmap' },
			marker: { type: 'boolean', default: true },
			markerTitle: { type: 'string', default: '' },
			markerText: { type: 'string', default: '' }
		},

		edit: function ( props ) {
			var a = props.attributes;
			var set = function ( key ) {
				return function ( value ) {
					var update = {};
					update[ key ] = value;
					props.setAttributes( update );
				};
			};

			var inspector = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: __( 'Location', 'easy-g-maps' ), initialOpen: true },
					el( TextControl, {
						label: __( 'Address', 'easy-g-maps' ),
						value: a.address,
						onChange: set( 'address' ),
						help: __( 'A street address or place name (geocoded in the browser).', 'easy-g-maps' )
					} ),
					el( TextControl, { label: __( 'Latitude', 'easy-g-maps' ), value: a.lat, onChange: set( 'lat' ) } ),
					el( TextControl, { label: __( 'Longitude', 'easy-g-maps' ), value: a.lng, onChange: set( 'lng' ) } ),
					el( TextControl, { label: __( 'Google Place ID', 'easy-g-maps' ), value: a.placeId, onChange: set( 'placeId' ) } )
				),
				el(
					PanelBody,
					{ title: __( 'Map', 'easy-g-maps' ), initialOpen: false },
					el( RangeControl, { label: __( 'Zoom', 'easy-g-maps' ), value: a.zoom, min: 1, max: 21, onChange: set( 'zoom' ) } ),
					el( SelectControl, {
						label: __( 'Map type', 'easy-g-maps' ),
						value: a.mapType,
						options: [
							{ label: __( 'Road map', 'easy-g-maps' ), value: 'roadmap' },
							{ label: __( 'Satellite', 'easy-g-maps' ), value: 'satellite' },
							{ label: __( 'Hybrid', 'easy-g-maps' ), value: 'hybrid' },
							{ label: __( 'Terrain', 'easy-g-maps' ), value: 'terrain' }
						],
						onChange: set( 'mapType' )
					} ),
					el( TextControl, {
						label: __( 'Height', 'easy-g-maps' ),
						value: a.height,
						onChange: set( 'height' ),
						help: __( 'e.g. 400px or 60vh.', 'easy-g-maps' )
					} ),
					el( TextControl, {
						label: __( 'Map name', 'easy-g-maps' ),
						value: a.mapName,
						onChange: set( 'mapName' ),
						help: __( 'Used by PHP filters to add more markers in code.', 'easy-g-maps' )
					} )
				),
				el(
					PanelBody,
					{ title: __( 'Marker', 'easy-g-maps' ), initialOpen: false },
					el( ToggleControl, { label: __( 'Show marker', 'easy-g-maps' ), checked: a.marker, onChange: set( 'marker' ) } ),
					el( TextControl, { label: __( 'Marker title', 'easy-g-maps' ), value: a.markerTitle, onChange: set( 'markerTitle' ) } ),
					el( TextControl, { label: __( 'Marker text', 'easy-g-maps' ), value: a.markerText, onChange: set( 'markerText' ) } )
				)
			);

			var hasLocation = a.address || a.placeId || ( a.lat && a.lng );

			var preview;
			if ( ! hasKey ) {
				preview = el(
					Notice,
					{ status: 'warning', isDismissible: false },
					__( 'Add your Google Maps API key in Settings → Easy G Maps to see a preview.', 'easy-g-maps' )
				);
			} else if ( ! hasLocation ) {
				preview = el(
					Notice,
					{ status: 'info', isDismissible: false },
					__( 'Enter an address, coordinates, or a Place ID to display a map.', 'easy-g-maps' )
				);
			} else {
				preview = el( ServerSideRender, { block: 'easy-g-maps/map', attributes: a } );
			}

			return el( 'div', useBlockProps(), inspector, preview );
		},

		save: function () {
			return null;
		}
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
) );

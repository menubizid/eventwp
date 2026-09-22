/**
 * EventWP — Gutenberg block editor scripts (vanilla ES5-safe).
 * Registers toolbar-less blocks whose controls live in the inspector.
 */
(function (wp) {
	'use strict';
	if (!wp || !wp.blocks) {
		return;
	}
	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : null;
	var RichText = wp.blockEditor ? wp.blockEditor.RichText : null;
	var createElement = wp.element.createElement;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var __ = wp.i18n.__;

	function preview(label, sub) {
		return el(
			'div',
			{ style: { padding: '28px', background: '#0b1224', color: '#fff', borderRadius: '8px', textAlign: 'center', fontSize: '15px' } },
			el('div', { style: { fontSize: '20px', fontWeight: 700, marginBottom: '6px' } }, label),
			el('div', { style: { color: '#8b93b0', fontSize: '13px' } }, sub)
		);
	}

	/* Block: Daftar Event */
	registerBlockType('eventwp/events', {
		edit: function (props) {
			var attrs = props.attributes;
			return el(
				'div',
				null,
				InspectorControls &&
					el(
						InspectorControls,
						null,
						el(TextControl, { label: __('Judul', 'eventwp'), value: attrs.title, onChange: function (v) { props.setAttributes({ title: v }); } }),
						el(TextControl, { label: __('Filter kategori (slug)', 'eventwp'), value: attrs.category, onChange: function (v) { props.setAttributes({ category: v }); } }),
						el(RangeControl, { label: __('Jumlah event', 'eventwp'), min: 1, max: 24, value: attrs.limit, onChange: function (v) { props.setAttributes({ limit: v }); } }),
						el(ToggleControl, { label: __('Tampilkan filter', 'eventwp'), checked: attrs.filter, onChange: function (v) { props.setAttributes({ filter: v }); } })
					),
				preview('EventWP — Daftar Event', __('Grid event up-to-date dengan filter kategori.', 'eventwp'))
			);
		},
		save: function () {
			return null; // server-rendered
		}
	});

	/* Block: Detail Event */
	registerBlockType('eventwp/event-details', {
		edit: function (props) {
			return el(
				'div',
				null,
				InspectorControls &&
					el(
						InspectorControls,
						null,
						el(TextControl, { label: __('ID Event', 'eventwp'), type: 'number', value: String(props.attributes.eventId || ''), onChange: function (v) { props.setAttributes({ eventId: parseInt(v, 10) || 0 }); } })
					),
				preview('EventWP — Detail Event', __('Detail event dengan tombol beli tiket.', 'eventwp'))
			);
		},
		save: function () {
			return null;
		}
	});

	/* Block: App */
	registerBlockType('eventwp/app', {
		edit: function (props) {
			return el(
				'div',
				null,
				InspectorControls &&
					el(
						InspectorControls,
						null,
						el(TextControl, { label: __('Tinggi (CSS)', 'eventwp'), value: props.attributes.height, onChange: function (v) { props.setAttributes({ height: v }); } })
					),
				preview('EventWP — App Sport Event', __('Aplikasi web mobile full: login, checkout, tiket QR.', 'eventwp'))
			);
		},
		save: function () {
			return null;
		}
	});
})(window.wp);

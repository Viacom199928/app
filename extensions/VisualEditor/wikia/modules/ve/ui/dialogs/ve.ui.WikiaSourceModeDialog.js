
/*!
 * VisualEditor user interface WikiaSourceModeDialog class.
 */

/*global mw*/

/**
 * Dialog for editing wikitext in source mode.
 *
 * @class
 * @extends ve.ui.MWDialog
 *
 * @constructor
 * @param {ve.ui.Surface} surface
 * @param {Object} [config] Config options
 */
ve.ui.WikiaSourceModeDialog = function VeUiWikiaSourceModeDialog( surface, config ) {
	// Parent constructor
	ve.ui.MWDialog.call( this, surface, config );
};

/* Inheritance */

ve.inheritClass( ve.ui.WikiaSourceModeDialog, ve.ui.MWDialog );

/* Static Properties */

ve.ui.WikiaSourceModeDialog.static.name = 'wikiaSourceMode';

ve.ui.WikiaSourceModeDialog.static.titleMessage = 'wikia-visualeditor-dialog-wikiasourcemode-title';

ve.ui.WikiaSourceModeDialog.static.icon = 'source';

/* Methods */

ve.ui.WikiaSourceModeDialog.prototype.initialize = function () {
	// Parent method
	ve.ui.MWDialog.prototype.initialize.call( this );

	// Properties
	this.sourceModeTextarea = new ve.ui.TextInputWidget({
		'$$': this.frame.$$,
		'multiline': true
	});
	this.applyButton = new ve.ui.ButtonWidget( {
		'$$': this.frame.$$,
		'label': ve.msg( 'wikia-visualeditor-dialog-wikiasourcemode-apply-button' ),
		'flags': ['primary']
	} );
	this.$helpLink = this.$$('<a>')
		.addClass( 've-ui-wikiaSourceModeDialog-helplink' )
		.attr( {
			'href': new mw.Title( ve.msg( 'wikia-visualeditor-dialog-wikiasourcemode-help-link' ) ).getUrl(),
			'target': '_blank'
		} )
		.text( ve.msg( 'wikia-visualeditor-dialog-wikiasourcemode-help-text' ) );

	// Events
	this.applyButton.connect( this, { 'click': [ 'onApply' ] } );

	// Initialization
	this.$body.append( this.sourceModeTextarea.$ );
	this.$foot.append( this.$helpLink, this.applyButton.$ );
	this.frame.$content.addClass( 've-ui-wikiaSourceModeDialog-content' );
};

/**
 * Handle opening the dialog.
 *
 * @method
 */
ve.ui.WikiaSourceModeDialog.prototype.onOpen = function () {
	var doc = this.surface.getModel().getDocument();

	// Parent method
	ve.ui.MWDialog.prototype.onOpen.call( this );

	this.$frame.startThrobbing();
	this.surface.getTarget().serialize(
		ve.dm.converter.getDomFromData( doc.getFullData(), doc.getStore(), doc.getInternalList() ),
		ve.bind( this.onSerialize, this )
	);
};

/**
 * @method
 * @param {string} wikitext Wikitext returned from Parsoid.
 */
ve.ui.WikiaSourceModeDialog.prototype.onSerialize = function ( wikitext ) {
	this.sourceModeTextarea.setValue( wikitext );
	this.sourceModeTextarea.$input.focus();
	this.$frame.stopThrobbing();
};

/**
 * @method
 */
ve.ui.WikiaSourceModeDialog.prototype.onApply = function () {
	this.$frame.startThrobbing();
	this.parse();
};

/**
 * @method
 */
ve.ui.WikiaSourceModeDialog.prototype.getWikitext = function() {
	return this.sourceModeTextarea.getValue();
};

/**
 * @method
 */
ve.ui.WikiaSourceModeDialog.prototype.parse = function( ) {
	$.ajax( {
		'url': mw.util.wikiScript( 'api' ),
		'data': {
			'action': 'visualeditor',
			'paction': 'parsewt',
			'page': mw.config.get( 'wgRelevantPageName' ),
			'wikitext': this.sourceModeTextarea.getValue(),
			'token': mw.user.tokens.get( 'editToken' ),
			'format': 'json'
		},
		'dataType': 'json',
		'type': 'POST',
		// Wait up to 100 seconds before giving up
		'timeout': 100000,
		'cache': 'false',
		'success': ve.bind( this.onParseSuccess, this ),
		'error': ve.bind( this.onParseError, this ),
		'complete': ve.bind( function() {
			this.$frame.stopThrobbing();
		} , this )
	} );
};

/**
 * @method
 */
ve.ui.WikiaSourceModeDialog.prototype.onParseSuccess = function( response ) {
	var surfaceModel, doc, newDoc, merge, tx;
	if ( !response || response.error || !response.visualeditor || response.visualeditor.result !== 'success' ) {
		return this.onParseError.call( this );
	}

	surfaceModel = this.surface.getModel();

	doc = surfaceModel.getDocument();
	newDoc = new ve.dm.Document ( ve.createDocumentFromHtml( response.visualeditor.content ) );

	// TODO: Eventually stores and internalLists should be merged (and data remapped) but currently that
	// functionality does not work correct.

	// merge store
	//merge = doc.getStore().merge( newDoc.getStore() );
	//newDoc.data.remapStoreIndexes( merge );
	doc.store = newDoc.store;

	// merge internal list
	//merge = doc.internalList.merge( newDoc.internalList, 0 );
	//newDoc.data.remapInteralListIndexes( merge.mapping );
	doc.internalList = newDoc.internalList;

	tx = new ve.dm.Transaction();
	tx.pushReplace( doc, 0, doc.data.data.length, newDoc.data.data,
		// get all except the last item
		( newDoc.metadata.data.length ? newDoc.metadata.data.slice( 0, -1 ) : [] )
	);
	tx.pushReplaceMetadata(
		// only send the last items
		( doc.metadata.length ? doc.metadata.data[doc.metadata.data.length - 1] : [] ),
		( newDoc.metadata.length ? newDoc.metadata.data[newDoc.metadata.data.length - 1] : [] )
	);
	surfaceModel.change( tx, new ve.Range( 0 ) );
	this.surface.getTarget().setWikitext( this.sourceModeTextarea.getValue() );
	surfaceModel.clearHistory();
	this.close();
	this.sourceModeTextarea.setValue( '' );
};

/**
 * @method
 */
ve.ui.WikiaSourceModeDialog.prototype.onParseError = function ( ) {
	// TODO: error handling?
};

ve.ui.dialogFactory.register( ve.ui.WikiaSourceModeDialog );

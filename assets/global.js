/**
 * JavaScript that handles batch processing.
 */
;( function( $ ) {
	var batchProcess = {
		/**
		 * Init.
		 */
		init: function() {
			this.cacheSelectors();
			this.bind();
		},

		/**
		 * Cache various selectors we will be using.
		 */
		cacheSelectors: function() {
			this.$submit       = $( '.batch-processing-form #submit' );
			this.$overlay      = $( '.batch-processing-overlay' );
			this.$batch_option = $( '.batch-process-option' );
		},

		/**
		 * Bind any actions to elements.
		 */
		bind: function() {
			this.$submit.on( 'click', this.submit.bind( this ) );
			this.$submit.prop( 'disabled', true );
			this.$batch_option.on( 'change', this.handleBatchSelection.bind( this ) );
		},

		/**
		 * Run a selected batch.
		 * 
		 * @param {event} e Click event.
		 */
		submit: function( e ) {
			e.preventDefault();

			this.toggleOverlay();
		},

		/**
		 * Toggle the overlay that holds our batch process information.
		 */
		toggleOverlay: function() {
			this.$overlay.toggleClass( 'is-open' );
		},

		/**
		 * Enable submit button on batch selection.
		 */
		handleBatchSelection: function() {
			this.$submit.prop( 'disabled', false );
		}
	};

	batchProcess.init();
} )( jQuery );

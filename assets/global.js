/**
 * JavaScript that handles batch processing.
 */
;( function( $ ) {
	var batchProcess = {
		/**
		 * Init.
		 */
		init : function() {
			this.cacheSelectors();
			this.bind();
		},

		/**
		 * Cache various selectors we will be using.
		 */
		cacheSelectors : function() {
			this.$submit        = $( '.batch-processing-form #submit' );
			this.$overlay       = $( '.batch-processing-overlay' );
			this.$batch_option  = $( '.batch-process-option' );
			this.$close_overlay = $( '.batch-processing-overlay .close' );
		},

		/**
		 * Bind any actions to elements.
		 */
		bind : function() {
			this.$submit.on( 'click', this.submit.bind( this ) );
			this.$close_overlay.on( 'click', this.toggleOverlay.bind( this ) );
			this.disableSubmitButton();
			this.$batch_option.on( 'change', this.enableSubmitButton.bind( this ) );
		},

		/**
		 * Run a selected batch.
		 * 
		 * @param {event} e Click event.
		 */
		submit : function( e ) {
			e.preventDefault();
			this.disableSubmitButton();
			this.toggleOverlay();
		},

		/**
		 * Toggle the overlay that holds our batch process information.
		 */
		toggleOverlay : function() {
			this.$overlay.toggleClass( 'is-open' );

			if ( ! this.$overlay.hasClass( 'is-open' ) ) {
				this.enableSubmitButton();
			}
		},

		/**
		 * Enable submit button.
		 */
		enableSubmitButton : function() {
			this.$submit.prop( 'disabled', false );
		},

		/**
		 * Disable submit button.
		 */
		disableSubmitButton : function() {
			this.$submit.prop( 'disabled', true );
		}
	};

	batchProcess.init();
} )( jQuery );

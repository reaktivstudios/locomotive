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
			this.$form            = $( '.batch-processing-form' );
			this.$submit          = this.$form.find( '#submit' );
			this.$overlay         = $( '.batch-processing-overlay' );
			this.$overlay_inner   = $( '.batch-overlay__inner' );
			this.$batch_option    = $( '.batch-process-option' );
			this.$close_overlay   = $( '.batch-processing-overlay .close' );
			this.$batch_progress  = $( '.progress-bar' );
			this.$visual_progress = $( '.progress-bar__visual' );
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
		 * Toggle the overlay that holds our batch process information.
		 */
		toggleOverlay : function() {
			this.$overlay.toggleClass( 'is-open' );

			if ( ! this.$overlay.hasClass( 'is-open' ) ) {
				this.enableSubmitButton();
				this.enableBatchOptions();
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
		},

		/**
		 * Disable batch options.
		 */
		disableBatchOptions : function() {
			this.$batch_option.prop( 'disabled', true );
		},

		/**
		 * Enable batch options.
		 */
		enableBatchOptions : function() {
			this.$batch_option.prop( 'disabled', false );
		},

		/**
		 * Run a selected batch.
		 * 
		 * @param {event} e Click event.
		 */
		submit : function( e ) {
			e.preventDefault();
			this.disableSubmitButton();
			this.disableBatchOptions();
			this.toggleOverlay();
			this.run( 1 );
		},

		/**
		 * Batch run process.
		 */
		run : function( current_step ) {
			var _this = this,
				$batch_start_msg = $( '<h2>Starting batch process</h2>' );

			if ( 1 === current_step ) {
				_this.$overlay_inner.html( $batch_start_msg );
			}

			$.ajax( {
				type: 'POST',
				url: batch.ajaxurl,
				data: {
					batch_process: _this.$form.find( 'input:radio[name=batch_process]:checked').val(),
					nonce: batch.nonce,
					step: current_step,
					action: 'run_batch',
				},
				dataType: 'json',
				success: function( response ) {
					if ( response.success ) {
						// Fill our overlay with relevant information.
						var results_template = wp.template( 'batch-processing-results' );
						_this.$overlay_inner.html( results_template( response ) );

						// Show visual progresss.
						$( '.batch-overlay__inner' ).find( '.progress-bar__visual' ).css( {
							'width': response.progress + '%',
						} );

						if ( response.current_step !== response.total_steps && 'running' === response.status.toLowerCase() ) {
							_this.run( current_step + 1 );
						}
					} else {
						_this.$overlay_inner.html( response.error );
					}
				}
			} ).fail( function ( response ) {
				alert( 'Something went wrong!' );
			});
		},
	};

	batchProcess.init();
} )( jQuery );

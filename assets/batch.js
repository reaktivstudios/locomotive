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
			this.$reset           = this.$form.find( '#reset' );
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
			this.$reset.on( 'click', this.reset.bind( this ) );
			this.$close_overlay.on( 'click', this.toggleOverlay.bind( this ) );
			this.disableSubmitButtons();
			this.$batch_option.on( 'change', this.enableSubmitButtons.bind( this ) );
		},

		/**
		 * Toggle the overlay that holds our batch process information.
		 */
		toggleOverlay : function() {
			this.$overlay.toggleClass( 'is-open' );

			if ( ! this.$overlay.hasClass( 'is-open' ) ) {
				this.enableSubmitButtons();
				this.enableBatchOptions();
			}
		},

		/**
		 * Enable submit button.
		 */
		enableSubmitButtons : function() {
			this.$submit.prop( 'disabled', false );
			this.$reset.prop( 'disabled', false );
		},

		/**
		 * Disable submit button.
		 */
		disableSubmitButtons : function() {
			this.$submit.prop( 'disabled', true );
			this.$reset.prop( 'disabled', true );
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
			this.disableSubmitButtons();
			this.disableBatchOptions();
			this.toggleOverlay();
			this.run( 1 );
		},

		/**
		 * Reset a selected batch.
		 *
		 * @param {event} e Click event.
		 */
		reset : function( e ) {
			e.preventDefault();
			this.disableSubmitButtons();
			this.reset_status();
		},

		/**
		 * Reset batch status.
		 */
		reset_status : function() {
			var _this = this,
				batch_slug =_this.$form.find( 'input:radio[name=batch_process]:checked').val();

			$.ajax( {
				type: 'POST',
				url: batch.ajaxurl,
				data: {
					batch_process: batch_slug,
					nonce: batch.nonce,
					action: 'reset_batch',
				},
				dataType: 'json',
				success: function( response ) {
					if ( response.success ) {
						alert( 'Reset batch successfully.' );

						var batch_label = $( 'label[for="' + batch_slug + '"' );
						batch_label.find( 'small' ).text( 'last run: just now | status: reset' );
					} else {
						alert( 'Unable to reset batch.' );
					}
				}
			} ).fail( function ( response ) {
				alert( 'Something went wrong!' );
			});

			this.enableSubmitButtons();
		},

		/**
		 * Batch run process.
		 */
		run : function( current_step ) {
			var _this = this,
				$batch_start_msg = $( '<h2>Starting batch process</h2>' ),
				batch_slug =_this.$form.find( 'input:radio[name=batch_process]:checked').val();

			if ( 1 === current_step ) {
				_this.$overlay_inner.html( $batch_start_msg );
			}

			$.ajax( {
				type: 'POST',
				url: batch.ajaxurl,
				data: {
					batch_process: batch_slug,
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
						} else {
							var batch_label = $( 'label[for="' + batch_slug + '"' );
							batch_label.find( 'small' ).text( 'last run: just now | status: ' + response.status.toLowerCase() );
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

var React = require( 'react' );
var ReactDOM = require( 'react-dom' );
var $ = jQuery; // We are loading this file after jQuery through `wp_enqueue_script`.

/**
 * Application components.
 */
import BatchPicker from './components/BatchPicker';
import Modal from './components/Modal';

/**
 * Our Locomotive App.
 */
var App = React.createClass( {
	getInitialState : function () {
		return {
			// Batches that can be run. Loaded through `wp_localize_script()`.
			batches: batch.batches,
			pageTitle: batch.pageTitle,

			// Object to hold data relating to running a migration (in the modal).
			processing: {
				active: false,
				batch: false,

				// Remote data is data that is retrieved via Ajax calls during a
				// batch process.
				remoteData: {
					batchTitle: '',
					status: '',
					error: '',
					progress: 0,
					currentStep: 0,
					totalSteps: 0,
					totalNumResults: 0
				}
			}
		};
	},

	/**
	 * Update which batch is currently selected.
	 *
	 * @param key Currently selected batch key.
	 */
	updateSelectedBatch : function ( key ) {
		if ( this.state.processing.active ) {
			return;
		}

		this.state.processing.batch = key;
		this.setState( { processing: this.state.processing } );
	},

	/**
	 * Function to handle flipping the switches for the modal.
	 *
	 * @param bool active Whether or not we are processing a batch.
	 */
	toggleProcessing : function ( active ) {
		if ( false === active || true === active ) {
			this.state.processing.active = active;
			this.setState( { processing: this.state.processing } );
		}
	},

	/**
	 * Run the currently selected batch process.
	 */
	runBatch : function ( currentStep ) {
		if ( '' === this.state.processing.batch ) {
			return;
		}

		var self = this,
			batchSlug = self.state.processing.batch;

		// If we open the modal and it was previously complete, clear it.
		if ( 100 === this.state.processing.remoteData.progress ) {
			this.state.processing.batch = '';
			this.state.processing.remoteData = {
				progress: 0
			};

			this.setState( { processing: this.state.processing } );
		}

		$.ajax( {
			type: 'POST',
			url: batch.ajaxurl,
			data: {
				batchProcess: batchSlug,
				nonce:         batch.nonce,
				step:          currentStep,
				action:        'run_batch',
			},
			dataType: 'json',
			success: function ( response ) {
				// Update our state with the processing status and progress, which will update the modal.
				self.state.processing.batch = batchSlug;
				self.state.processing.remoteData = {
					batchTitle:       response.batch,
					status:            response.status,
					progress:          response.progress,
					currentStep:      response.currentStep,
					totalSteps:       response.totalSteps,
					totalNumResults: response.totalNumResults
				};

				// Update our batches, which will update the batch listing.
				self.state.batches[ batchSlug ].lastRun = 'just ran';
				self.state.batches[ batchSlug ].status = self.state.processing.remoteData.status;

				// Check for errors.
				if ( response.error ) {
					self.state.processing.remoteData.error = response.error;
					self.setState( { processing: self.state.processing } );
				}

				self.setState( {
					processing: self.state.processing,
					batches: self.state.batches
				} );

				// Determine if we have to run another step in the batch. Checks if there are more steps
				// that need to run and makes sure the 'status' from the server is still 'running'.
				if ( response.success ) {
					if ( response.currentStep !== response.totalSteps && 'running' === response.status.toLowerCase() ) {
						self.runBatch( currentStep + 1 );
					}
				} else {
					alert( 'Batch failed.' );
				}
			}
		} ).fail( function () {
			alert( 'Something went wrong.' );
		} );

		this.toggleProcessing( true );
	},

	/**
	 * Reset the selected batch process.
	 */
	resetBatch : function () {
		if ( '' === this.state.processing.batch ) {
			return;
		}

		var self = this,
			batchSlug = self.state.processing.batch.toString();

		$.ajax( {
			type: 'POST',
			url: batch.ajaxurl,
			data: {
				batchProcess: batchSlug,
				nonce:         batch.nonce,
				action:        'reset_batch',
			},
			dataType: 'json',
			success: function ( response ) {
				if ( response.success ) {
					// Update our batches, which will update the batch listing.
					self.state.batches[ self.state.processing.batch ].lastRun = 'never';
					self.state.batches[ self.state.processing.batch ].status = 'new';

					self.setState( {
						processing: self.state.processing,
						batches: self.state.batches
					} );
				} else {
					alert( 'Reset batch failed.' );
				}
			}
		} ).fail( function () {
			alert( 'Something went wrong.' );
		} );
	},

	/**
	 * Decides if `Run Batch` button is enabled or disabled.
	 *
	 * @returns {boolean} Can we run a batch?
	 */
	canInteractWithBatch : function () {
		// Default to being able to run a batch.
		var canRun = true;
		var sProcessing = this.state.processing;

		// If we don't have a batch selected.
		if ( false === sProcessing.batch ) {
			canRun = false;
		}

		// If we are have the modal open.
		if ( sProcessing.active ) {
			canRun = false;
		}

		// If we are currently processing a batch and there are results.
		if ( sProcessing.remoteData.currentStep !== sProcessing.remoteData.totalSteps && sProcessing.remoteData.totalNumResults !== 0 ) {
			canRun = false;
		}

		return canRun;
	},

	/**
	 * Render our batch application.
	 *
	 * @returns {JSX}
	 */
	render : function () {
		var selectedBatch = '';
		if ( this.state.processing.batch ) {
			selectedBatch = this.state.batches[ this.state.processing.batch ].name;
		}

		return (
			<div className="wrap">
				<h1>{ this.state.pageTitle }</h1>
				<BatchPicker
					batches={ this.state.batches }
					canInteractWithBatch={ this.canInteractWithBatch() }
					updateSelectedBatch={ this.updateSelectedBatch }
					runBatch={ this.runBatch }
					resetBatch={ this.resetBatch }
				/>

				<Modal
					isOpen={ this.state.processing.active }
					selectedBatch={ selectedBatch }
					batchInfo={ this.state.processing.remoteData }
					toggleProcessing={ this.toggleProcessing }
				/>
			</div>
		);
	}
} );

ReactDOM.render( <App />, document.getElementById( 'batch-main' ) );

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
    getInitialState : function() {
        return {
            // Batches that can be run. Loaded through `wp_localize_script()`.
            batches: batch.batches,
            page_title: batch.page_title,

            // Object to hold data relating to running a migration (in the modal).
            processing: {
                active: false,
                batch: false,

                // Remote data is data that is retrieved via Ajax calls during a
                // batch process.
                remote_data: {
                    batch_title: '',
                    status: '',
                    error: '',
                    progress: 0,
                    current_step: 0,
                    total_steps: 0,
                    total_num_results: 0
                }
            }
        };
    },

    /**
     * Update which batch is currently selected.
     *
     * @param key Currently selected batch key.
     */
    updateSelectedBatch : function( key ) {
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
    toggleProcessing : function( active ) {
        if ( false === active || true === active ) {
            this.state.processing.active = active;
            this.setState( { processing: this.state.processing } );
        }
    },

    /**
     * Run the currently selected batch process.
     */
    runBatch : function( current_step ) {
        if ( '' === this.state.processing.batch ) {
            return;
        }

        var self = this,
            batch_slug = self.state.processing.batch;

        // If we open the modal and it was previously complete, clear it.
        if ( 100 === this.state.processing.remote_data.progress ) {
            this.state.processing.batch = '';
            this.state.processing.remote_data = {
                progress: 0
            };

            this.setState( { processing: this.state.processing } );
        }

        $.ajax( {
            type: 'POST',
            url: batch.ajaxurl,
            data: {
                batch_process: batch_slug,
                nonce:         batch.nonce,
                step:          current_step,
                action:        'run_batch',
            },
            dataType: 'json',
            success: function( response ) {
                // Update our state with the processing status and progress, which will update the modal.
                self.state.processing.batch = batch_slug;
                self.state.processing.remote_data = {
                    batch_title:       response.batch,
                    status:            response.status,
                    progress:          response.progress,
                    current_step:      response.current_step,
                    total_steps:       response.total_steps,
                    total_num_results: response.total_num_results
                };

                // Update our batches, which will update the batch listing.
                self.state.batches[ batch_slug ].last_run = 'just ran';
                self.state.batches[ batch_slug ].status = self.state.processing.remote_data.status;

                // Check for errors.
                if ( response.error ) {
                    self.state.processing.remote_data.error = response.error;
                    self.setState( { processing: self.state.processing } );
                }

                self.setState( {
                    processing: self.state.processing,
                    batches: self.state.batches
                } );

                // Determine if we have to run another step in the batch. Checks if there are more steps
                // that need to run and makes sure the 'status' from the server is still 'running'.
                if ( response.success ) {
                    if ( response.current_step !== response.total_steps && 'running' === response.status.toLowerCase() ) {
                        self.runBatch( current_step + 1 );
                    }
                } else {
                    alert( 'Batch failed.' );
                }
            }
        } ).fail( function ( response ) {
            alert( 'Something went wrong.' );
        });

        this.toggleProcessing( true );
    },

    /**
     * Reset the selected batch process.
     */
    resetBatch : function() {
        if ( '' === this.state.processing.batch ) {
            return;
        }

        var self = this,
            batch_slug = self.state.processing.batch.toString();

        $.ajax( {
            type: 'POST',
            url: batch.ajaxurl,
            data: {
                batch_process: batch_slug,
                nonce:         batch.nonce,
                action:        'reset_batch',
            },
            dataType: 'json',
            success: function( response ) {
                if ( response.success ) {
                    // Update our batches, which will update the batch listing.
                    self.state.batches[ self.state.processing.batch ].last_run = 'never';
                    self.state.batches[ self.state.processing.batch ].status = 'new';

                    self.setState( {
                        processing: self.state.processing,
                        batches: self.state.batches
                    } );
                } else {
                    alert( 'Reset batch failed.' );
                }
            }
        } ).fail( function ( response ) {
            alert( 'Something went wrong.' );
        });
    },

    /**
     * Decides if `Run Batch` button is enabled or disabled.
     *
     * @returns {boolean} Can we run a batch?
     */
    canInteractWithBatch : function() {
        // Default to being able to run a batch.
        var canRun = true;
        var s_processing = this.state.processing;

        // If we don't have a batch selected.
        if ( false === s_processing.batch ) {
            canRun = false;
        }

        // If we are have the modal open.
        if ( s_processing.active ) {
            canRun = false;
        }

        // If we are currently processing a batch and there are results.
        if ( s_processing.remote_data.current_step !== s_processing.remote_data.total_steps && s_processing.remote_data.total_num_results !== 0 ) {
            canRun = false;
        }

        return canRun;
    },

    /**
     * Render our batch application.
     *
     * @returns {JSX}
     */
    render : function() {
        var selectedBatch = '';
        if ( this.state.processing.batch ) {
            selectedBatch = this.state.batches[ this.state.processing.batch ].name;
        }

        return (
            <div className="wrap">
                <h1>{ this.state.page_title }</h1>
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
                    batchInfo={ this.state.processing.remote_data }
                    toggleProcessing={ this.toggleProcessing }
                />
            </div>
        )
    }
} );

ReactDOM.render( <App/>, document.getElementById( 'batch-main' ) );

var React = require( 'react' );
var ReactDOM = require( 'react-dom' );
var CSSTransitionGroup = require( 'react-addons-css-transition-group' );
var $ = jQuery; // We are loading this file after jQuery through `wp_enqueue_script`.

/**
 * Our Batch Processing App.
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
                // batch process..
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

        var _this = this,
            batch_slug = _this.state.processing.batch.toString();

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
                _this.state.processing.remote_data = {
                    batch_title:       response.batch,
                    status:            response.status,
                    progress:          response.progress,
                    current_step:      response.current_step,
                    total_steps:       response.total_steps,
                    total_num_results: response.total_num_results
                };

                // Update our batches, which will update the batch listing.
                _this.state.batches[ _this.state.processing.batch ].last_run = 'just ran';
                _this.state.batches[ _this.state.processing.batch ].status = _this.state.processing.remote_data.status;

                // Check for errors.
                if ( response.error ) {
                    _this.state.processing.remote_data.error = response.error;
                    _this.setState( { processing: _this.state.processing } );
                }

                _this.setState( {
                    processing: _this.state.processing,
                    batches: _this.state.batches
                } );

                // Determine if we have to run another step in the batch. Checks if there are more steps
                // that need to run and makes sure the 'status' from the server is still 'running'.
                if ( response.success ) {
                    if ( response.current_step !== response.total_steps && 'running' === response.status.toLowerCase() ) {
                        _this.runBatch( current_step + 1 );
                    }
                }
            }
        } ).fail( function ( response ) {
            alert( 'Something went wrong!' );
        });

        this.toggleProcessing( true );
    },

    resetBatch : function() {

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
                <h2>{ this.state.page_title }</h2>
                <BatchPicker
                    batches={ this.state.batches }
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

/**
 * Potential Batch listings.
 */
var BatchPicker = React.createClass( {
    /**
     * Render an individual batch option.
     *
     * @param key Used to get the right batch from this.props.batches.
     * @returns {JSX}
     */
    renderBatchOption : function( key ) {
        var batch = this.props.batches[ key ];

        return (
            <li key={ key } onClick={ this.props.updateSelectedBatch.bind( null, key ) }>
                <input type="radio" id={ key } name="batch_process" className="batch-process-option" value="test-another-batch" />
                    <label htmlFor={ key }>
                        { batch.name } <small>last run: { batch.last_run } | status: { batch.status }</small>
                    </label>
            </li>
        )
    },

    /**
     * Render the batch processes list.
     *
     * @returns {JSX}
     */
    render : function() {
        return (
            <div className="batch-picker">
                <ul className="batch-processes">
                    { Object.keys( this.props.batches ).map( this.renderBatchOption ) }
                </ul>

                <button id="submit" className="button button-primary" onClick={ this.props.runBatch.bind( null, 1 ) }>Run Batch Process</button>
                <button id="reset" className="button button-secondary" onClick={ this.props.resetBatch }>Reset Batch Process</button>
            </div>
        )
    }
} );

/**
 * Modal component.
 */
var Modal = React.createClass( {
    render : function() {
        var classes        = 'batch-processing-overlay',
            batch_info     = this.props.batchInfo,
            batch_title    = ( batch_info.batch_title ) ? batch_info.batch_title : this.props.selectedBatch,
            status         = ( batch_info.status ) ? batch_info.status : '',
            progress_style = {
                width: batch_info.progress + '%'
            };

        if ( this.props.isOpen ) {
            classes += ' is-open';
        }

        /**
         * Return the title for the modal.
         *
         * @returns {JSX}
         */
        var title = function() {
            if ( status ) {
                return <h2>{ batch_title }: { status }</h2>;
            } else {
                return <h2>{ batch_title }</h2>
            }
        }

        /**
         * Return content for the modal.
         *
         * @returns {JSX}
         */
        var content = function() {
            if ( batch_info.error ) {
                return (
                    <div className="batch-error">
                        { batch_info.error }
                    </div>
                );
            } else {
                return (
                    <div className="progress-bar">
                        <span className="progress-bar__text">Progress: { batch_info.progress }%</span>
                        <div className="progress-bar__visual" style={ progress_style }></div>
                    </div>
                );
            }
        }

        return (
            <div className={ classes }>
                <div className="close" onClick={ this.props.toggleProcessing.bind( null, false ) }>close</div>
                <div className="batch-overlay__inner">
                    { title() }
                    { content() }
                </div>
            </div>
        );
    }
} );

ReactDOM.render( <App/>, document.getElementById( 'batch-main' ) );

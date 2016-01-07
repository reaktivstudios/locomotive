var React = require( 'react' );
var ReactDOM = require( 'react-dom' );
var CSSTransitionGroup = require('react-addons-css-transition-group');


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
                batch: false
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
    runBatch : function() {
        if ( '' === this.state.processing.batch ) {
            return;
        }

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

                <button id="submit" className="button button-primary" onClick={ this.props.runBatch }>Run Batch Process</button>
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
        var classes = 'batch-processing-overlay';
        if ( this.props.isOpen ) {
            classes += ' is-open';
        }

        return (
            <div className={ classes }>
                <div className="close" onClick={ this.props.toggleProcessing.bind( null, false ) }>close</div>
                <div className="batch-overlay__inner"></div>
            </div>
        );
    }
} );

ReactDOM.render( <App/>, document.getElementById( 'batch-main' ) );

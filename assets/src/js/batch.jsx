var React = require( 'react' );
var ReactDOM = require( 'react-dom' );

/**
 * Our Batch Processing App.
 */
var App = React.createClass( {
    getInitialState: function() {
        return {
            // Batches that can be run. Loaded through `wp_localize_script()`.
            batches: batch.batches,
            page_title: batch.page_title,
        };
    },

    render : function() {
        return (
            <div className="wrap">
                <h2>{ this.state.page_title }</h2>
                <BatchPicker batches={ this.state.batches } />
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
            <li key={ key }>
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

                <button id="submit" className="button button-primary">Run Batch Process</button>
                <button id="reset" className="button button-secondary">Reset Batch Process</button>
            </div>
        )
    }
} );

ReactDOM.render( <App/>, document.getElementById( 'batch-main' ) );

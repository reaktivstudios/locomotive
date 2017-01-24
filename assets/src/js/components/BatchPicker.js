import React from 'react';

/**
 * Potential Batch listings.
 */
var BatchPicker = React.createClass( {
	/**
	 * Type Checking
	 * @type {Object}
	 */
	propTypes: {
		batches: React.PropTypes.object,
		updateSelectedBatch: React.PropTypes.func,
		runBatch: React.PropTypes.func,
		canInteractWithBatch: React.PropTypes.bool,
		toggleResetModal: React.PropTypes.func
	},
	/**
	 * Render an individual batch option.
	 *
	 * @param key Used to get the right batch from this.props.batches.
	 * @returns {JSX}
	 */
	renderBatchOption : function ( key ) {
		var batch = this.props.batches[ key ];

		return (
			<li key={ key } onClick={ this.props.updateSelectedBatch.bind( null, key ) }>
				<input type="radio" id={ key } name="batch_process" className="batch-process-option" value="test-another-batch" />
				<label htmlFor={ key }>
					{ batch.name } <small>last run: { batch.last_run } | status: { batch.status }</small>
				</label>
			</li>
		);
	},

	/**
	 * Render the batch processes list.
	 *
	 * @returns {JSX}
	 */
	render : function () {
		return (
			<div className="batch-picker">
				<ul className="batch-processes">
					{ Object.keys( this.props.batches ).map( this.renderBatchOption ) }
				</ul>

				<button id="submit" className="button button-primary" onClick={ this.props.runBatch.bind( null, 1 ) } disabled={ ! this.props.canInteractWithBatch }>Run Batch Process</button>
				<button id="reset" className="button button-secondary" onClick={ this.props.toggleResetModal.bind( null, true ) } disabled={ ! this.props.canInteractWithBatch }>Reset Batch Process</button>
			</div>
		);
	}
} );

export default BatchPicker;

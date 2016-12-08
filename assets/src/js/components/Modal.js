import React from 'react';

/**
 * Modal component.
 */
var Modal = React.createClass( {
	/**
	 * Type Checking
	 * @type {Object}
	 */
	propTypes: {
		isOpen: React.PropTypes.bool,
		toggleProcessing: React.PropTypes.func,
		batchInfo: React.PropTypes.object,
		selectedBatch: React.PropTypes.string
	},

	mixins: [
		require( 'react-onclickoutside' )
	],

	handleClickOutside : function () {
		if ( this.props.isOpen ) {
			this.props.toggleProcessing( false );
		}
	},

	render : function () {
		var classes        = 'locomotive-overlay',
		    batchInfo     = this.props.batchInfo,
		    batchTitle    = ( batchInfo.batchTitle ) ? batchInfo.batchTitle : this.props.selectedBatch,
		    status         = ( batchInfo.status ) ? batchInfo.status : '',
		    progressStyle = { width: batchInfo.progress + '%' };

		if ( this.props.isOpen ) {
			classes += ' is-open';
		}

		/**
		 * Return the title for the modal.
		 *
		 * @returns {JSX}
		 */
		var title = function () {
			if ( status ) {
				return <h2>{ batchTitle }: { status }</h2>;
			}

			return <h2>{ batchTitle }</h2>;
		};

		/**
		 * Return content for the modal.
		 *
		 * @returns {JSX}
		 */
		var content = function () {
			if ( batchInfo.error ) {
				return (
					<div className="batch-error">
						{ batchInfo.error }
					</div>
				);
			}

			return (
				<div className="progress-bar">
					<span className="progress-bar__text">Progress: { batchInfo.progress }%</span>
					<div className="progress-bar__visual" style={ progressStyle }></div>
				</div>
			);
		};

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

export default Modal;

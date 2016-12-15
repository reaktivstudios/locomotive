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
		batchErrors: React.PropTypes.array,
		selectedBatch: React.PropTypes.string
	},

	getInitialState: function () {
		return {
			showErrors: false
		};
	},

	mixins: [
		require( 'react-onclickoutside' )
	],

	handleClickOutside : function () {
		if ( this.props.isOpen ) {
			this.props.toggleProcessing( false );
		}
	},

	onErrorClick: function () {
		this.setState( { showErrors: !this.state.showErrors } );
	},

	render : function () {
		var classes        = 'locomotive-overlay',
			batchInfo     = this.props.batchInfo,
			errorClick = this.onErrorClick,
			batchTitle    = ( batchInfo.batch_title ) ? batchInfo.batch_title : this.props.selectedBatch,
			batchErrors = this.props.batchErrors,
			numErrors = batchErrors.length,
			totalResults = parseInt( batchInfo.total_num_results ),
			numSuccess =  ( ( totalResults / batchInfo.total_steps ) * batchInfo.current_step ) - numErrors,
			status         = ( batchInfo.status ) ? batchInfo.status : '',
			progressStyle = { width: batchInfo.progress + '%' },
			showErrors = this.state.showErrors;

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

		var errorItems = batchErrors.map( function ( item ) {
			return (
				<li key={ item.item } className="batch-error__item">{ item.item } failed with the message:  { item.message }</li>
			);

		} );

		var errorList = function () {
			if ( showErrors ) {
				return (
					<ul className="batch-error__list">
						{errorItems}
					</ul>
				);
			}
		};

		var infoBlock = function () {
			if ( batchInfo.error ) {
				return (
					<div className="batch-error">
						<h3 className="batch-error__title dashicons-before dashicons-warning">Status: Failed</h3>
						<h4>There were <span className="red">{ numErrors } failed</span> items and <span className="green">{ numSuccess } successful</span> items in your batch.</h4>
						<button
							className={ 'batch-error__btn dashicons-before dashicons-' + ( showErrors ? 'arrow-down' : 'arrow-right' ) }
							onClick={ errorClick }
						>
							Error Log
						</button>
						{  errorList() }
					</div>
				);
			}

			return (
				<div className="batch-success">
					<h3 className="batch-success__title dashicons-before dashicons-yes">Status: Success!</h3>
				</div>
			);
		};

		/**
		 * Return content for the modal.
		 *
		 * @returns {JSX}
		 */
		var content = function () {
			return (
				<div className="batch-overaly__content">
					<div className="progress-bar">
						<span className="progress-bar__text">Progress: { batchInfo.progress }%</span>
						<div className="progress-bar__visual" style={ progressStyle }></div>
					</div>
					{ infoBlock() }
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

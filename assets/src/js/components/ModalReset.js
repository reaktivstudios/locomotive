import React from 'react';

/**
 * Modal component.
 */
var ModalReset = React.createClass( {
	/**
	 * Type Checking
	 * @type {Object}
	 */
	propTypes: {
		isOpen: React.PropTypes.bool,
		resetBatch: React.PropTypes.func,
		toggleResetModal: React.PropTypes.func
	},

	mixins: [
		require( 'react-onclickoutside' )
	],

	handleClickOutside : function () {
		if ( this.props.isOpen ) {
		  this.props.toggleResetModal( false );
		}
	},

	closeModal: function () {
		this.props.toggleResetModal( false );
	},

	render : function () {
		var classes = 'locomotive-overlay';

		if ( this.props.isOpen ) {
			classes += ' is-open';
		}

		return (
			<div className={ classes }>
				<div className="batch-overlay__inner">
         <h3>Are you sure you want to reset this process?</h3>
         <p>Doing so will reset the last run time back to never, and delete all associated post meta and options data.</p>

         <button id="submit" className="button button-primary" onClick={ this.props.resetBatch }>Reset</button>
          <button id="submit" className="button button-secondary" onClick={ this.closeModal }>Cancel</button>
				</div>
			</div>
		);
	}
} );

export default ModalReset;

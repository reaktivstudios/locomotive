import React from 'react';

/**
 * Modal component.
 */
var Modal = React.createClass( {
    mixins: [
        require( 'react-onclickoutside' )
    ],

    handleClickOutside : function() {
        if ( this.props.isOpen ) {
            this.props.toggleProcessing( false );
        }
    },

    render : function() {
        var classes        = 'locomotive-overlay',
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

export default Modal;

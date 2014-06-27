// add :focus selector
jQuery.expr[':'].focus = function( elem ) {
    return elem === document.activeElement && ( elem.type || elem.href );
};

$.editable.addInputType( 'datepicker', {

    /* create input element */
    element: function( settings, original ) {
        var form = $( this );
        var dateFormat = settings.datepicker.dateFormat;
        var input = $( '<input type="text" class="form-control date-picker" id="date02" data-date-format="' + dateFormat + '"/>' );
        input.attr( 'autocomplete','off' );
        form.append( input );
        return input;
    },

    /* attach jquery.ui.datepicker to the input element */
    plugin: function( settings, original ) {
        var form = this,
            input = form.find( "input" );

        // Don't cancel inline editing onblur to allow clicking datepicker
        settings.onblur = 'nothing';


        input.datepicker().on('changeDate', function() {
            console.log('changeDate');
            // clicking specific day in the calendar should
            // submit the form and close the input field
            form.submit();
        }).on('hide', function()  {
            console.log('hide');
            setTimeout( function() {
                if ( !input.is( ':focus' ) ) {
                    // input has NO focus after 150ms which means
                    // calendar was closed due to click outside of it
                    // so let's close the input field without saving
                    original.reset( form );
                } else {
                    // input still HAS focus after 150ms which means
                    // calendar was closed due to Enter in the input field
                    // so lets submit the form and close the input field
                    form.submit();
                }

                // the delay is necessary; calendar must be already
                // closed for the above :focus checking to work properly;
                // without a delay the form is submitted in all scenarios, which is wrong
            }, 150 );
        });
    }
} );

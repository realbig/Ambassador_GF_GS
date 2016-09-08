window.GFGoogleSheetsFeedSettings = null;

(function ($, api) {
    'use strict';

    api = {

        /**
         * Stores used elements within this api.
         *
         * @since {{VERSION}}
         */
        $elements: {},

        /**
         * Initializes this api.
         *
         * @since {{VERSION}}
         */
        init: function () {

            api.get_elements();
            api.bind_handlers();
            api.populate_sheet_choices();
        },

        /**
         * Populates the Google Sheet field choices on load.
         *
         * @since {{VERSION}}
         */
        populate_sheet_choices: function () {

            // Don't process if on a current feed
            if (api.$elements.sheet_field.val()) {
                return;
            }

            // Create the spinner
            var gfSpinner = new gfAjaxSpinner(
                $('select#sheet'),
                gf_vars.baseUrl + '/images/spinner.gif',
                'position: relative; top: 2px; left: 5px;'
            );

            // Get the options
            $.post(
                ajaxurl,
                {
                    action: 'gform_googlesheets_get_sheet_choices'
                },
                function (response) {

                    gfSpinner.destroy();

                    if (response.success) {

                        api.$elements.sheet_field.find('option').remove();

                        // Append each option
                        $.each(response.data.choices, function (i) {
                            api.$elements.sheet_field.append($('<option />').val(this.value).text(this.label));
                        });
                    }
                }
            )
        },

        /**
         * Gets all global elements.
         *
         * @since {{VERSION}}
         */
        get_elements: function () {

            // Dropdown for choosing a sheet
            api.$elements.sheet_field = $('#sheet');

            // Field map
            api.$elements.field_map_row = $('#gaddon-setting-row-fields');
        },

        /**
         * Binds all global handlers.
         *
         * @since {{VERSION}}
         */
        bind_handlers: function () {

            api.$elements.sheet_field.change(api.update_field_map_choices);
        },

        /**
         * Updates the field map options with the new sheet column headers.
         *
         * @since {{VERSION}}
         */
        update_field_map_choices: function () {

            // Create the spinner
            var gfSpinner = new gfAjaxSpinner(
                $('select#sheet'),
                gf_vars.baseUrl + '/images/spinner.gif',
                'position: relative; top: 2px; left: 5px;'
            );

            api.$elements.field_map_row.hide();

            $.get(
                ajaxurl,
                {
                    action: 'gform_googlesheets_get_field_map',
                    sheet_id: $(this).val(),
                    id: form.id
                },
                function (response) {

                    gfSpinner.destroy();

                    api.$elements.field_map_row.find('td').html(response.data.html);
                    api.$elements.field_map_row.show();
                }
            )
        }
    };

    $(api.init);
})(jQuery, GFGoogleSheetsFeedSettings);
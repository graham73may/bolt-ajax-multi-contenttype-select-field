(function ($) {
    'use strict';

    $.fn.extend({
        customSelect2Sortable : function (options) {
            var select = $(this);

            $(select).select2(options);

            var ul = $(select).next('.select2-container').first('ul.select2-selection__rendered');

            ul.sortable({
                items     : 'li:not(.select2-search)',
                tolerance : 'pointer',

                stop : function () {
                    $($(ul).find('.select2-selection__choice').get().reverse()).each(function () {
                        var id     = $(this).data('data').id;
                        var option = select.find('option[value="' + id + '"]')[0];

                        $(select).prepend(option);
                    });
                }
            });
        }
    });

    function modifySelect2 () {
        var $buicSelects = $('.js-ajax-multi-ct-select');
        var len          = $buicSelects.length;
        var options      = {};
        var i;
        var $buicSelect;
        var $select;

        for (i = 0; i < len; i++) {
            $buicSelect = $($buicSelects[i]);
            $select     = $buicSelect.find('select');
            options     = $select.data('select2').options.options;

            if (options.ajax === undefined || options.ajax === null) {
                options = {
                    language                : {
                        errorLoading : function () {
                            return 'Searching...'
                        }
                    },
                    placeholder             : options.placeholder,
                    allowClear              : options.allowClear || true,
                    minimumResultsForSearch : options.minimumResultsForSearch,
                    width                   : options.width,
                    ajax                    : {
                        select         : $select,
                        url            : function (params) {
                            var page = params.page || 1;

                            return '/bolt/ajax-multi-ct-select?page=' + page;
                        },
                        dataType       : 'json',
                        delay          : 500,
                        type           : 'POST',
                        data           : function (params) {
                            var $container = $select.closest('.js-ajax-multi-ct-select-container');

                            return {
                                q     : params.term, // search term
                                page  : params.page || 1,
                                field : $container.data('field')
                            };
                        },
                        processResults : function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results    : data.results,
                                pagination : {
                                    more : (data.pager.current * data.pager.limit) < data.pager.count
                                }
                            };
                        },
                        cache          : true
                    }
                };

                if ($buicSelect.hasClass('js-ajax-multi-ct-select--sortable')) {
                    $select.select2('destroy');

                    options.createTag = function (params) {
                        return undefined;
                    };
                    $select.customSelect2Sortable(options);
                } else {
                    $select.select2('destroy');
                    $select.select2(options);
                }

                $buicSelect.find('.select2-selection__placeholder').html('(none)');
            }
        }
    }

    $('.repeater-add').on('click', function () {
        modifySelect2();
    });

    $(window).on('load', modifySelect2);
}(jQuery));

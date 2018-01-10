(function ($) {
    'use strict';

    $.fn.extend({
        customSelect2Sortable: function () {
            var $select = $(this);

            var ul = $select.next('.select2-container').first('ul.select2-selection__rendered');

            ul.sortable({
                items    : 'li:not(.select2-search)',
                tolerance: 'pointer',

                stop: function () {
                    $($(ul).find('.select2-selection__choice').get().reverse()).each(function () {
                        var id = $(this).data('data').id;
                        var option = $(this).find('option[value="' + id + '"]')[0];

                        $(this).prepend(option);
                    });
                }
            });

        }
    });

    function modifySelect2 () {
        var $buicSelects = $('.js-ajax-multi-ct-select');
        var len = $buicSelects.length;
        var options = {};
        var i;
        var $buicSelect;
        var $select;

        for (i = 0; i < len; i++) {
            $buicSelect = $($buicSelects[i]);
            $select = $buicSelect.find('select');
            options = $select.data('select2').options.options;

            $select.select2({
                language               : {
                    errorLoading: function () {
                        return 'Searching...';
                    }
                },
                placeholder            : options.placeholder,
                allowClear             : options.allowClear || true,
                minimumResultsForSearch: options.minimumResultsForSearch,
                width                  : options.width,
                ajax                   : {
                    select        : $select,
                    url           : function (params) {
                        var page = params.page || 1;

                        return '/bolt/ajax-multi-ct-select?page=' + page;
                    },
                    dataType      : 'json',
                    delay         : 500,
                    type          : 'POST',
                    data          : function (params) {
                        $select = $(this);
                        var $container = $select.closest('.js-ajax-multi-ct-select-container');
                        return {
                            q    : params.term, // search term
                            page : params.page || 1,
                            field: $container.data('field')
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;

                        return {
                            results   : data.results,
                            pagination: {
                                more: (data.pager.current * data.pager.limit) < data.pager.count
                            }
                        };
                    },
                    cache         : false
                }
            });

            if ($buicSelect.hasClass('js-ajax-multi-ct-select--sortable')) {
                $select.customSelect2Sortable();
            }

            $buicSelect.find('.select2-selection__placeholder').html('(none)');

        }

    }

    $('.repeater-add').on('click', function () {
        modifySelect2();
    });

    $(window).on('load', modifySelect2);

}(jQuery));

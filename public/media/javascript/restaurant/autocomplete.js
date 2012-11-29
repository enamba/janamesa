$(document).ready(function () {
    /*
     * Autocomplete for zip codes in restaurant backend
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 17.08.2012
     */
    (function ($) {
        $.fn.cityAutocomplete = function () {
            var $cityInput = $('.yd-city-autocomplete-shown', this),
                $cityId = $('.yd-city-autocomplete-hidden', this),
                acMinLength = 2,
                acDelay = 250;

            var acXhr = null,
                acCache = {},
                currentCityLabel = $cityInput.val(),
                currentCityId = $cityId.val();

            function abortXhr() {
                if (acXhr !== null && typeof acXhr.abort == 'function') {
                    acXhr.abort();
                    acXhr = null;
                }
            }

            function processXhr(xhrSuccessCallback) {
                acXhr =  $.ajax({
                    dataType : 'json',
                    url: '/request_administration/cityautocomplete',
                    data: {
                        term: $cityInput.val().toLowerCase()
                    },
                    beforeSend: function (xhr, settings) {
                        if (typeof $.ajaxSettings.beforeSend == 'function') {
                            if ($.ajaxSettings.beforeSend(xhr, settings) === false) {
                                return false;
                            }
                        }
                        if (this.url in acCache) {
                            var cachedContent = acCache[this.url];
                            xhrSuccessCallback(cachedContent);
                            return false;
                        }
                        return true;
                    },
                    success: function (rawData) {
                        if ($.isArray(rawData['cities'])) {
                            acCache[this.url] = rawData;
                            xhrSuccessCallback(rawData);
                        }
                    }
                });
            }

            $cityInput.focusout(function () {
                abortXhr();
                setTimeout(function () {
                    $cityInput.val(currentCityLabel);
                    $cityId.val(currentCityId);
                }, acDelay);
            }).autocomplete({
                minLength: acMinLength,
                delay: acDelay,
                source: function (request, response) {
                    abortXhr();
                    processXhr(function (rawData) {
                        var formattedData = [];
                        if ($.isArray(rawData['cities'])) {
                            for (var c in rawData['cities']) {
                                var cityData = rawData['cities'][c];
                                formattedData.push({
                                    'id': cityData.id,
                                    'value': cityData.value
                                });
                            }
                        }
                        response(formattedData);
                    });
                },
                focus: function (event, ui) {
                    return false;
                },
                select: function (event, ui) {
                    currentCityLabel = ui.item.label;
                    currentCityId = ui.item.id;
                    $cityInput.val(currentCityLabel);
                    $cityId.val(currentCityId);
                }
            });
        };
    })(jQuery);
    // If dynamically created autocomplete fields will be needed, consider LiveQuery plugin usage
    // see: http://docs.jquery.com/Plugins/livequery
    $('.yd-city-autocomplete-container').each(function(){
        $(this).cityAutocomplete();
    });
});

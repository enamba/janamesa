/**
 * Remote validation engine language
 * @author vpriem
 * @since 11.04.2011
 */
(function($) {
    $.fn.validationEngineLanguage = function(){};
    $.validationEngineLanguage = {
        _allrules: false,
        allRules: function() {
            if (this._allrules !== false) {
                return this._allrules;
            }
            var allrules = {};
            $.ajax({
                async: false,
                dataType: "json",
                url: "/request/validation",
                success: function (data) {
                    allrules = data;
                }
            });
            this._allrules = allrules;
            return this._allrules;
        }
    };
})(jQuery);
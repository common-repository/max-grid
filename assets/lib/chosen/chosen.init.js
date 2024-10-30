if (jQuery) {
    (function ($) {
        function chosen(element, options, callback) {
            this.element = element;
            this.options = $.extend(true, {}, defaults, options);
            this.callback = callback;
            this.init();
        }
        $.fn.ultraselect = function (options, callback) {
            var args = Array.prototype.slice.call(arguments, 1);    
        };
    }(jQuery));
}
define(function () {
    'use strict';

    /**
     * Regex to replace all unsupported characters from string.
     *
     * @param {String} str
     * @return {String}
     */
    return function (str) {
        return str.replace('/[^a-zA-Z0-9\s\-.\']/', '').substring(0, 127);
    };
});

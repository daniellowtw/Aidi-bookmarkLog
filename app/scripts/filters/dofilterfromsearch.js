'use strict';

/**
 * @ngdoc filter
 * @name aidiApp.filter:doFilterFromSearch
 * @function
 * @description
 * # doFilterFromSearch
 * Filter in the aidiApp.
 */
angular.module('aidiApp')
    .filter('doFilterFromSearch', function () {
        // not in use
        return function (input, options) {
            for (var inputKey in input) {
                input[inputKey].links = _.filter(input[inputKey].links, function (link) {
                    if (options.tags) return RegExp(options.tags).test(link.tags)
                    if (options.filter) {
                        return RegExp(options.filter).test(link.title + link.description)
                    }
                    return true;
                });
                if (!input[inputKey].links.length) input[inputKey].show = false;
                else {input[inputKey].show = true};
            }
            return input
        }
            ;
    }
)
;

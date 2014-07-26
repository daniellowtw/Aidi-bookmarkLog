'use strict';

/**
 * @ngdoc function
 * @name aidiApp.controller:TagcontrollerCtrl
 * @description
 * # TagcontrollerCtrl
 * Controller of the aidiApp
 */
angular.module('aidiApp')
    .controller('TagcontrollerCtrl', function ($scope, profileFactory) {
        profileFactory.api('allTags').then(function(result){
            $scope.tags = result.data;
        })
    });

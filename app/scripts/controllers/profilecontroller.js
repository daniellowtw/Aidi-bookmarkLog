'use strict';

/**
 * @ngdoc function
 * @name aidiApp.controller:ProfilecontrollerCtrl
 * @description
 * # ProfilecontrollerCtrl
 * Controller of the aidiApp
 */
angular.module('aidiApp')
    .controller('ProfilecontrollerCtrl', function ($scope, profileFactory, $routeParams, $rootScope, $location) {
        $scope.removeLink = function(linkDate, $index){
            profileFactory.api('deleteLink', linkDate).then(function(result){
                $location.path('/profile');
            })
        }
        $scope.splitTags = function (tagString) {
            var tagArray = tagString.split(/\s+/);
            return tagArray;
        }
//        $scope.filterOptions = {}
        if (!!$routeParams.filter) {
            profileFactory.api('filterFulltext', $routeParams.filter).then(function (result) {
                $scope.dayEntries = result.data;
            });
            $scope.message = "searching by filter"
            $rootScope.filter = $routeParams.filter;
        }
        else if (!!$routeParams.tags) {
            profileFactory.api('filterTags', $routeParams.tags).then(function (result) {
                $scope.dayEntries = result.data;
            });
            $scope.message = "searching by tags"
        }
        else {
            profileFactory.getData.then(function (result) {
                $scope.dayEntries = result.data;

            });
        }

    });

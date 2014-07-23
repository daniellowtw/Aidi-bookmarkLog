'use strict';

/**
 * @ngdoc function
 * @name aidiApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the aidiApp
 */
angular.module('aidiApp')
  .controller('MainCtrl', function ($scope) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
  });

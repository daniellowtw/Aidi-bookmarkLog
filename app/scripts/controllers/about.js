'use strict';

/**
 * @ngdoc function
 * @name aidiApp.controller:AboutCtrl
 * @description
 * # AboutCtrl
 * Controller of the aidiApp
 */
angular.module('aidiApp')
  .controller('AboutCtrl', function ($scope) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
  });

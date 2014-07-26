'use strict';

/**
 * @ngdoc function
 * @name aidiApp.controller:AddcontrollerCtrl
 * @description
 * # AddcontrollerCtrl
 * Controller of the aidiApp
 */
angular.module('aidiApp')
    .controller('AddcontrollerCtrl', function ($scope, $http, $location, $routeParams, profileFactory) {
        if(!!$routeParams.linkDate){
            profileFactory.api('getLinkFromDate', $routeParams.linkDate).then(function(result){
                $scope.form = result.data;
            })
        }
        else{
            $scope.form = $routeParams;
        }
        $scope.processForm = function(){
            //validation
            if(!!$scope.form.alinkdate)
                {$scope.form.alinkdate = Date.now();}
            //end validation
            $http({
                method  : 'POST',
                url     : 'http://localhost:8888/post.php',
                data    : $.param($scope.form),  // pass in data as strings
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
            })
                .success(function(data){
                    $location.path("/#/profile");
                })
        }
        $scope.dateTime = new Date().toLocaleString();

    });

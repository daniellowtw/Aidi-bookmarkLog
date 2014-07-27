'use strict';

/**
 * @ngdoc function
 * @name aidiApp.controller:AddcontrollerCtrl
 * @description
 * # AddcontrollerCtrl
 * Controller of the aidiApp
 */
angular.module('aidiApp')
    .controller('AddcontrollerCtrl', function ($scope, $http, $location, $routeParams, profileFactory, SERVER) {
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
            if(!$scope.form.linkdate)
                {$scope.form.linkdate = Date.now();}
            //end validation
            $http({
                method  : 'POST',
                url     : SERVER+'post.php',
                data    : $.param($scope.form),  // pass in data as strings
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
            })
                .success(function(data){
                    console.log(data);
                    $location.path("/#/profile");
                })
        }
        $scope.dateTime = new Date().toLocaleString();

    });

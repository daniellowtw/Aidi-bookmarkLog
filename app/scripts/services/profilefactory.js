'use strict';

/**
 * @ngdoc service
 * @name aidiApp.profileFactory
 * @description
 * # profileFactory
 * Factory in the aidiApp.
 */
angular.module('aidiApp')
    .service('profileFactory', function ($q, $http, SERVER) {
        this.api = function(method, args){
//            console.log(SERVER+'api.php?'+method+'='+args);
            return $http.get(SERVER+'api.php?'+method+'='+args)
        }
        this.getData = $http.get(SERVER+"get.php");
    });

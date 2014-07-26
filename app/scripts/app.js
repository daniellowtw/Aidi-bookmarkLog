'use strict';

/**
 * @ngdoc overview
 * @name aidiApp
 * @description
 * # aidiApp
 *
 * Main module of the application.
 */
angular
    .module('aidiApp', [
        'ngAnimate',
        'ngCookies',
        'ngResource',
        'ngRoute',
        'ngSanitize',
        'ngTouch',
        'ui.bootstrap',
        'ui.bootstrap.tooltip'
    ])
    .config(function ($routeProvider, $compileProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'views/main.html',
                controller: 'MainCtrl'
            })
            .when('/about', {
                templateUrl: 'views/about.html',
                controller: 'AboutCtrl'
            })
            .when('/profile/search/:filter', {
                templateUrl: 'views/profile.html',
                controller: 'ProfilecontrollerCtrl'
            })
            .when('/profile/tags/:tags', {
                templateUrl: 'views/profile.html',
                controller: 'ProfilecontrollerCtrl'
            })
            .when('/profile', {
                templateUrl: 'views/profile.html',
                controller: 'ProfilecontrollerCtrl'
            })
            .when('/add/:linkDate', {
                templateUrl: 'views/add.html',
                controller: 'AddcontrollerCtrl'
            })
            .when('/add', {
                templateUrl: 'views/add.html',
                controller: 'AddcontrollerCtrl'
            })
            .when('/tag', {
                templateUrl: 'views/tag.html',
                controller: 'TagcontrollerCtrl'
            })
            .otherwise({
                redirectTo: '/'
            });
            $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|javascript):/);
    })
    .run(function ($rootScope, $location, profileFactory) {
        $rootScope.doSearch = function(){
            $location.path('profile/search/'+$rootScope.filter);
        }
        $rootScope.HOMEDIR = $location.$$absUrl.substr(0, $location.$$absUrl.length - $location.$$path.length);
        $rootScope.authenticate = function(){
            profileFactory.api('checkSecret', CryptoJS.MD5($rootScope.secret).toString()).then(function(result){
                if (result.data === 'true'){
                    $rootScope.authenticated = true;
                }
                else {
                    alert("Failed authentication");
                }
            })
        }
        $rootScope.changeSecret = function(){
            profileFactory.api('changeSecret', CryptoJS.MD5($rootScope.secret).toString()+CryptoJS.MD5($rootScope.newSecret).toString()).then(function(result){
                if (result.data === 'true'){
                    alert("Secret Changed successfully");
                }
                else {
                    alert("Failed to change");
                }
            })
        }
    })
    .value("SERVER", "http://twdl3.user.srcf.net/summer14/aidi/server/")
;

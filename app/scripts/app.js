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
        'ngTouch'
    ])
    .config(function ($routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'views/main.html',
                controller: 'MainCtrl'
            })
            .when('/about', {
                templateUrl: 'views/about.html',
                controller: 'AboutCtrl'
            })
            .when('/profile', {
                templateUrl: 'views/profile.html',
                controller: 'ProfilecontrollerCtrl'
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
    })
    .run(function ($rootScope) {

    })
;

(function () {
    'use strict';

    angular
        .module('app', ['ngRoute', 'ngCookies'])
        .config(config)
        .run(run);

    config.$inject = ['$routeProvider', '$locationProvider'];
    function config($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                controller: 'HomeController',
                templateUrl: 'views/home.view.html',
                controllerAs: 'vm'
            })

            .when('/login', {
                controller: 'LoginController',
                templateUrl: 'views/login.view.html',
                controllerAs: 'vm'
            })

            .when('/register', {
                controller: 'RegisterController',
                templateUrl: 'views/register.view.html',
                controllerAs: 'vm'
            })

            .when('/registerorganisation', {
                controller:'RegisterorganisationController', 
                templateUrl: 'views/registerorganisation.view.html',
                controllerAs: 'vm'
            })

            .when('/offers', {
                controller: 'OfferController', 
                templateUrl: 'views/offer.view.html', 
                controllerAs: 'vm'
            })

            .when('/request', {
                controller: 'RequestController', 
                templateUrl: 'views/request.view.html', 
                controllerAs: 'vm'
            })

            .when('/homepage', {
                controller: 'OfferController', 
                templateUrl: 'views/offer.view.html', 
                controllerAs: 'vm'
            })

            .otherwise({ redirectTo: '/login' });
    }

    run.$inject = ['$rootScope', '$location', '$cookieStore', '$http'];
    function run($rootScope, $location, $cookieStore, $http) {
        // keep user logged in after page refresh
        $rootScope.globals = $cookieStore.get('globals') || {};
        if ($rootScope.globals.currentUser) {
            $http.defaults.headers.common['Authorization'] = 'Basic ' + $rootScope.globals.currentUser.authdata; // jshint ignore:line
        }

        $rootScope.$on('$locationChangeStart', function (event, next, current) {
            // redirect to login page if not logged in and trying to access a restricted page
            var restrictedPage = $.inArray($location.path(), ['', '/', '/login', '/register', '/registerorganisation']) === -1;
            var restrictedPage2 = $.inArray($location.path(), ['', '/']) === -1;
            var restrictedPage3 = $.inArray($location.path(), ['/request']) === -1;
            var verifiedUser = $rootScope.globals.currentUser.verified
            console.log('aaa' + verifiedUser)
            var loggedIn = $rootScope.globals.currentUser
;            if (restrictedPage && !loggedIn) {
                $location.path('/login');
            } else if (!restrictedPage2 && loggedIn){
                $location.path('/homepage');
            } else if (!restrictedPage3 && !verifiedUser) {
                $location.path('/offers');
            }
        });
    }

})();
(function () {
    'use strict';

    angular
        .module('app')
        .controller('LogoutController', LogoutController);

    LogoutController.$inject = ['$location', 'AuthenticationService'];
    function LogoutController($location, AuthenticationService) {

        initController()

        function initController() {
            AuthenticationService.ClearCredentials();
            $location.path('/');
        }();

    }

})();


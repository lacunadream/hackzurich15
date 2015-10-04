(function () {
    'use strict';

    angular
        .module('app')
        .controller('RegisterorganisationController', RegisterorganisationController);

    RegisterOrganisationController.$inject = ['UserService', '$location', '$rootScope', 'FlashService'];
    function RegisterorganisationController(UserService, $location, $rootScope, FlashService) {
        var vm = this;

        vm.register = register;

        function register() {
            vm.dataLoading = true;
            UserService.CreateOrganisation(vm.user)
                .then(function (response) {
                    console.log(response)
                    if (response.status == "success") {
                        FlashService.Success('Registration successful', true);
                        $location.path('/login');
                    } else {
                        FlashService.Error('Registration error');
                        vm.dataLoading = false;
                    }
                });
        }
    }

})();

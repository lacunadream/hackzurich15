// (function () {
//     'use strict';

//     angular
//         .module('app')
//         .controller('LogoutController', LogoutController);

//     LogoutController.$inject = ['$location', 'AuthenticationService'];
//     function LogoutController($location, AuthenticationService) {

//          initController()

//         function initController() {
//             // reset login status
//             AuthenticationService.ClearCredentials();
//             $location.path('/');
//         }();

//         initController();

//         // function login() {
//         //     vm.dataLoading = true;
//         //     AuthenticationService.Login(vm.username, vm.password, function (response) {
//         //         if (response.success) {
//         //             AuthenticationService.SetCredentials(vm.username, vm.password);
//         //             $location.path('/');
//         //         } else {
//         //             FlashService.Error(response.message);
//         //             vm.dataLoading = false;
//         //         }
//         //     });
//         // };
//     }
// initController();
// })();

angular.module('app', [])
    .controller('LogoutController', )
(function () {
    'use strict';

    angular
        .module('app')
        .controller('RequestController', RequestController);

    RequestController.$inject = ['UserService', '$location', '$rootScope', 'FlashService'];
    function RequestController(UserService, $location, $rootScope, FlashService) {
        var vm = this;



        initController():


        function initController() {
            getOffers();
        }

        function getOffers() {
            UserService.GetOffers($rootScope.globals.currentUser.username) 
                .then(function (offers) {
                    vm.offers = offers; 
                });
            }
         }

        function createOffer() {
            vm.dataLoading = true;
            UserService.CreateOffer(vm.offer)
                .then(function (response) {
                    if (response.success) {
                        FlashService.Success('Offer created successfully', true);
                    } else {
                        FlashService.Error(response.message);
                        vm.dataLoading = false;
                    }
                });
        }

         function deleteOffer() {
            UserService.DeleteOffer(vm.id)
                .then(function (response) {
                    if (response.success) {
                        FlashService.Success('Deletion successful', true);
                    } else {
                        FlashService.Error(response.message);
                    }
                })
         }

})();

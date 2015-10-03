(function () {
    'use strict';

    angular
        .module('app')
        .controller('OfferController', OfferController);

    OfferController.$inject = ['UserService', '$location', '$rootScope', 'FlashService'];
    function OfferController(UserService, $location, $rootScope, FlashService) {
        var vm = this;

        vm.createOffer = createOffer;
        vm.getOffers = getOffers;
        vm.offers = [];

        initController();


        function initController() {
            getOffers();
        }

        function getOffers() {
            UserService.GetOffers() 
                .then(function (offers) {
                    vm.offers = offers; 
                    console.log(offers);
                    console.log(vm.offers);
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

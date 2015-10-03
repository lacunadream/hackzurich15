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
        vm.deleteOffer = deleteOffer;
        vm.offers = [];
        

        initController();


        function initController() {
            getOffers();
        }

        function getOffers() {
            UserService.GetOffers() 
                .then(function (offers) {
                    vm.offers = offers; 
                    console.log(vm.offers);
                });
            
        }

        function createOffer() {
            vm.dataLoading = true;
            UserService.CreateOffer(vm.offer)
                .then(function (response) {
                    console.log(vm.offer);
                    if (response.success) {
                        FlashService.Success('Offer created successfully', true);
                        console.log(response);
                    } else {
                        FlashService.Error(response.message);
                        vm.dataLoading = false;
                    }
                });
        }

         function deleteOffer(id) {
            UserService.DeleteOffer({"id":id})
                .then(function (response) {
                    if (response.status = "success") {
                        FlashService.Success('Deletion successful', true);
                        console.log('yay')
                        console.log(response)
                        $location.path('/offers');
                    } else {
                        FlashService.Error(response.message);
                        console.log('nay')
                    }
                });
            }
         }

})();

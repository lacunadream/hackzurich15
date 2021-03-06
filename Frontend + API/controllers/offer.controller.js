(function () {
    'use strict';

    angular
        .module('app')
        .controller('OfferController', OfferController);

    OfferController.$inject = ['UserService', '$location', 'FlashService', '$rootScope'];
    function OfferController(UserService, $location, FlashService, $rootScope) {
        var vm = this;


        vm.verified = $rootScope.globals.currentUser.verified
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
                    console.log(response);
                    console.log(vm.offer);
                    if (response.status == "success") {
                        FlashService.Success('Your donation has been received succesfully. Thank you for your donation.', true);
                        console.log(response);
                        vm.dataLoading = false;
                    } else {
                        FlashService.Error('An error occurred.');
                        vm.dataLoading = false;
                    }
                });
        }

         function deleteOffer(id) {
            UserService.DeleteOffer({"id":id})
                .then(function (response) {
                    if (response.status == "success") {
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

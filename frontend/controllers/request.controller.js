(function () {
    'use strict';

    angular
        .module('app')
        .controller('RequestController', RequestController);

    RequestController.$inject = ['UserService', '$location', '$rootScope', 'FlashService'];
    function RequestController(UserService, $location, $rootScope, FlashService) {
        var vm = this;
        vm.getQuery = getQuery;
        vm.numbers = 
        vm.state = false

        function getQuery() {
            // var req = encodeURI(vm.itemtype);
            // console.log('a' + req)
            UserService.GetQuery(vm.itemtype) 
                .then(function (response) {
                    vm.numbers = response;
                    vm.state = true; 
                    vm.itemtype2 = vm.itemtype;
                    console.log(response);
                    console.log(vm.numbers);
                    console.log(vm.itemtype);
                });
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
         }

})();

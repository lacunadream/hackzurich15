(function () {
    'use strict';

    angular
        .module('app')
        .controller('RequestController', RequestController);

    RequestController.$inject = ['UserService', '$location', '$rootScope', 'FlashService'];
    function RequestController(UserService, $location, $rootScope, FlashService) {
        var vm = this;
        vm.getQuery = getQuery;
        vm.createRequest = createRequest;
        vm.numbers 
        vm.state = false
        vm.state2 = false

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
         

        function createRequest() {
            vm.dataLoading = true;
            UserService.CreateRequest(vm.request)
                .then(function (response) {
                    console.log(response);
                    console.log(vm.request);
                    if (response.status == "success") {
                        FlashService.Success('Request created successfully', true);
                        vm.involvedUsers = response.involved_users;
                        vm.reservedAmount = response.reserved_amount;
                        vm.state2 = true; 
                        vm.dataLoading = false;
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

(function () {
    'use strict';

    angular
        .module('app')
        .factory('UserService', UserService);

    UserService.$inject = ['$http'];
    function UserService($http) {
        var service = {};

        service.GetAll = GetAll;
        service.GetById = GetById;
        service.GetByUsername = GetByUsername;
        service.Create = Create;
        service.Update = Update;
        service.Delete = Delete;
        service.GetOffers = GetOffers;
        service.DeleteOffer = DeleteOffer; 

        return service;

        function GetAll() {
            return $http.get('/api/users').then(handleSuccess, handleError('Error getting all users'));
        }

        function GetById(id) {
            return $http.get('/api/users/' + id).then(handleSuccess, handleError('Error getting user by id'));
        }

        function GetByUsername(username) {
            return $http.get('/api/users/' + username).then(handleSuccess, handleError('Error getting user by username'));
        }

        function Create(user) {
            return $http.post('/api/register/individual', user).then(handleSuccess, handleError('Error creating user'));
        }

        function Update(user) {
            return $http.put('/api/users/' + user.id, user).then(handleSuccess, handleError('Error updating user'));
        }

        function Delete(id) {
            return $http.delete('/api/users/' + id).then(handleSuccess, handleError('Error deleting user'));
        }

        function GetOffers() {
            return $http.get('/api/offers').then(handleSuccess, handleError('No offers found'));
        }

        function DeleteOffer(id) {
            return $http.post('/api/offers/delete', id).then(handleSuccess, handleError('Error deleting offer'));
        }

        function CreateOffer(offer) {
            return $http.post('/api/offers/' + offer).then(handleSuccess, handleError('No offers found'));
        }

        function GetRequest(item_name) {
            return $http.get('/api/request/query?' + item_name).then(handleSuccess, handleError('No requests found'));
        }

        function CreateRequest(items) {
            return $http.post('/api/request/query' + items).then(handleSuccess, handleError('No offers found'));
        }

        // private functions

        function handleSuccess(res) {
            return res.data;
        }

        function handleError(error) {
            return function () {
                return { success: false, message: error };
            };
        }
    }

})();

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
        service.GetOffers = GetOffers;
        service.DeleteOffer = DeleteOffer; 
        service.CreateOffer = CreateOffer;
        service.GetQuery = GetQuery;
        service.CreateRequest = CreateRequest;

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

        function GetOffers() {
            return $http.get('/api/offers').then(handleSuccess, handleError('No offers found'));
        }

        function DeleteOffer(id) {
            return $http.post('/api/offers/delete', id).then(handleSuccess, handleError('Error deleting offer'));
        }

        function CreateOffer(offer) {
            return $http.post('/api/offers/create', offer).then(handleSuccess, handleError('No offers found'));
        }

        function GetRequest(item_name) {
            return $http.get('/api/request/query?' + item_name).then(handleSuccess, handleError('No requests found'));
        }

        function GetQuery(type) {
            return $http.get('/api/query?type=' + type).then(handleSuccess, handleError('No offers found'));
        }

        function CreateRequest(request) {
            return $http.post('/api/request', request).then(handleSuccess, handleError('No offers found'));
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

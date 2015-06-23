angular.module('web').factory('productService',['$q', '$http',

    function($q, $http) {
        var service = {};

        service.getUser = function() {
            var deferred = $q.defer();
            
            $http.get("/api/products/getUser").then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };
        
        service.deleteUser = function() {
            var deferred = $q.defer();
            
            $http.delete("/api/products/deleteUser").then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };
        
        service.setUser = function() {
            var deferred = $q.defer();
            
            $http.post("/api/products/getUser", "{}").then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };        
        
        service.getStores = function() {
            var deferred = $q.defer();
            
            $http.get("/api/products/getStores").then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };
        
        service.getUserStores = function(page) {
            var deferred = $q.defer();
            
            $http.get("/api/products/getUserStores/"+page).then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };
        
        service.getUserStore = function(userStoreId) {
            var deferred = $q.defer();
            
            $http.get("/api/products/getUserStore/" + userStoreId).then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };        

        service.setUserStore = function(storeid, userName, password) {
            var deferred = $q.defer();
            
            var postData = JSON.stringify({
                "store_id": storeid,
                "username": userName,
                "password": password
            });
            
            $http.post("/api/products/setUserStore", postData).then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };  
        
        service.deleteUserStore = function(storeId) {
            var deferred = $q.defer();
            
            $http.delete("/api/products/deleteUserStore/" + storeId).then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };
        
        service.getUserPurchases = function(fromDate, page) {
            var deferred = $q.defer();
            
            $http.get("/api/products/getPurchases/" + fromDate + "/" + page).then(function(response) {
                    deferred.resolve(response.data);
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };        

        return service;
    }        
]);
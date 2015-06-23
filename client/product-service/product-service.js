angular.module('web').factory('productService',['$q', '$http',

    function($q, $http) {
        var service = {};

        service.getUser = function() {
            var deferred = $q.defer();
            
            $http.get("/api/getProductUser").then(function(response) {
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
        
        service.deleteUser = function(userId) {
            var deferred = $q.defer();
            
            $http.delete("/api/deleteProductUser").then(function(response) {
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
            
            $http.post("/api/setProductUser", "{}").then(function(response) {
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
            
            $http.get("/api/getProductStores").then(function(response) {
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
        
        service.getUserStores = function() {
            var deferred = $q.defer();
            
            $http.get("/api/getProductUserStores").then(function(response) {
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
        
        service.getUserStore = function(storeId) {
            var deferred = $q.defer();
            
            $http.get("/api/getProductUserStore/" + storeId).then(function(response) {
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
            
            $http.post("/api/setProductUserStore", postData).then(function(response) {
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
            
            $http.delete("/api/deleteProductUserStore/" + storeId).then(function(response) {
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
            
            $http.get("/api/getProductUserPurchases/" + fromDate + "/" + page).then(function(response) {
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
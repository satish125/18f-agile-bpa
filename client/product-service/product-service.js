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
        
        service.addUser = function() {
            var deferred = $q.defer();
            
            $http.post("/api/products/addUser", "{}").then(function(response) {
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

                    var stores_new = [];

                    for(var resultIndex in response.data.payload){
                        stores_new.push(response.data.payload[resultIndex]);
                    }

                    response.data.payload = stores_new;

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
                    var userStores = response.data.payload;
                    var userStores_new = {};

                    //convert array of stores to a map for lookup
                    for(var i = 0, s; s = userStores[i]; i++){
                        userStores_new[s.supermarket_id] = s;
                    }

                    response.data.payload = userStores_new;

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

        service.addUserStore = function(storeid, userName, password) {
            var deferred = $q.defer();
            
            var postData = JSON.stringify({
                "store_id": storeid,
                "username": userName,
                "password": password
            });
            
            $http.post("/api/products/addUserStore", postData).then(function(response) {
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
        
        service.deleteUserStore = function(userStoreId) {
            var deferred = $q.defer();
            
            $http.delete("/api/products/deleteUserStore/" + userStoreId).then(function(response) {
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
        service.updateUserStore = function(userStoreId, userName, password) {
            var deferred = $q.defer();
            
            var postData = JSON.stringify({
                "user_store_id": userStoreId,
                "username": userName,
                "password": password
            });            
            
            $http.put("/api/products/updateUserStore", postData).then(function(response) {
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
		
        service.getUserPurchases = function(dayLimit, page) {
            var deferred = $q.defer();
            
            $http.get("/api/products/getUserPurchases/" + dayLimit + "/" + page).then(function(response) {
                    deferred.resolve(response.payload);
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
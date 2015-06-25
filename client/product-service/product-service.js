angular.module('web').factory('productService',['$q', '$http',

    function($q, $http) {
        var service = {
			stores: [],
			userStores: []
		};

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

            $http.get("/api/products/getStores").then(
				function(response) {
                    for(var resultIndex in response.data.payload){
                        service.stores.push(response.data.payload[resultIndex]); // it's stored as {"0":x,"1":y}
                    }
                    response.data.payload = service.stores;
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
            if (!page){
				service.userStores = [];
			}
            page = page || 1;
            var deferred = $q.defer();

            $http.get("/api/products/getUserStores/"+page).then(
				function(response) {
					Array.prototype.push.apply(service.userStores,response.data.payload.result);

					if (response.data.payload.meta.next_page) {
						service.getUserStores(response.data.payload.meta.next_page);
					}
                    response.data.payload = response.data.payload.result; // trimming meta
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
                    deferred.resolve(response.data.payload);
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
angular.module('web').factory('productService',['$q', '$http',

    function($q, $http) { //NOSONAR Functions should not have too many lines
        var userStoreMap = {};

        var service = {
            stores: [],
            userStores: []
        };

        var map = {
            store: function(store){
                store.isConnecting = false;
                store.isDisconnecting = false;
                store.isWorking = function(){
                    return this.isConnecting || this.isDisconnecting;
                };
                store.href = 'https://www.google.com/search?q='+encodeURIComponent(store.name)+'&btnI';
                store.hasConnectionAttempt = function(){
                    return this.id in userStoreMap;
                };
                store.userStore = function(){
                    return userStoreMap[this.id] || {status: function(){
                        return undefined;
                    }};
                };
                store.isConnected = function(){
                    return this.userStore().credentials_status === 'Verified';
                };
                return store;
            },
            userStore: function(userStore){
                userStore.status = function(){
                    return this.credentials_status === 'Invalid' ? 'Invalid Credentials'
                        : this.credentials_status !== 'Verified' ? 'Connecting'
                        : this.scrape_status === 'Done' ? 'Ready' : 'Purchase Review';
                };
                return userStore;
            }
        };

        service.getUser = function() {
            var deferred = $q.defer();

            $http.get("/api/products/getUser").then(
                function(response) {
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

            $http.delete("/api/products/deleteUser").then(
                function(response) {
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

            $http.post("/api/products/addUser", "{}").then(
                function(response) {
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
                        if (response.data.payload.hasOwnProperty(resultIndex)) {
                            service.stores.push(map.store(response.data.payload[resultIndex]));
                        }
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
                    Array.prototype.push.apply(service.userStores,response.data.payload.result.map(map.userStore));

                    // for ease of lookup
                    userStoreMap = {};
                    for (var i = 0; i < service.userStores.length; i++){
                        userStoreMap[service.userStores[i].supermarket_id] = service.userStores[i];
                    }

                    if (response.data.payload.meta.next_page) {
                        service.getUserStores(response.data.payload.meta.next_page);
                    }
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

            $http.get("/api/products/getUserStore/" + userStoreId).then(
                function(response) {
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

            $http.post("/api/products/addUserStore", postData).then(
                function(response) {
                    // success response object model doesn't match userStore fetch
                    response.data.payload.result.supermarket_id = storeid;
                    // add to the userStore list
                    service.userStores.push(map.userStore(response.data.payload.result));
                    userStoreMap[response.data.payload.result.supermarket_id] = response.data.payload.result;
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

            $http.delete("/api/products/deleteUserStore/" + userStoreId).then(
                function(response) {
                    for(var i = 0; i < service.userStores.length; i++){
                        if(service.userStores[i].id === userStoreId){
                            delete userStoreMap[service.userStores[i].supermarket_id];
                            // remove from userStore list
                            service.userStores.splice(i,1);
                            break;
                        }
                    }
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

            $http.put("/api/products/updateUserStore", postData).then(
                function(response) {
                    for(var i = 0; i < service.userStores.length; i++){
                        if(service.userStores[i].id === userStoreId){
                            // the payload result is only a string... "Updated"
                            // it doesn't return a store object like adding does
                            service.userStores[i].credentials_status = 'Not defined';
                            service.userStores[i].scrape_status = 'Not defined';
                            break;
                        }
                    }
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

            $http.get("/api/products/getUserPurchases/" + dayLimit + "/" + page).then(
                function(response) {
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
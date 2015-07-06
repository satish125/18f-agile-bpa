angular.module('web').factory('userService',['$q', '$http',

    function($q, $http) { //NOSONAR Functions should not have too many lines
        var service = {user:{},isLoggedIn:undefined};

        service.loginUser = function(email, password) {
            var deferred = $q.defer();

            var postData = JSON.stringify({
                "email": email,
                "password": password
            });

            $http.post("/api/user/login", postData).then(
                function(response) {
                    deferred.resolve(response.data);
                    if (response.data.code === "success") {
                        service.getUser();
                        service.isLoggedIn = true;
                    } else {
                        service.user = {};
                        service.isLoggedIn = false;
                    }
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };

        service.logoutUser = function() {
            var deferred = $q.defer();

            service.user = {};
            service.isLoggedIn = false;

            $http.get("/api/user/logout").then(
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

        service.registerUser = function(email, zipcode, password) {
            var deferred = $q.defer();

            var postData = JSON.stringify({
                "email": email,
                "password": password,
                "zipcode": zipcode
            });

            $http.post("/api/user/register", postData).then(
                function(response) {
                    deferred.resolve(response.data);
                    if (response.data.code === "success") {
                        service.getUser();
                        service.isLoggedIn = true;
                    } else {
                        service.user = {};
                        service.isLoggedIn = false;
                    }
                },
                function(error) {
                    deferred.reject(error);
                },
                function(value) {
                    deferred.notify(value);
                });

            return deferred.promise;
        };

        service.getUser = function() {
            var deferred = $q.defer();

            $http.get("/api/user/get").then(
                function(response) {
                    deferred.resolve(response.data);
                    if (response.data.code === "success") {
                        service.user = response.data.payload;
                        service.isLoggedIn = true;
                    } else {
                        service.user = {};
                        service.isLoggedIn = false;
                    }
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
angular.module('web').factory('userService',['$q', '$http',

    function($q, $http) {
        var service = {};
        
        service.loginUser = function(email, password) {
            var deferred = $q.defer();
            
            var postData = JSON.stringify({
                "email": email,
                "password": password
            });
            
            $http.post("/api/loginUser", postData).then(function(response) {
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

        service.getUser = function(email) {
            var deferred = $q.defer();
            
            
            $http.get("/api/getUser/"+email).then(function(response) {
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
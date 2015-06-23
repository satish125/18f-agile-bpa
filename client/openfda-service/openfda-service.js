angular.module('web').factory('openfdaService',['$q', '$http',

    function($q, $http) {
        var service = {};
        
        service.recentRecalls = function(dayLimit, recordLimit) {
            var deferred = $q.defer();
            
            var myDayLimit = (dayLimit === "") ? 30 : dayLimit;
            var myRecordLimit = (recordLimit === "") ? 100 : recordLimit;
            
            $http.get("/api/openFDA/recentRecalls/food/"+ myDayLimit + "/" + myRecordLimit ).then(function(response) {
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
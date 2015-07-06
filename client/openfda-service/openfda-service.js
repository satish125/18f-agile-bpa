angular.module('web').factory('openfdaService',['$q', '$http',

    function($q, $http) {
        var map = {
            recentRecalls: function(recall){
                recall.recall_initiation_date = new Date(
                    recall.recall_initiation_date.substring(0,4),
                    (Number(recall.recall_initiation_date.substring(4,6))-1),
                    recall.recall_initiation_date.substring(6,8)
                );
                return recall;
            }
        };

        var service = {};

        service.productMatch = function(product, minMatchingScore, minQualityScore){
            var deferred = $q.defer();

            var type = "food";
            var dayLimit = 365;
            product.source = "iamdata";

            $http.post("/api/openFDA/productMatch/"+type+"/"+dayLimit+"/"+minMatchingScore+"/"+minQualityScore, product).then(function(response) {
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

        service.recentRecalls = function(dayLimit, recordLimit) {
            var deferred = $q.defer();

            var myDayLimit = (dayLimit === "") ? 30 : dayLimit;
            var myRecordLimit = (recordLimit === "") ? 100 : recordLimit;

            $http.get("/api/openFDA/recentRecalls/food/"+ myDayLimit + "/" + myRecordLimit ).then(
                function(response) {
                    response.data.payload = response.data.payload.map(map.recentRecalls);
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
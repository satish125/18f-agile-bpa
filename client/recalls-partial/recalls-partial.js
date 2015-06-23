angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService', 
    function($scope, openfdaService, productService){

        $scope.num_purchases = null;
        $scope.store_purchases = [];
        $scope.recalls = [];
        var dayLimit = 365;

        function getPageOfPurchases(page){
            productService.getUserPurchases(dayLimit, page).then(function(response){
                $scope.store_purchases = response.result;

                //loop through purchases and match
                for(var i = 0, store; store = response.result[i]; i++){
                    for(var j = 0, product; product = store[j]; j++){
                        productMatch(product);
                    }
                }

                $scope.num_purchases = response.meta.totalCount;

                //re-run this function if more pages to get
                if(response.meta.remaining_number_of_request > 0){
                    getPageOfPurchases(page+1);
                }
            });
        }

        function productMatch(product){
            openfdaService.productMatch(product).then(function(response){
                $scope.recalls.push({
                    purchase: product,
                    matches: response
                });
            });
        }

        getPageOfPurchases(1);
    }
]);
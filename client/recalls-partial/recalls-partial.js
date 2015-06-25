angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService', 
    function($scope, openfdaService, productService){

        $scope.num_purchases = 0;
        $scope.num_orders = 0;
        $scope.store_purchases = [];
        $scope.recalls = [];
        var dayLimit = 365;
        var minScore = 0.5;

        function getPageOfPurchases(page){
            productService.getUserPurchases(dayLimit, page).then(function(response){
                $scope.store_purchases = response.result;

                //loop through purchases and match
                for(var i = 0, orders; orders = $scope.store_purchases[i]; i++){
                    for(var j = 0, item; item = orders.purchase_items[j]; j++){
                        $scope.num_purchases++;
                        item.date = orders.date; //pass the date to the php
                        productMatch(item);
                    }
                }

                $scope.num_orders+= response.result.length;

                //re-run this function if more pages to get
                if(response.next_page){
                    getPageOfPurchases(page+1);
                }
            });
        }

        function productMatch(product){
            openfdaService.productMatch(product, minScore).then(function(response){
                if(response.code !== 'success'){
                    //TODO Handle error
                    return;
                }

                $scope.recalls.push({
                    purchase: product,
                    matches: response.payload
                });
            });
        }

        getPageOfPurchases(1);
    }
]);
angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService', 
    function($scope, openfdaService, productService){

        $scope.store_purchases = null; //array of stores and purchases
        $scope.recalls = []; //array of purchase items with possible recalls 

        $scope.num_purchases = 0; //number of items purchased
        $scope.num_orders = 0; //number of purchase events
        $scope.num_checked = 0; //number of purchase items that were successfully checked
        $scope.num_attempted = 0; //number of purchase items that we've attempted to check
        $scope.progress = 0;

        var dayLimit = 365;
        var minScore = 0.5;

        /**
         * @param page 
         */
        function getPageOfPurchases(page){
            productService.getUserPurchases(dayLimit, page).then(function(response){

                //add this page of store purchases to our saved array
                if($scope.store_purchases == null){
                    $scope.store_purchases = [];
                }
                $scope.store_purchases = $scope.store_purchases.concat(response.result);

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
                if(response.code !== 'success' && response.code !== 'NO_MATCH'){
                    //TODO Handle error
                    $scope.num_checked++;
                    return;
                }

                $scope.recalls.push({
                    purchase: product,
                    matches: response.payload
                });
            }).finally(function(){
                //set progress
                $scope.num_attempted++;
                $scope.progress = ($scope.num_attempted/$scope.num_purchases)*100;
            });
        }

        getPageOfPurchases(1);
    }
]);
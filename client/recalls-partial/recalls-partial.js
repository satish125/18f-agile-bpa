angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService', 
    function($scope, openfdaService, productService){

        var dayLimit = 365;
        var minScore = 0.75;

         function init(){
            $scope.store_purchases = null; //array of stores and purchases
            $scope.match_results = getCachedMatches(); //all results from open.fda
            $scope.recalls = {}; //obj of purchase items with possible recalls

            $scope.num_purchases = 0; //number of items purchased
            $scope.num_orders = 0; //number of purchase events
            $scope.num_checked = 0; //number of purchase items that were successfully checked
            $scope.num_attempted = 0; //number of purchase items that we've attempted to check
            $scope.progress = 0;

            getPageOfPurchases(1);
        }

        function getCachedMatches(){
            if(!localStorage['matches']){
                localStorage['matches'] = "{}";
                //todo: set day, check day
            }

            var oldestCacheTime = localStorage['oldestCacheTime'];
            ///if(oldestCacheTime){ var elapsedMS = (new Date()) - (new Date(oldestCacheTime)); var days = elapsedMS/1000/60/60/24; }

            try{ 
                return JSON.parse(localStorage['matches']);
            }catch(e){
                localStorage['matches'] = "{}";
                return {};
            }
        }

        function putCachedMatches(obj){
            localStorage['matches'] = JSON.stringify(obj);
        }

        function setProgress(){
            $scope.progress = ($scope.num_attempted/$scope.num_purchases)*100;
        }

        $scope.recheckAll = function(){
            putCachedMatches({});
            init();
        };

        $scope.sizeOf = function(obj){
            return Object.keys(obj).length;
        };

        /**
         * @param page 
         */
        function getPageOfPurchases(page){
            return productService.getUserPurchases(dayLimit, page).then(function(response){

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
        }//end getPageOfPurchases

        function productMatch(item){
            var product = item.product;

            //check cached recalls for the product
            var cachedProduct = $scope.match_results[product.id];
            if(typeof cachedProduct !== 'undefined'){
                console.log('product '+product.id+' has already been searched for');
                if(cachedProduct!=null){
                    console.log('product '+product.id+' has cached matches');
                    $scope.recalls[product.id] = cachedProduct;
                }
                $scope.num_checked++;
                $scope.num_attempted++;
                setProgress();
                return;
            }

            console.log('product '+product.id+' is not cached');

            return openfdaService.productMatch(item, minScore).then(function(response){
                //error
                if(response.code !== 'success' && response.code !== 'NO_MATCH'){
                    return;
                }

                //checked successfully
                $scope.num_checked++;

                //didn't find any matches
                if(response.payload.results.length === 0){
                    return;
                }

                //add recalls
                $scope.recalls[product.id] = response.payload;
            }).finally(function(){

                //set progress
                $scope.num_attempted++;
                setProgress();

                //cache the results
                if($scope.recalls[product.id]){
                    $scope.match_results[product.id] = $scope.recalls[product.id];
                }else{
                    $scope.match_results[product.id] = null;
                }
                putCachedMatches($scope.match_results);
            });
        }//end productMatch

        $scope.toggleRecall = function(recall){
            if(!recall.expanded){
                recall.expanded = true;
            }else{
                recall.expanded = false;
            }
        };

        init();
    }
]);
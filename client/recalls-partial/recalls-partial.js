angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService',
    function($scope, openfdaService, productService){

        var dayLimit = 365;
        var minScore = 0.6;

        function init(){
            $scope.stores = null; //array of stores and purchases
            $scope.matchResults = getCachedMatches(); //all results from open.fda
            $scope.recalls = {}; //obj of purchase items with possible recalls

            $scope.purchaseCount = 0; //number of items purchased
            $scope.orderCount = 0; //number of purchase events
            $scope.checkCount = 0; //number of purchase items that were successfully checked
            $scope.attemptCount = 0; //number of purchase items that we've attempted to check
            $scope.progress = 0;

            getPageOfPurchases(1);
        }

        function getCachedMatches(){
            var hasCache = !!localStorage['matches'];
            var matchesDate = localStorage['matchesDate'];
            var isExpired = matchesDate && (new Date() - new Date(matchesDate))/1000/60/60/24 > 1;

            if(!hasCache || isExpired){
                putCachedMatches({});
            }

            try{
                return JSON.parse(localStorage['matches']);
            }catch(e){
                putCachedMatches({});
                return {};
            }
        }

        function putCachedMatches(obj){
            localStorage['matches'] = JSON.stringify(obj);
            localStorage['matchesDate'] = new Date().getTime();
        }

        function setProgress(){
            $scope.progress = ($scope.attemptCount/$scope.purchaseCount)*100;
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
                //instatiate an empty array, so we know we're done retrieving results
                if($scope.stores === null){
                    $scope.stores = [];
                }

                //no results found, leave the array empty
                if(!response.result){
                    $scope.progress = 100;
                    return;
                }

                //add this page of store purchases to our saved array
                $scope.stores = $scope.stores.concat(response.result);

                //loop through purchases and match
                for(var i = 0, order; order = response.result[i]; i++){
                    for(var j = 0, item; item = order.purchase_items[j]; j++){
                        $scope.purchaseCount++;

                        //pass the date to the php
                        item.date = order.date;

                        //pass the store to the product for display
                        item.store = order.user_store.store_name;

                        productMatch(item);
                    }
                }

                //sum number of orders
                $scope.orderCount += response.result.length;

                //re-run this function if more pages to get
                if(response.next_page){
                    getPageOfPurchases(page+1);
                }
            });
        }//end getPageOfPurchases

        function productMatch(item){
            var product = item.product;

            //check cached recalls for the product
            var cachedProduct = $scope.matchResults[product.id];
            if(typeof cachedProduct !== 'undefined'){
                if(cachedProduct !== null){
                    $scope.recalls[product.id] = cachedProduct;
                }
                $scope.checkCount++;
                $scope.attemptCount++;
                setProgress();
                return;
            }

            return openfdaService.productMatch(item, minScore).then(function(response){
                //error
                if(response.code !== 'success' && response.code !== 'NO_MATCH'){
                    return;
                }

                //checked successfully
                $scope.checkCount++;

                //didn't find any matches
                if(response.payload.results.length === 0){
                    return;
                }

                //add recalls
                $scope.recalls[product.id] = response.payload;
            }).finally(function(){
                //set progress
                $scope.attemptCount++;
                setProgress();

                //cache the results
                $scope.matchResults[product.id] = $scope.recalls[product.id] ? $scope.recalls[product.id] : null;

                putCachedMatches($scope.matchResults);
            });
        }//end productMatch

        $scope.toggleRecall = function(recall){
            recall.expanded = !recall.expanded;
        };

        init();
    }
]);
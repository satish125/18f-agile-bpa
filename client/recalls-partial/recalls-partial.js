angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService',
    function($scope, openfdaService, productService){ //NOSONAR Functions should not have too many lines

        var dayLimit = 365;
        var minMatchingScore = 0.6;
        var minQualityScore = 0.5;

        function init(){
            //array of stores and purchases
            $scope.purchasesCollected = false;

            //all results from open.fda
            $scope.matchResults = getCachedMatches();

            //if there are no matches, set the oldest cache time to now
            if($scope.sizeOf($scope.matchResults) === 0){
                localStorage['oldestRecallCacheTime'] = new Date().getTime();
            }

            //obj of purchase items with possible recalls
            $scope.recalls = {};

            //number of purchase events
            $scope.purchaseCount = 0;

            //number of purchase items that were successfully checked
            $scope.checkCount = 0;

            //number of purchase items that we've attempted to check
            $scope.attemptCount = 0;

            $scope.progress = 0;

            getPageOfPurchases(1);

            //currently processing item
            $scope.itemName = "";
        }

        function getCachedMatches(){
            try{
                if (localStorage['oldestRecallCacheTime'] && (new Date() - new Date(Number(localStorage['oldestRecallCacheTime'])))/1000/60/60/24 < 1){
                    return JSON.parse(localStorage['matches']) || {};
                }
            }catch(e){
                console.log('unable to parse cached matches');
            }
            return {};
        }

        function putCachedMatches(obj){
            localStorage['matches'] = JSON.stringify(obj);
        }

        function setProgress(itemName){
            $scope.progress = (++$scope.attemptCount/$scope.purchaseCount)*100;
            $scope.itemName = itemName;
        }

        $scope.recheckAll = function(){
            putCachedMatches({});
            init();
        };

        $scope.sizeOf = function(obj){
            return Object.keys(obj).length;
        };

        function getPageOfPurchases(page){
            return productService.getUserPurchases(dayLimit, page).then(function(response){

                $scope.purchasesCollected = true;

                if(response.result){
                    for(var i = 0, order; i < response.result.length; i++){
                        order = response.result[i];
                        for(var j = 0, item; j < order.purchase_items.length; j++){
                            item = order.purchase_items[j];
                            $scope.purchaseCount++;
                            productMatch($.extend(item,{date: order.date, store: order.user_store.store_name}));
                        }
                    }
                    if(response.next_page){
                        getPageOfPurchases(page+1);
                    }
                }

                //no purchases, done.
                if(!response.result || $scope.purchaseCount === 0){
                    console.log('there are no purchases.');
                    $scope.progress = 100;
                }
            }).finally(function(){
                if(!$scope.purchasesCollected){
                    console.log('could not get list of purchases.');
                }
            });
        }

        function productMatch(item){
            var cachedProduct = $scope.matchResults[item.product.id];

            //if there is a cached product
            if(typeof cachedProduct !== 'undefined'){
                //if there are matches
                if(cachedProduct !== null){
                    $scope.recalls[item.product.id] = cachedProduct;
                }
                $scope.checkCount++;
                setProgress(item.name);
            } else {
                //no cached product, call matching api
                openfdaService.productMatch(item, minMatchingScore, minQualityScore).then(function(response){
                    if(response.code === 'success' || response.code === 'NO_MATCH'){
                        $scope.checkCount++;
                        if($scope.sizeOf(response.payload.results) > 0){
                            $scope.recalls[item.product.id] = response.payload;
                        }
                    }else if(response.code === 'system_failure'){
                        console.log(response);
                    }
                }).finally(function(){
                    setProgress(item.name);

                    $scope.matchResults[item.product.id] = $scope.recalls[item.product.id] ? $scope.recalls[item.product.id] : null;

                    putCachedMatches($scope.matchResults);
                });
            }
        }

        $scope.toggleRecall = function(recall, $event){
            if(!recall.expanded || (recall.expanded && $event.target.innerHTML === '×')){
                recall.expanded = !recall.expanded;
            }
        };

        init();
    }
]);
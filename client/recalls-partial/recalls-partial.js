angular.module('web').controller('RecallsPartialCtrl',['$scope', 'openfdaService',  'productService',
    function($scope, openfdaService, productService){

        var dayLimit = 365, minScore = 0.6;

        function init(){
            $scope.purchasesCollected = false;
            $scope.matchResults = getCachedMatches();
            $scope.recalls = {};
            $scope.purchaseCount = 0;
            $scope.checkCount = 0;
            $scope.attemptCount = 0;
            $scope.progress = 0;
            getPageOfPurchases(1);
        }

        function getCachedMatches(){
            try{
				if (localStorage['matchesDate'] && (new Date() - new Date(Number(localStorage['matchesDate'])))/1000/60/60/24 < 1){
					return JSON.parse(localStorage['matches']) || {};
				}
			}catch(e){}
			return {};
		}

        function putCachedMatches(obj){
            localStorage['matches'] = JSON.stringify(obj);
            localStorage['matchesDate'] = new Date().getTime();
        }

        function setProgress(){
            $scope.progress = (++$scope.attemptCount/$scope.purchaseCount)*100;
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
					for(var i = 0, order; order = response.result[i]; i++){
						for(var j = 0, item; item = order.purchase_items[j]; j++){
							$scope.purchaseCount++;
							productMatch($.extend(item,{date: order.date, store: order.user_store.store_name}));
						}
					}
					if(response.next_page){
						getPageOfPurchases(page+1);
					}
                }
            });
        }

        function productMatch(item){
            var cachedProduct = $scope.matchResults[item.product.id];
            if(typeof cachedProduct !== 'undefined'){
                if(cachedProduct !== null){
                    $scope.recalls[item.product.id] = cachedProduct;
                }
                $scope.checkCount++;
                setProgress();
            } else {
				openfdaService.productMatch(item, minScore).then(function(response){
					if(response.code === 'success' || response.code === 'NO_MATCH'){
						$scope.checkCount++;
						if(response.payload.results.length > 0){
							$scope.recalls[item.product.id] = response.payload;
						}
					}
				}).finally(function(){
					setProgress();

					$scope.matchResults[item.product.id] = $scope.recalls[item.product.id] ? $scope.recalls[item.product.id] : null;

					putCachedMatches($scope.matchResults);
				});
			}
        }

        $scope.toggleRecall = function(recall){
            recall.expanded = !recall.expanded;
        };

        init();
    }
]);
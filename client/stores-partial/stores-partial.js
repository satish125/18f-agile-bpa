angular.module('web').controller('StoresPartialCtrl',['$scope','productService',function($scope,productService){

	$scope.stores = [];
	$scope.userStores = [];
	
	productService.getStores().then(
		function(data) {
			$scope.stores = data.payload;
		},
		function(error) {
			$scope.errors.push(error.message);
		}
	);

	function getUserStores(page){

		productService.getUserStores(page).then(
			function(data) {
				$scope.userStores = data.payload;
				// if more pages available, go get them?
				// getUserStores(++page);
			},
			function(error) {
				$scope.errors.push(error.message);
			}
		);
	}
	getUserStores(1);

	$scope.doStoreConnect = function(store){
		console.log(store);

		productService.addUserStore(store.id, store.username, store.password).then(function(response){
			console.log(response);
		});
	};

}]);
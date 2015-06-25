angular.module('web').controller('StoresPartialCtrl',['$scope','productService',function($scope,productService){

	$scope.getStores = function(){
		// filtering userStores out of the full list of stores
		/*
		return productService.stores.filter(function(store){
			return 0 === $scope.getUserStores().filter(function(userStore){
				return userStore.supermarket_id === store.id;
			}).length;
		});
		*/
		return productService.stores.map(function(store){
			store.isConnected = 0 !== $scope.getUserStores().filter(function(userStore){
				return userStore.supermarket_id === store.id;
			}).length;
			return store;
		});
	};
	$scope.getUserStores = function(){
		return productService.userStores;
	};

	if (!productService.stores.length){
		productService.getStores();
	}
	productService.getUserStores();
	// rebuilding on every scope change
	// we should probably tie user-dependent data into the logout function

	$scope.doStoreConnect = function(store){
		productService.addUserStore(store.id, store.username, store.password).then(function(response){
			console.log(response);
		});
	};

}]);
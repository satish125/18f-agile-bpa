angular.module('web').controller('StoresPartialCtrl',['$scope','productService',function($scope,productService){

	$scope.getUserStores = function(){
		return productService.userStores;
	};

	$scope.getUserStoreMap = function(){
		var userStoreMap = {};
		for (var i = 0; i < $scope.getUserStores().length; i++){
			userStoreMap[$scope.getUserStores()[i].supermarket_id] = $scope.getUserStores()[i];
		}
		return userStoreMap;
	};

	$scope.getStores = function(){
		return productService.stores.map(function(store){
			store.hasConnectionAttempt = function(){
				return this.id in $scope.getUserStoreMap();
			};
			store.userStore = function(){
				return $scope.getUserStoreMap()[this.id] || {};
			};
			store.isConnected = function(){
				return this.userStore().credentials_status === 'Verified';
			};
			return store;
		});
	};

	if (!productService.stores.length){
		productService.getStores();
	}
	productService.getUserStores();
	// rebuilding on every scope change
	// we should probably tie user-dependent data into the logout function

	$scope.doStoreConnect = function(store){
		if (store.hasConnectionAttempt()){
			productService.updateUserStore(store.userStore().id, store.username, store.password);
		}else{
			productService.addUserStore(store.id, store.username, store.password);
		}
		// if credentials are unverified, set a timeout, pull back a single store
		// repeat until no longer unverified
	};

	$scope.doStoreDisconnect = function(store){
		productService.deleteUserStore(store.userStore().id);
	};

}]);
angular.module('web').controller('StoresPartialCtrl',['$scope','productService',function($scope,productService){

	$scope.stores = [];

	function setStores(){
		$scope.stores = productService.stores;
		if (productService.userStores.length){
			orderStores();
		}
	}
	function orderStores(){ // order is dependent on having both lists, stores and userStores
		$scope.stores = $scope.$eval('stores | orderBy:[\'-hasConnectionAttempt()\',\'name\']'); // one-time orderBy on initial load
	}

	if (!productService.stores.length){
		$scope.gettingStores = true;
		productService.getStores().then(setStores).finally(function(){$scope.gettingStores = false;});
	}else{ // it persisted in the service
		setStores();
	}

	$scope.getUserStores = function(){
		return productService.userStores;
	};

	$scope.refreshUserStores = function(){
		return productService.getUserStores();
	};

	$scope.refreshUserStores().then(function(){
		if ($scope.stores.length) {
			orderStores();
		}
	});

	$scope.doStoreConnect = function(store){
		var action = {};
		store.isConnecting = true;
		if (store.hasConnectionAttempt()){
			action = productService.updateUserStore(store.userStore().id, store.username, store.password);
		}else{
			action = productService.addUserStore(store.id, store.username, store.password);
		}
		action.finally(function(){
			store.isConnecting = false;
		});
	};

	$scope.doStoreDisconnect = function(store){
		store.isDisconnecting = true;
		productService.deleteUserStore(store.userStore().id)
		.finally(function(){
			store.isDisconnecting = false;
		});
	};

	$scope.toggleStoreConnect = function(store){
		store.expanded = true;
	};

}]);
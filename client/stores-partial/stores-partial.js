angular.module('web').controller('StoresPartialCtrl',['$scope','productService',function($scope,productService){

	$scope.encodeURIComponent = encodeURIComponent;

	$scope.stores = [];

	$scope.getUserStores = function(){
		return productService.userStores.map(function(userStore){
			userStore.status = function(){
				return this.credentials_status === 'Invalid' ? 'Invalid Credentials'
					: this.credentials_status !== 'Verified' ? 'Connecting'
					: this.scrape_status === 'Done' ? 'Ready' : 'Purchase Review';
			};
			return userStore;
		});
	};

	$scope.getUserStoreMap = function(){
		var userStoreMap = {};
		for (var i = 0; i < $scope.getUserStores().length; i++){
			userStoreMap[$scope.getUserStores()[i].supermarket_id] = $scope.getUserStores()[i];
		}
		return userStoreMap;
	};

	function buildScopeStores(){
		$scope.stores = productService.stores.map(function(store){
			store.isConnecting = false;
			store.isDisconnecting = false;
			store.isWorking = function(){return this.isConnecting || this.isDisconnecting;};
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
		$scope.stores = $scope.$eval('stores | orderBy:[\'-hasConnectionAttempt()\',\'name\']'); // one-time orderBy
	}

	$scope.getStores = function(){
		if ($scope.stores.length !== productService.stores.length){
			buildScopeStores();
		}
		return $scope.stores;
	};

	if (!productService.stores.length){
		productService.getStores();
	}
	$scope.refreshUserStores = function(){
		productService.getUserStores().then(buildScopeStores);
	};
	$scope.refreshUserStores();
	// rebuilding on every scope change
	// future release - cleanup user-dependent data into the logout function if it persists in angular services

	$scope.doStoreConnect = function(store){
		var action = {};
		store.isConnecting = true;
		if (store.hasConnectionAttempt()){
			action = productService.updateUserStore(store.userStore().id, store.username, store.password).then(function(response){
				for(var i = 0; i < productService.userStores.length; i++){
					if(productService.userStores[i].id === store.userStore().id){
						// the payload result is only a string... "Updated"
						// it doesn't return a store object like adding does
						productService.userStores[i].credentials_status = 'Not defined';
						productService.userStores[i].scrape_status = 'Not defined';
					}
				}
			});
		}else{
			action = productService.addUserStore(store.id, store.username, store.password).then(function(response){
				response.payload.result.supermarket_id = store.id; // success response object model doesn't match userStore fetch
				productService.userStores.push(response.payload.result); // add to the userStore list
			});
		}
		action.finally(function(){store.isConnecting = false;});
	};

	$scope.doStoreDisconnect = function(store){
		store.isDisconnecting = true;
		productService.deleteUserStore(store.userStore().id)
		.then(function(){
			var userStoreId = store.userStore().id;
			for(var i = 0; i < productService.userStores.length; i++){
				if(productService.userStores[i].id === userStoreId){
					productService.userStores.splice(i,1); // remove from userStore list
					store.isDisconnecting = false;
					return;
				}
			}
		})
		.finally(function(){
			// redundancy in case of error
			// kept original so userStore removal completed at the same time as the disconnect process
			store.isDisconnecting = false;
		});
	};

}]);
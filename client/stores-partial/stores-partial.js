angular.module('web').controller('StoresPartialCtrl',['$scope','productService','$timeout',function($scope,productService,$timeout){

    $scope.stores = [];

    function setStores(){
        $scope.stores = productService.stores;
    }

    if (!productService.stores.length){
        $scope.gettingStores = true;
        productService.getStores().then(setStores).finally(function(){
            $scope.gettingStores = false;
        });
    }
    // it persisted in the service
    else{
        setStores();
    }

    $scope.getUserStores = function(){
        return productService.userStores;
    };

    $scope.refreshUserStores = function(){
        $scope.isRefreshing = true;
        return productService.getUserStores().finally(function(){
            $scope.isRefreshing = false;
        });
    };

    $scope.refreshUserStores();

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
            store.username = '';
            store.password = '';
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
        $scope.stores.map(function(obj){
            obj.expanded = false;
            return obj;
        });
        store.expanded = true;
        $timeout(function(){
            $('#username'+store.id).focus();
        },500);
    };

    $scope.connectedStoresFilter = function(store){
        return store.hasConnectionAttempt();
    };

    $scope.availableStoresFilter = function(store){
        return !store.hasConnectionAttempt();
    };

}]);
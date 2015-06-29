angular.module('web').controller('HomePartialCtrl',[ '$scope', '$state', '$stateParams', 'openfdaService', 'userService',
    function($scope, $state, $stateParams, openfdaService, userService) {

        $scope.RecentRecallsInProgress = true;
        $scope.dayLimit = 90;
        $scope.recordLimit = 100;
        $scope.errorMsg = null;

        $scope.recentRecalls_model = null;

        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });

        openfdaService.recentRecalls($scope.dayLimit, $scope.recordLimit).then(function(recallData) {
            $scope.recentRecalls_model = recallData.payload.map(function(obj){
            	obj.recall_initiation_date = new Date(
            		obj.recall_initiation_date.substring(0,4),
            		(Number(obj.recall_initiation_date.substring(4,6))-1),
            		obj.recall_initiation_date.substring(6,8));
            	return obj;
            });

            if (recallData.code !== "success") {
                $scope.errorMsg = recallData.msg;
            }
        }, function(error) {
            $scope.errorMsg = error.message;
        })['finally'](function() {
            $scope.RecentRecallsInProgress = false;
        });

		$scope.isLoggedIn = function(){
			return userService.isLoggedIn;
		};
        if ($stateParams.logout){
			userService.logoutUser();
		}

    }
]);

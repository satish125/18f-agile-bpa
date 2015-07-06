angular.module('web').controller('HomePartialCtrl',[ '$scope', '$state', '$stateParams', 'openfdaService', 'userService',
    function($scope, $state, $stateParams, openfdaService, userService) {

        $scope.RecentRecallsInProgress = true;
        $scope.dayLimit = 90;
        $scope.recordLimit = 100;
        $scope.errorMsg = null;
        $scope.recentRecalls = [];

        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });

        openfdaService.recentRecalls($scope.dayLimit, $scope.recordLimit)
        .then(function(recallData) {
            $scope.recentRecalls = recallData.payload;

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

angular.module('web').controller('HomePartialCtrl',[ '$scope', '$state', '$stateParams', 'recallService',
    function($scope, $state, $stateParams, recallService) {
        
        $scope.RecentRecallsInProgress = true;
        $scope.dayLimit = 30;
        $scope.recordLimit = 100;
        $scope.errorMsg = null;
        
        $scope.recentRecalls_model = null;        
        
        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });
        
        recallService.recentRecalls($scope.dayLimit, $scope.recordLimit).then(function(recallData) {
            $scope.recentRecalls_model = recallData.payload;
            
            if (recallData.code !== "success") {
                $scope.errorMsg = recallData.msg;
            }
        }, function(error) {
            $scope.errorMsg = error.message;
        })['finally'](function() {
            $scope.RecentRecallsInProgress = false;
        });

    }
    
]);    

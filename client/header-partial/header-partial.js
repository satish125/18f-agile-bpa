angular.module('web').controller('HeaderPartialCtrl',['$scope','$location','userService',
    function($scope,$location,userService) {
        $scope.currentPath = function(){
            return $location.path();
        };
        $scope.isLoggedIn = function(){
            return userService.isLoggedIn;
        };
    }
]);
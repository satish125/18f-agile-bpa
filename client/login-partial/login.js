angular.module('web').controller('LoginCtrl', [ '$scope', '$stateParams', 'userService',
    function($scope, $stateParams, userService) {
        
        $scope.loginInProgress = false;
        $scope.userEmail = null;      
        $scope.userPassword = null; 
        $scope.errorMsg = null;

        $scope.user_session_model = null;        
       
        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });
        
        var loginUser = function() {
            $scope.loginInProgress = true;
            $scope.errorMsg = null;

            userService.loginUser($scope.userEmail, $scope.userPassword).then(function(loginData) {
                $scope.user_session_model = loginData;
            }, function(error) {
                $scope.errorMsg = error.message;
            })['finally'](function() {
                $scope.loginInProgress = false;
            });
        };

    }

]);

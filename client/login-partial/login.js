angular.module('web').controller('LoginCtrl', [ '$scope', '$state', '$stateParams', 'userService',
    function($scope, $state, $stateParams, userService) {
        
        $scope.loginInProgress = false;
        $scope.userEmail = null;      
        $scope.userPassword = null; 
        $scope.errorMsg = null;

        $scope.userLogin_model = null;        
       
        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });
        
        var loginUser = function() {
            $scope.loginInProgress = true;
            $scope.errorMsg = null;

            userService.loginUser($scope.userEmail, $scope.userPassword).then(function(loginData) {
                $scope.userLogin_model = loginData;
                
                if (loginData.code === "success") {
                    $state.go('landing-partial');
                } else {
                    $scope.errorMsg = loginData.msg;
                }
            }, function(error) {
                $scope.errorMsg = error.message;
            })['finally'](function() {
                $scope.loginInProgress = false;
            });
        };

    }

]);

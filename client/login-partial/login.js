angular.module('web').controller('LoginCtrl', [ '$scope', '$state', '$stateParams', 'userService',
    function($scope, $state, $stateParams, userService) {
        $scope.loginInProgress = false;
        $scope.hasloginError = false;

        $scope.login = {};

        $scope.userLogin_model = null;

        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });

        $scope.doLogin = function() {
            $scope.loginInProgress = true;
            $scope.hasloginError = false;
            $scope.login.errorMsg = null;

            userService.loginUser($scope.login.email, $scope.login.password).then(function(loginData) {
                $scope.userLogin_model = loginData.payload;

                if (loginData.code === "success") {
                    $state.go('recalls-partial');
                } else {
                    $scope.login.errorMsg = loginData.msg;
                    $scope.hasloginError = true;
                }
            }, function(error) {
                $scope.login.errorMsg = error.message;
                $scope.hasloginError = true;
            })['finally'](function() {
                $scope.loginInProgress = false;
            });
        };

    }

]);
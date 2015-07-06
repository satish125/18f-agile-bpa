angular.module('web').controller('SignupPartialCtrl',['$scope','$state','userService',function($scope,$state,userService){
    $scope.signup = {errors: []};

    angular.element(document).ready(function () {
        $.material.ripples('.btn');
    });

    $scope.passwordConfirmPattern = {
        test: function() {
            return $('#password')[0].value === $('#confirm')[0].value;
        }
    };

    $scope.doSignup = function() {
        $scope.signup.isInProgress = true;
        $scope.signup.errors = [];

        userService.registerUser($scope.signup.email, $scope.signup.zip, $scope.signup.password)
        .then(
            function(data) {
                $scope.userLogin_model = data.payload;

                if (data.code === "success") {
                    $state.go('stores-partial');
                } else {
                    $scope.signup.errors.push(data.msg);
                }
            },
            function(error) {
                $scope.signup.errors.push(error.message);
            }
        )['finally'](function(){
            $scope.signup.isInProgress = false;
        });
    };

}]);
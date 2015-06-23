angular.module('web').controller('SignupPartialCtrl',['$scope','$state','userService',function($scope,$state,userService){
	$scope.signup = {errors: []};

	$scope.passwordConfirmPattern = {
		test: function(value) {
			return $('#password')[0].value === $('#confirm')[0].value;
		}
	};

	$scope.doSignup = function() {
		$scope.signup.isInProgress = true;
		$scope.signup.errors = [];

		userService.registerUser($scope.signup.email, $scope.signup.password)
		.then(
			function(data) {
				$scope.userLogin_model = data.payload;

				if (data.code === "success") {
					$state.go('landing-partial');
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
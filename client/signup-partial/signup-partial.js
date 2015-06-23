angular.module('web').controller('SignupPartialCtrl',function($scope){
	$scope.signup = {};

	$scope.passwordConfirmPattern = {
		test: function(value) {
			return $('#password')[0].value === $('#confirm')[0].value;
		}
	};

});
angular.module('web').controller('SignupPartialCtrl',function($scope){

	$scope.zipPattern = (function() {
		var regexp = /^\d{5}$/;
		return {
			test: function(value) {
				return regexp.test(value);
			}
		};
	})();

});
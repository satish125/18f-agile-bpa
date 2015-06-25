angular.module('web').controller('HeaderPartialCtrl',['$scope','$location',
	function($scope,$location) {
		$scope.currentPath = function(){
			return $location.path();
		};
	}
]);
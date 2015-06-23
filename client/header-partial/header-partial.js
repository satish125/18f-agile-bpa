angular.module('web').controller('HeaderPartialCtrl',['$scope','$state',
	function($scope,$state) {
		$scope.currentUrl = function(){
			return $state.current.url;
		};
	}
]);
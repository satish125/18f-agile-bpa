angular.module('web').controller('StoresPartialCtrl',['$scope','productService',function($scope,productService){

	$scope.stores = [];

	$scope.demoData = [
		{
			name: 'Amazon',
			imageSrc: 'https://bagiq.com/images/store-logos/store-amazon.svg'
		},
		{
			name: 'PeaPod',
			imageSrc: 'https://bagiq.com/images/store-logos/store-peapod.png'
		},
		{
			name: 'Walmart',
			imageSrc: 'https://bagiq.com/images/store-logos/store-walmart.svg'
		}
	];
	
	productService.getStores().then(
		function(data) {
			$scope.stores = data.payload;
		},
		function(error) {
			$scope.errors.push(error.message);
		}
	);

	function getUserStores(page){

		productService.getUserStores(page)
		.then(
			function(data) {
				$scope.userStores = data.payload;
				// if more pages available, go get them?
				// getUserStores(++page);
			},
			function(error) {
				$scope.errors.push(error.message);
			}
		);
	}
	getUserStores(1);

}]);
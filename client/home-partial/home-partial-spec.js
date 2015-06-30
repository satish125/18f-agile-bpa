describe('Test suite for HomePartialCtrl', function() {

	beforeEach(module('web'));

	var scope, ctrl, _openfdaService;

	describe('On loading the home page', function() {

		beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, $state, $stateParams, openfdaService) {
			scope = $rootScope.$new();
			scope.dayLimit = 1;
			scope.recordLimit = 1;

			_openfdaService = openfdaService;

			spyOn(_openfdaService, 'recentRecalls').and.callThrough();

			ctrl = $controller('HomePartialCtrl', {
				$scope: scope,
				$state: $state,
				$stateParams: $stateParams,
				openfdaService: _openfdaService
			});		
			
		}));
		
		it('should make recent recall request to the server with the passed day and record limits', function() {
			expect(_openfdaService.recentRecalls).toHaveBeenCalled();
			expect(_openfdaService.recentRecalls).toHaveBeenCalledWith(scope.dayLimit, scope.recordLimit);
		});
	});

});
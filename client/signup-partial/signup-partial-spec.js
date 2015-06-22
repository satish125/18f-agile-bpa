describe('SignupPartialCtrl', function() {

	beforeEach(module('web'));

	var scope,ctrl;

    beforeEach(inject(function($rootScope, $controller) {
		scope = $rootScope.$new();
		ctrl = $controller('SignupPartialCtrl', {$scope: scope});
    }));

	it('should ...', inject(function() {

		expect(1).toEqual(1);

	}));

});
describe('Test suite for HomePartialCtrl', function() {

    beforeEach(module('web'));

    var scope, ctrl, _openfdaService, _userService;

    describe('On loading the home page', function() {

        beforeEach(inject(function($rootScope, $controller, $state, $stateParams, openfdaService, userService) {
            _openfdaService = openfdaService;
            _userService = userService;
            scope = $rootScope.$new();
            scope.dayLimit = 1;
            scope.recordLimit = 1;
            scope.isLoggedIn = _userService.isLoggedIn;

            spyOn(_openfdaService, 'recentRecalls').and.callThrough();

            ctrl = $controller('HomePartialCtrl', {
                $scope: scope,
                $state: $state,
                $stateParams: $stateParams,
                openfdaService: _openfdaService,
                userService: _userService
            });

        }));

        it('should make recent recall request to the server with the passed day and record limits', function() {
            expect(_openfdaService.recentRecalls).toHaveBeenCalled();
            expect(_openfdaService.recentRecalls).toHaveBeenCalledWith(scope.dayLimit, scope.recordLimit);
        });

        it('should determine that the user is logged in', function() {
            expect(scope.isLoggedIn).toBeTruthy();
        });
    });

});
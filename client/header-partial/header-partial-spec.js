describe('Test suite for HeaderPartialCtrl', function() {

    beforeEach(module('web'));

    var scope, ctrl, $userService;

    describe('On getting the current location after setting to /#/home', function() {

        beforeEach(inject(function($rootScope, $controller, $state, $location, userService) {
            scope = $rootScope.$new();
            $userService = userService;

            ctrl = $controller('HeaderPartialCtrl', {
                $scope: scope,
                location: $location,
                userService: userService
            });

            $location.path('/#/login');
        }));

        it("should be /#/login", function() {
            expect(scope.currentPath()).toBe('/#/login');
        });

        it("should be false if logged out",function(){
            $userService.logoutUser();
            expect(scope.isLoggedIn()).toBe(false);
        });
    });

});
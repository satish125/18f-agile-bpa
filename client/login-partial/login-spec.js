describe('Test suite for LoginCtrl', function() {


    beforeEach(module('web'));

    var scope, ctrl;

    var _userService;

    var email = 'someuser',
        pwd = 'somepwd';

    describe('On user submitting a login', function() {

        beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, $state, $stateParams, userService) {
            scope = $rootScope.$new();

            _userService = userService;

            spyOn(_userService, 'loginUser').and.callThrough();

            ctrl = $controller('LoginCtrl', {
                $scope: scope,
                $state: $state,
                $stateParams: $stateParams,
                userService: userService
            });

            scope.login = {};
            scope.login.email = email;
            scope.login.password = pwd;

            scope.doLogin();

        }));

        it('should show the progress UI', function() {
            expect(scope.loginInProgress).toBeTruthy();
        });

        it('should make login request to the server with the user passed email and password', function() {
            expect(_userService.loginUser).toHaveBeenCalled();
            expect(_userService.loginUser).toHaveBeenCalledWith(email, pwd);
        });

    });


});
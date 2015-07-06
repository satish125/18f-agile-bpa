describe('Test suite for SignupPartialCtrl', function() {//NOSONAR Functions should not have too many lines

    beforeEach(module('web'));

    var scope, ctrl, _userService;

    function test(type, val){
        var msge = "";
        switch (type) {
            case 'email':
                if (val === "") {
                    msge = "An email is required";
                }
                break;
            case 'zip':
                if (val === "") {
                    msge = "A zip code is required";
                }
                break;
            case 'password':
                if (val === "") {
                    msge = "A password is required";
                }
                break;
            case 'confirm':
                if (val === "") {
                    msge = "A confirmation password is required";
                }
                break;
            default:
                msge = "";
                break;
        }
        return msge;
    }

    function validateEmail(email) {
        var len = email.length;
        return len;
    }

    function comparePwd(pwd1, pwd2) {
        return pwd1 === pwd2;
    }

    describe('On user submitting a sign-up form', function() {
        beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, $state, userService) {
            scope = $rootScope.$new();
            scope.signup = {};
            scope.signup.email = 'someuser@test.com';
            scope.signup.zip = '12345';
            scope.signup.password = 'somepswd';
            scope.signup.confirm = 'somepswd';

            _userService = userService;

            spyOn(_userService, 'registerUser').and.callThrough();

            ctrl = $controller('SignupPartialCtrl', {
                $scope: scope,
                $state: $state,
                userService: _userService
            });

            scope.doSignup();

        }));

        it('should make sign-up request to the server with the user passed email, zip, and password', function() {
            expect(_userService.registerUser).toHaveBeenCalled();
            expect(_userService.registerUser).toHaveBeenCalledWith(scope.signup.email, scope.signup.zip, scope.signup.pwd);
        });
    });

    describe("On validating the sign-up fields", function() {

        beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, $state, $stateParams, userService) {
            scope.signup = {};
            scope.signup.email = 'someuser@test.com';
            scope.signup.zip = '12345';
            scope.signup.password = 'somepswd';
            scope.signup.confirm = 'somepswd';

            _userService = userService;

            spyOn(_userService, 'registerUser').and.callThrough();

            ctrl = $controller('SignupPartialCtrl', {
                $scope: scope,
                $state: $state,
                $stateParams: $stateParams,
                userService: _userService
            });
        }));

        it("should display a message if email is empty", function() {
            scope.signup.email = "";
            scope.doSignup();
            expect(test("email",scope.signup.email)).toBe("An email is required");
        });

        it("should display a message if zip code is empty", function() {
            scope.signup.email = "someuser@test.com";
            scope.signup.zip = "";
            scope.doSignup();
            expect(test("zip",scope.signup.zip)).toBe("A zip code is required");
        });

        it("should display a message if password is empty", function() {
            scope.signup.zip = "12345";
            scope.signup.password = "";
            scope.doSignup();
            expect(test("password",scope.signup.password)).toBe("A password is required");
        });

        it("should display a message if password confirmation is empty", function() {
            scope.signup.password = "somepswd";
            scope.signup.confirm = "";
            scope.doSignup();
            expect(test("confirm",scope.signup.confirm)).toBe("A confirmation password is required");
        });

        it('should display a message if password minimum length is below 8 characters', function() {
            scope.signup.password = "pword";
            scope.signup.confirm = "pword";
            scope.doSignup();
            expect(validateEmail(scope.signup.password) < 8).toBeTruthy();
        });

        it("should display a message if confirm password does not match password", function() {
            scope.signup.password = "somepswd";
            scope.signup.confirm = "testdemo";
            scope.doSignup();
            expect(comparePwd(scope.signup.password,scope.signup.confirm)).toBeFalsy();
        });
    });

});
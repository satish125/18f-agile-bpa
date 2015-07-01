describe('Test suite for SignupPartialCtrl', function() { //NOSONAR Functions should not have too many lines
/*
	beforeEach(function() {
		module('web');
		module('templates');
	});
*/	
    beforeEach(module('web'));

	var scope, ctrl, form, formElem, templateHtml, _userService;

    var email = 'someuser@test.com',
		zip = '12345',
        pwd = 'somepswd';

	describe('On user submitting a sign-up form', function() {

		beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, $state, $stateParams, userService) {
			scope = $rootScope.$new();

			_userService = userService;

			spyOn(_userService, 'registerUser').and.callThrough();

			ctrl = $controller('SignupPartialCtrl', {
				$scope: scope,
				$state: $state,
				$stateParams: $stateParams,
				userService: _userService
			});		
			
			scope.signup = {};
			scope.signup.email = email;
			scope.signup.zip = zip;
			scope.signup.password = pwd;
			scope.signup.confirm = pwd;

			scope.doSignup();

		}));
		
		it('should make sign-up request to the server with the user passed email, zip, and password', function() {
			expect(_userService.registerUser).toHaveBeenCalled();
			expect(_userService.registerUser).toHaveBeenCalledWith(email, zip, pwd);
		});
	});

	describe("On validating the sign-up fields", function() {
		
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
			}
			return msge;
		}
		
		function validateEmail(email) {
			var len = email.length;
			return len;
		}
		
		function comparePwd(pwd1, pwd2) {
			var eq = false;
			if (pwd1 === pwd2) {eq = true;}
			return eq;
		}

		beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, $state, $stateParams, userService) {
			scope = $rootScope.$new();

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
			scope.signup = {};
			scope.signup.email = "";
			scope.signup.zip = zip;
			scope.signup.password = pwd;
			scope.signup.confirm = pwd;
			scope.doSignup();		
			expect(test("email",scope.signup.email)).toBe("An email is required");
			
		});

		it("should display a message if zip code is empty", function() {
			scope.signup = {};
			scope.signup.email = email;
			scope.signup.zip = "";
			scope.signup.password = pwd;
			scope.signup.confirm = pwd;
			scope.doSignup();
			expect(test("zip",scope.signup.zip)).toBe("A zip code is required");
		});

		it("should display a message if password is empty", function() {
			scope.signup = {};
			scope.signup.email = email;
			scope.signup.zip = zip;
			scope.signup.password = "";
			scope.signup.confirm = pwd;
			scope.doSignup();
			expect(test("password",scope.signup.password)).toBe("A password is required");
		});

		it("should display a message if password confirmation is empty", function() {
			scope.signup = {};
			scope.signup.email = email;
			scope.signup.zip = zip;
			scope.signup.password = pwd;
			scope.signup.confirm = "";
			scope.doSignup();
			expect(test("confirm",scope.signup.confirm)).toBe("A confirmation password is required");
		});
		
		it('should display a message if password minimum length is below 8 characters', function() {
			scope.signup = {};
			scope.signup.email = email;
			scope.signup.zip = zip;
			scope.signup.password = "pword";
			scope.signup.confirm = "pword";
			scope.doSignup();
			expect(validateEmail(scope.signup.password) < 8).toBeTruthy();
		});

		it("should display a message if confirm password does not match password", function() {
			scope.signup = {};
			scope.signup.email = email;
			scope.signup.zip = zip;
			scope.signup.password = pwd;
			scope.signup.confirm = "testdemo";
			scope.doSignup();
			expect(comparePwd(scope.signup.password,scope.signup.confirm)).toBeFalsy();
		});			
		
	});
	
});
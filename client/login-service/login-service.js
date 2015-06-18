angular.module('web').factory('loginService',function($http) {

	var loginService = {};

	loginService.signIn = function(username, password){
		return $http.get('api/login/'+username+'/'+password);
	};

	return loginService;
});
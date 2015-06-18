angular.module('web').factory('loginService',function($http) {

	var loginService = {};

	loginService.signIn = function(username, password){
		return $http.get('api/bpa.php/loginService/'+username+'/'+password);
	}

	return loginService;
});
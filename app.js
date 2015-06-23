angular.module('web', ['ui.bootstrap','ui.utils','ui.router','ngAnimate']);

angular.module('web').config(function($stateProvider, $urlRouterProvider) {

    $stateProvider.state('login', {
        url: '/login',
        templateUrl: 'client/login-partial/login.html'
    });

    $stateProvider.state('home-partial', {
        url: '/',
        templateUrl: 'client/home-partial/home-partial.html'
    });

    $stateProvider.state('landing-partial', {
        url: '/landing',
        templateUrl: 'client/landing-partial/landing-partial.html'
    });

    $stateProvider.state('signup-partial', {
        url: '/signup',
        templateUrl: 'client/signup-partial/signup-partial.html'
    });
    $stateProvider.state('stores-partial', {
        url: '/connect',
        templateUrl: 'client/stores-partial/stores-partial.html'
    });
    /* Add New States Above */
    $urlRouterProvider.otherwise('/');

});

angular.module('web').run(function($rootScope) {

    $rootScope.safeApply = function(fn) {
        var phase = $rootScope.$$phase;
        if (phase === '$apply' || phase === '$digest') {
            if (fn && (typeof(fn) === 'function')) {
                fn();
            }
        } else {
            this.$apply(fn);
        }
    };

    $.material.init();
});

angular.module('web').directive('ngFocus', [function() {
	var FOCUS_CLASS = 'ng-focused';
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function(scope, element, attrs, ctrl) {
			ctrl.$focused = false;
			element.bind('focus', function(evt) {
				element.addClass(FOCUS_CLASS);
				scope.$apply(function() {ctrl.$focused = true;});
			}).bind('blur', function(evt) {
				element.removeClass(FOCUS_CLASS);
				scope.$apply(function() {ctrl.$focused = false;});
			});
		}
	};
}]);
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

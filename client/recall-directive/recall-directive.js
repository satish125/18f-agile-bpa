angular.module('web').directive('recallDirective', function() {
    return {
        restrict: 'A',
        link: function() {
            //make the sharethis buttons on the page
            if(window.stButtons){
                window.stButtons.makeButtons();
            }
        }
    };
});
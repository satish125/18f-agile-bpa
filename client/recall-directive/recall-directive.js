angular.module('web').directive('recallDirective', function() {

	//make the sharethis buttons on the page
	if(window.stButtons){
		window.stButtons.makeButtons();
	}
});
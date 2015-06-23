angular.module('web').controller('HomePartialCtrl',[ '$scope', '$state', '$stateParams', 'openfdaService',
    function($scope, $state, $stateParams, recallService) {
        
        $scope.RecentRecallsInProgress = true;
        $scope.dayLimit = 30;
        $scope.recordLimit = 100;
        $scope.errorMsg = null;
        
        $scope.recentRecalls_model = null;        
        
        angular.element(document).ready(function () {
            $.material.ripples('.btn');
        });
        
        recallService.recentRecalls($scope.dayLimit, $scope.recordLimit).then(function(recallData) {
            $scope.recentRecalls_model = recallData.payload.map(function(obj){
            	obj.recall_initiation_date = new Date(
            		obj.recall_initiation_date.substring(0,4),
            		obj.recall_initiation_date.substring(4,6),
            		obj.recall_initiation_date.substring(6,8));
            	return obj;
            });
            
            if (recallData.code !== "success") {
                $scope.errorMsg = recallData.msg;
            }
        }, function(error) {
            $scope.errorMsg = error.message;
        })['finally'](function() {
            $scope.RecentRecallsInProgress = false;
        });

        $scope.demoData = [
	    {
	        recall_number: "F-0489-2015",
	        reason_for_recall: "Protein supplement fails to declare allergen: milk",
	        status: "Ongoing",
	        distribution_pattern: "Nationwide",
	        product_quantity: null,
	        recall_initiation_date: "yyyymmdd",
	        state: "ME",
	        event_id: "63260",
	        product_type: "Food",
	        product_description: "Hunger and Weight Vanilla  Dietary Supplement Packaged under the labels Stronger Faster Healthier Whey: 2 lb (32 oz),  and 10 oz sizes",
	        country: "US",
	        city: "Warren",
	        recalling_firm: "Maine Natural Health, Inc.",
	        report_date: "20141119",
	        "@epoch": 1424553174.836488,
	        voluntary_mandated: "Voluntary: Firm Initiated",
	        classification: "Class I",
	        code_info: "All lots codes that fail to declare the allergens: milk",
	        "@id": "0015ee8ba126a8441eed062b89a6c1db7dee83a3f0fd5fed41c7d9022355b5ac",
	        "openfda": {},
	        initial_firm_notification: "Press Release"
	    },
	    {
	        recall_number: "F-0489-2015",
	        reason_for_recall: "Protein supplement fails to declare allergen: milk",
	        status: "Ongoing",
	        distribution_pattern: "Nationwide",
	        product_quantity: null,
	        recall_initiation_date: "1288323623006",
	        state: "ME",
	        event_id: "63260",
	        product_type: "Food",
	        product_description: "Hunger and Weight Vanilla  Dietary Supplement Packaged under the labels Stronger Faster Healthier Whey: 2 lb (32 oz),  and 10 oz sizes",
	        country: "US",
	        city: "Warren",
	        recalling_firm: "Maine Natural Health, Inc.",
	        report_date: "20141119",
	        "@epoch": 1424553174.836488,
	        voluntary_mandated: "Voluntary: Firm Initiated",
	        classification: "Class I",
	        code_info: "All lots codes that fail to declare the allergens: milk",
	        "@id": "0015ee8ba126a8441eed062b89a6c1db7dee83a3f0fd5fed41c7d9022355b5ac",
	        "openfda": {},
	        initial_firm_notification: "Press Release"
	    }];
    }
]);

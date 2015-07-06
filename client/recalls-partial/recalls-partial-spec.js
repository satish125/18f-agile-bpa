describe('Test suite for RecallsPartialCtrl', function() {

    beforeEach(module('web'));

    var scope, ctrl, _productService;

    describe('On the recent recalls page', function() {
        beforeEach(inject(function($rootScope, $controller, $q, $httpBackend, productService) {
            scope = $rootScope.$new();
            scope.dayLimit = 365;

            _productService = productService;

            spyOn(_productService, 'getUserPurchases').and.callThrough();

            ctrl = $controller('RecallsPartialCtrl', {
                $scope: scope,
                productService: _productService
            });

        }));

        it('should make user purchases request to the server with the passed day limit and page', function() {
            expect(_productService.getUserPurchases).toHaveBeenCalled();
            expect(_productService.getUserPurchases).toHaveBeenCalledWith(scope.dayLimit, 1);
        });

    });

});
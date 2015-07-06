describe('Test suite for openfdaService', function(openfdaService) {

    beforeEach(module('web'));

    var scope, _openfdaService, promise, response;

    beforeEach(inject(function(openfdaService) {

        _openfdaService = openfdaService;
        spyOn(openfdaService, "recentRecalls").and.callThrough();
        spyOn(openfdaService, "productMatch").and.callThrough();
        //$httpBackend = $injector.get('$httpBackend');

        //spyOn(openfdaService, "recentRecalls").and.callThrough();
    }));

    it('should call the recentRecalls method',inject(function() {
        promise = _openfdaService.recentRecalls(90,10);
        expect(_openfdaService.recentRecalls).toHaveBeenCalled();
    }));

    it('should return a promise',inject(function() {
        expect(promise).not.toBe(false);
    }));

    it('should call the productMatch method',inject(function() {
        promise = _openfdaService.productMatch({}, 1, 1);
        expect(_openfdaService.productMatch).toHaveBeenCalled();
    }));
});
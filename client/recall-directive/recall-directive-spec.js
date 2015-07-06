describe('Test suite for recallDirective', function() {

  beforeEach(module('web'));

  var scope,compile;

  beforeEach(inject(function($rootScope,$compile) {
    scope = $rootScope.$new();
    compile = $compile;
  }));

  it('should populate the ShareThis button', function() {
    var element = compile("<span class='st_sharethis_large' displayText='ShareThis'></span>")(scope);
    expect(element.text().length > 0);
  });
});
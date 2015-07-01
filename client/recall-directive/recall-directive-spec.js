describe('recallDirective', function() {

  beforeEach(module('web'));

  var scope,compile;

  beforeEach(inject(function($rootScope,$compile) {
    scope = $rootScope.$new();
    compile = $compile;
  }));

  it('should ...', function() {

    var element = compile("<span class='st_sharethis_large' displayText='ShareThis'></span>")(scope);
    console.log(element.text());
    expect(element.text().length > 0);
  });
});
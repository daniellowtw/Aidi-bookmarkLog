'use strict';

describe('Controller: TagcontrollerCtrl', function () {

  // load the controller's module
  beforeEach(module('aidiApp'));

  var TagcontrollerCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    TagcontrollerCtrl = $controller('TagcontrollerCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});

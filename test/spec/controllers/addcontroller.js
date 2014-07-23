'use strict';

describe('Controller: AddcontrollerCtrl', function () {

  // load the controller's module
  beforeEach(module('aidiApp'));

  var AddcontrollerCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    AddcontrollerCtrl = $controller('AddcontrollerCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});

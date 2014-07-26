'use strict';

describe('Service: profileFactory', function () {

  // load the service's module
  beforeEach(module('aidiApp'));

  // instantiate service
  var profileFactory;
  beforeEach(inject(function (_profileFactory_) {
    profileFactory = _profileFactory_;
  }));

  it('should do something', function () {
    expect(!!profileFactory).toBe(true);
  });

});

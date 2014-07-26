'use strict';

describe('Filter: doFilterFromSearch', function () {

  // load the filter's module
  beforeEach(module('aidiApp'));

  // initialize a new instance of the filter before each test
  var doFilterFromSearch;
  beforeEach(inject(function ($filter) {
    doFilterFromSearch = $filter('doFilterFromSearch');
  }));

  it('should return the input prefixed with "doFilterFromSearch filter:"', function () {
    var text = 'angularjs';
    expect(doFilterFromSearch(text)).toBe('doFilterFromSearch filter: ' + text);
  });

});

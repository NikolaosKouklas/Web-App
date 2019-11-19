var app = angular.module('myApp', ['ngMaterial','ngMessages']); //,'ngTable'

// e.g. {{ item | makeUppercase }}
/*app.filter('makeUppercase', function () {
  return function (item) {
      return item.toUpperCase();
  };
});*/

// e.g. <tr ng-repeat="item in items | startsWithKeyword:keyword">
/*app.filter('startsWithKeyword', function () {
  return function (items, keyword) {
    var filtered = [];
    
    if(keyword==undefined){
        return items;
    }
    
    var strLowerKeyword = (keyword + "").toLowerCase();
    
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      if (item.lastname.toLowerCase().indexOf(strLowerKeyword)===0) {
        filtered.push(item);
      }
    }
    
    return filtered;
  };
});*/

// e.g. <tr ng-repeat="item in items | gradeGreaterThan:grade">
/*app.filter('gradeGreaterThan', function () {
    return function (input, grade) {
        var output = [];
        if (isNaN(grade)) {
            output = input;
        }
        else {
            angular.forEach(input, function (item) {
                if (item.grade > grade) {
                    output.push(item)
                }
            });
        }
        return output;
    }
});*/

/*app.filter('myFilter', function() {
  return function(input, optional1, optional2) {

    var output;
    // Do filter work here
    return output;
  }
});*/

/*app.filter('myFilter', function() {
  return function(items) {
    var out = [];
    angular.forEach(items, function(item) {
      if (item.type === 'static') {
        out.push(item)
      }
    })
    return out;
  }
});*/
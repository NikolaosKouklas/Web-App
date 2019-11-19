app.config(function($mdDateLocaleProvider) {
    
    $mdDateLocaleProvider.parseDate = function(dateString) {
        var m = moment(dateString, 'D/M/YYYY', true);
        return m.isValid() ? m.toDate() : '';
    };
    
    $mdDateLocaleProvider.formatDate = function(date) {
        var m = moment(date);
        return m.isValid() ? m.format('D/M/YYYY') : null;
    };
    
});



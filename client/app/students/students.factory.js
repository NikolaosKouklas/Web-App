app.factory("studentsFactory", function($http){
 
    var factory = {};
 
    // read all students
    factory.readStudents = function(){
        return $http({
            method: 'GET',
            url: 'http://192.168.1.101:8080/api/v1/students'
        });
    };
     
    // create student
    factory.createStudent = function($scope){
        
        return $http({
            method: 'POST',
            data: {
                'firstname' : $scope.firstname,
                'lastname' : $scope.lastname,
                'grade' : parseFloat($scope.grade),
                'birth_date' : moment($scope.birth_date).format("YYYY-MM-DD")
            },
            url: 'http://192.168.1.101:8080/api/v1/students'
        });
    };
 
    factory.readOneStudent = function(id){
        return $http({
            method: 'GET',
            url: 'http://192.168.1.101:8080/api/v1/students/' + id
        });
    };
     
    // update student
    factory.updateStudent = function($scope){
     
        return $http({
            method: 'PUT',
            data: {
                'firstname' : $scope.firstname,
                'lastname' : $scope.lastname,
                'grade' : parseFloat($scope.grade),
                'birth_date' : moment($scope.birth_date).format("YYYY-MM-DD")
            },
            url: 'http://192.168.1.101:8080/api/v1/students/' + $scope.id
        });
    };
     
    // delete student
    factory.deleteStudent = function(id){
        return $http({
            method: 'DELETE',
            url: 'http://192.168.1.101:8080/api/v1/students/' + id
        });
    };
     
    // search all products
    /*factory.searchStudents = function(keyword){
        if (keyword == "") {
            return $http({
                method: 'GET',
                url: 'http://192.168.1.101:8080/api/v1/students'
            });
        }
        
        return $http({
            method: 'GET',
            url: 'http://192.168.1.101:8080/api/v1/students/search/' + keyword
        });
    };*/
     
    return factory;
});
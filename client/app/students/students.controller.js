app.controller('studentsController', function($scope, $mdDialog, $mdToast, studentsFactory, $http){ //, NgTableParams
    
    // functions for pagination start here//
    $scope.pagConf = function() {
        
        /*$scope.f1 = {'lastname': undefined};
        $scope.f2 = {'firstname': undefined};
        $scope.f3 = {'grade': undefined};*/
        
        $scope.currentPage = 1;
        
        if ($scope.students.length == 0) {
            $scope.totalPages = 1;
        }
        else {
            $scope.totalPages = Math.ceil($scope.students.length / $scope.itemsPerPage);
        }
        
        $scope.beginIndex = 0;
        $scope.lastPage = $scope.totalPages;
    };
    
    $scope.updateTotalPages = function() {
        
        if($scope.itemsPerPage == undefined) {
            return;
        }
        
        if ($scope.students.length == 0) {
            $scope.totalPages = 1;
        }
        else {
            $scope.totalPages = Math.ceil($scope.students.length / $scope.itemsPerPage);
        }
        
        $scope.lastPage = $scope.totalPages;
        
        if($scope.currentPage > $scope.lastPage){
            $scope.currentPage = $scope.lastPage;
        }
        
        $scope.beginIndex = ($scope.currentPage - 1) * $scope.itemsPerPage;
    };
    
    $scope.firstPage = function() {
        $scope.currentPage = 1;
        $scope.beginIndex = 0;
    };
    
    $scope.previousPage = function() {
        
        if($scope.currentPage > 1) {
            $scope.currentPage -= 1;
            $scope.beginIndex = ($scope.currentPage - 1) * $scope.itemsPerPage;
        }
    };
    
    $scope.finalPage = function() {
        $scope.currentPage = $scope.lastPage;
        $scope.beginIndex = ($scope.currentPage - 1) * $scope.itemsPerPage;
    };
    
    $scope.nextPage = function() {
        
        if($scope.currentPage < $scope.lastPage) {
            $scope.currentPage += 1;
            $scope.beginIndex = ($scope.currentPage - 1) * $scope.itemsPerPage;
        }
    };
    // functions for pagination end here//
    
    // for orderBy // e.g. orderBy:propertyName:reverse
    $scope.propertyName = null;
    $scope.reverse = false;
  
    // function sortBy
    $scope.sortBy = function(propertyName) {
        if($scope.propertyName === propertyName && $scope.reverse){
            $scope.propertyName = null;
            $scope.reverse = false;
        }
        else {
            $scope.reverse = ($scope.propertyName === propertyName) ? !$scope.reverse : false;
            $scope.propertyName = propertyName;
        }
        
    };
    
    // function startsWith
    $scope.startsWith = function (actual, expected) {
        var lowerStr = (actual + "").toLowerCase();
        return lowerStr.indexOf(expected.toLowerCase()) === 0;
    }
    
    // function greaterThan
    $scope.greaterThan = function (actual, expected) {
        
        if(expected==undefined) {
            return true;
        }
        return actual > expected;
    }
    
    // function for grade filter
    // e.g. <tr ng-repeat="row in students | filter:gradeFilter(threshold)">
    /*$scope.gradeFilter = function (threshold) {
        return function(item) {
            if (isNaN(threshold)) {
                return true;
            }
            return item.grade > threshold;
        };
    };*/
    
    // read products
    $scope.readStudents = function(){
 
        // use products factory
        studentsFactory.readStudents().then(function successCallback(response){
            $scope.students = response.data.records;
            $scope.updateTotalPages();
            //$scope.tableParams = new NgTableParams({}, { dataset: $scope.students});
        }, function errorCallback(response){
            $scope.showToast("Unable to read record.");
            //$scope.tableParams = new NgTableParams({}, { dataset: []});
        });
 
    }
     
    // show 'create student form' in dialog box
    $scope.showCreateStudentForm = function(event){
 
        // remove form values
        $scope.clearStudentForm();
        
        $mdDialog.show({
            controller: DialogController,
            templateUrl: './app/students/create_student.template.html',
            parent: angular.element(document.body),
            clickOutsideToClose: true,
            scope: $scope,
            preserveScope: true,
            fullscreen: true
        });  
    }
 
    // create new student
    $scope.createStudent = function(){
     
        studentsFactory.createStudent($scope).then(function successCallback(response){
     
            // tell the user new product was created
            $scope.showToast(response.data.message);
            // refresh the list
            $scope.readStudents();
            // close dialog
            $scope.cancel();
            // remove form values
            //$scope.clearStudentForm();

        }, function errorCallback(response){
            $scope.showToast(response.data.message);
        });
    }
    
    // clear variable / form values
    $scope.clearStudentForm = function(){
        $scope.id = "";
        $scope.firstname = "";
        $scope.lastname = "";
        $scope.grade = "";
        $scope.birth_date = "";
    }
    
    // show toast message
    $scope.showToast = function(message){
        $mdToast.show(
            $mdToast.simple()
                .textContent(message)
                .hideDelay(3000)
                .position("top right")
        );
    }
 
    $scope.readOneStudent = function(id){
 
        // get product to be edited
        studentsFactory.readOneStudent(id).then(function successCallback(response){
     
            // put the values in form
            $scope.id = response.data.record.id;
            $scope.firstname = response.data.record.firstname;
            $scope.lastname = response.data.record.lastname;
            $scope.grade = response.data.record.grade;
            $scope.birth_date = response.data.record.birth_date;
     
            $mdDialog.show({
                controller: DialogController,
                templateUrl: './app/students/read_one_student.template.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                scope: $scope,
                preserveScope: true,
                fullscreen: true
            }).then(function(){
                
                },
     
                // user clicked 'Cancel'
                function() {
                    // clear modal content
                    $scope.clearStudentForm();
                }
            );
        }, 
        function errorCallback(response){
            $scope.showToast("Unable to retrieve record.");
        });
    }
 
    // retrieve record to fill out the form
    $scope.showUpdateStudentForm = function(id){
     
        // get product to be edited
        studentsFactory.readOneStudent(id).then(function successCallback(response){
     
            // put the values in form
            $scope.id = response.data.record.id;
            $scope.firstname = response.data.record.firstname;
            $scope.lastname = response.data.record.lastname;
            $scope.grade = response.data.record.grade;
            $scope.birth_date = response.data.record.birth_date;
     
            $mdDialog.show({
                controller: DialogController,
                templateUrl: './app/students/update_student.template.html',
                parent: angular.element(document.body),
                targetEvent: event,
                clickOutsideToClose: true,
                scope: $scope,
                preserveScope: true,
                fullscreen: true
            }).then(
                function(){},
     
                // user clicked 'Cancel'
                function() {
                    // clear modal content
                    $scope.clearStudentForm();
                }
            );
     
        }, function errorCallback(response){
            $scope.showToast("Unable to update record.");
        });
     
    }
     
    // update product record / save changes
    $scope.updateStudent = function(){
     
        studentsFactory.updateStudent($scope).then(function successCallback(response){
     
            // tell the user student record was updated
            $scope.showToast(response.data.message);
            // refresh the student list
            $scope.readStudents();
            // close dialog
            $scope.cancel();
            // clear modal content
            //$scope.clearStudentForm();
        },
        function errorCallback(response) {
            $scope.showToast(response.data.reason);
        });
     
    }
     
    $scope.confirmDeleteStudent = function(event, id){
     
        // dialog settings
        var confirm = $mdDialog.confirm()
            .title('Are you sure?')
            .textContent('Student with ID:' + id + ' will be deleted')
            .targetEvent(event)
            .ok('Yes')
            .cancel('No');
     
        // show dialog
        $mdDialog.show(confirm).then(
            // 'Yes' button
            function() {
                // if user clicked 'Yes', delete student record
                $scope.deleteStudent(id);
            },
     
            // 'No' button
            function() {
                // hide dialog
            }
        );
    }
    
    // delete product
    $scope.deleteStudent = function(id){
     
        studentsFactory.deleteStudent(id).then(function successCallback(response){
     
            // tell the user product was deleted
            $scope.showToast(response.data.message);
            // refresh the list
            $scope.readStudents();
     
        }, function errorCallback(response){
            $scope.showToast("Unable to delete record.");
        });
     
    }
     
    // search students
    /*$scope.searchStudents = function(){
     
        // use products factory
        studentsFactory.searchStudents($scope.student_search_keywords).then(function successCallback(response){
            $scope.students = response.data.records;
            $scope.tableParams = new NgTableParams({}, { dataset: $scope.students});
        }, function errorCallback(response){
            $scope.tableParams = new NgTableParams({}, { dataset: []});
            $scope.showToast("Unable to read record.");
        });
    }*/
     
    // methods for dialog box
    function DialogController($scope, $mdDialog) {
        $scope.cancel = function() {
            $mdDialog.cancel();
        };
    }
    
    $scope.today = new Date();
    
    $scope.minDate = new Date(
        $scope.today.getFullYear()-65,
        $scope.today.getMonth(),
        $scope.today.getDate()
    );
    
    $scope.maxDate = new Date(
        $scope.today.getFullYear()-18,
        $scope.today.getMonth(),
        $scope.today.getDate()
    );
    
});
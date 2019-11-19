<?php

use Psr\Container\ContainerInterface;

// include database and object files
include_once 'Database.php';

class Student
{
    protected $container;

    public function _construct(ContainerInterface $container) {
           
        $this->container = $container;
    }
    
    public function getAll($request, $response, $args) {
        
        try {
            
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT id, firstname, lastname, grade, birth_date FROM students ORDER BY id";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            $num = $stmt->rowCount();
            $fetched_data = array();
            
            if($num>0) {
                // fetch() is faster than fetchAll()
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    extract($row); // this will make $row['name'] to just $name only
                    $student_item = array(
                        "id" => intval($id),
                        "firstname" => $firstname,
                        "lastname" => $lastname,
                        "grade" => floatval($grade),
                        "birth_date" => $birth_date
                    );
                    array_push($fetched_data, $student_item);
                }
                $message = "Students found";
            }
            else {
                $message = "No students found";
            }
            
            $status = 200;
            
            $result_arr=array(
                "status" => true,
                "status_code" => $status,
                "message" => $message,
                "num_of_students" => $num,
                "records" => $fetched_data
            );
            
        }
        catch(Throwable $t) {
            
            $status = 500;
            
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to get students. Internal Server Error.",
                "reason" => $t->getMessage()
            );

        }
        
        $db->closeConnection();
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function getOne($request, $response, $args) {
        
        try {
            
            $id = $args['id'];
            
            $db = new Database();
            $conn = $db->getConnection();     
            $query = "SELECT id, firstname, lastname, grade, birth_date FROM students WHERE id=:id";
            
            $stmt = $conn->prepare($query);
            // bind values
            $stmt->bindParam(":id", $id);
            
            $stmt->execute();
                    
            $num = $stmt->rowCount();
                    
            if($num>0) {
                
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                extract($row);
                
                $student_item = array(
                    "id" => intval($id),
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "grade" => floatval($grade),
                    "birth_date" => $birth_date
                );
                
                $status = 200;
                
                $result_arr=array(
                    "status" => true,
                    "status_code" => $status,
                    "message" => "Student found",
                    "found" => true,
                    "record" => $student_item
                );
            }
            else {
                
                $status = 404;
                
                $result_arr=array(
                    "status" => true,
                    "status_code" => $status,
                    "message" => "Student not found",
                    "found" => false
                );
            }       
        }
        catch(Throwable $t) {
            
            $status = 500;
            
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to find student. Internal Server Error.",
                "reason" => $t->getMessage()
            );
        }
        
        $db->closeConnection();
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function create($request, $response, $args) {
        
        try {

            $db = new Database();
            $conn = $db->getConnection();
            $query = "INSERT INTO students (firstname, lastname, grade, birth_date) VALUES (:firstname, :lastname, :grade, :birth_date)";
            $stmt = $conn->prepare($query);
            
            $data = json_decode($request->getBody());
            
            if(is_null($data)) {
                
                $status = 400;
                
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to create a new student",
                    "reason" => "Syntax error in json data"
                );
            }
            elseif(!array_key_exists("firstname",$data) || !array_key_exists("lastname",$data) || !array_key_exists("grade",$data) || !array_key_exists("birth_date",$data)) {
                    
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to create a new student",
                    "reason" => "'firstname','lastname','grade' and 'birth_date' fields must be contained in json data"
                );
            }
            else {
                
                if(!Student::isValidName($data->firstname) || !Student::isValidName($data->lastname)) {
                    
                    $status = 400;
                    
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to create a new student",
                        "reason" => "Invalid firstname or lastname value. Names must contain only letters and have length at least one"
                    );
                }
                elseif(!Student::isValidGrade($data->grade)) {
                    
                    $status = 400;
                    
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to create a new student",
                        "reason" => "Invalid grade value. It must be a float number >=0 and <=20"
                    );
                }
                elseif(!Student::isValidDate($data->birth_date)) {
                    
                    $status = 400;
                    
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to create a new student",
                        "reason" => "Invalid birth date value"
                    );
                }
                else {
                    
                    // bind values
                    $stmt->bindParam(":firstname", $data->firstname);
                    $stmt->bindParam(":lastname", $data->lastname);
                    $stmt->bindParam(":grade", $data->grade);
                    $stmt->bindParam(":birth_date", $data->birth_date);
                    
                    $stmt->execute();
                    
                    $status = 201;
                    
                    $result_arr = array(
                        "status" => true,
                        "status_code" => $status,
                        "message" => "New student was created",
                        "student_id" => intval($conn->lastInsertId())
                    );
                }
            }
        }
        catch (Throwable $t) {
            
            $status = 500;
            
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to create a new student record. Internal Server Error.",
                "reason" => $t->getMessage()
            );
        }

        $db->closeConnection();
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function update($request, $response, $args) {
        
        $id = intval($args['id']);
        
        try {

            $db = new Database();
            $conn = $db->getConnection();
            $query = "UPDATE students SET firstname=:firstname, lastname=:lastname, grade=:grade, birth_date =:birth_date WHERE id=:id";
            $stmt = $conn->prepare($query);
            
            $data = json_decode($request->getBody());
            
            if(is_null($data)) {
                
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "message" => "Unable to update student",
                    "student_id" => intval($id),
                    "reason" => "Syntax error in json data"
                );
            }
            elseif(!array_key_exists("firstname",$data) || !array_key_exists("lastname",$data) || !array_key_exists("grade",$data) || !array_key_exists("birth_date",$data)) {
                    
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to update student",
                    "reason" => "'firstname','lastname','grade' and 'birth_date' fields must be contained in json data"
                );
            }
            else {
                
                if(!Student::isValidName($data->firstname) || !Student::isValidName($data->lastname)) {
                    
                    $status = 400;
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to update student",
                        "student_id" => intval($id),
                        "reason" => "Invalid firstname or lastname value. Names must contain only letters and have length at least one"
                    );
                }
                elseif(!Student::isValidGrade($data->grade)) {
                    
                    $status = 400;
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to update student",
                        "student_id" => intval($id),
                        "reason" => "Invalid grade value. It must be a float number >=0 and <=20"
                    );
                }
                elseif(!Student::isValidDate($data->birth_date)) {
                    
                    $status = 400;
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to update student",
                        "student_id" => intval($id),
                        "reason" => "Invalid birth date value"
                    );
                }
                else {
                    
                    // bind values
                    $stmt->bindParam(":firstname", $data->firstname);
                    $stmt->bindParam(":lastname", $data->lastname);
                    $stmt->bindParam(":grade", $data->grade);
                    $stmt->bindParam(":birth_date", $data->birth_date);
                    $stmt->bindParam(":id", $id);
                    
                    $stmt->execute();
                    
                    if($stmt->rowCount()==1) {
                        
                        $status = 200;
                        $result_arr = array(
                            "status" => true,
                            "status_code" => $status,
                            "message" => "Student was updated",
                            "student_id" => intval($id)
                        );
                    }
                    else {
                        
                        $status = 400;
                        $result_arr = array(
                            "status" => false,
                            "status_code" => $status,
                            "message" => "Unable to update student",
                            "student_id" => intval($id),
                            "reason" => "student id not found or no need to update the same values"
                        );
                    }
                }
            }
        }
        catch (Throwable $t) {
            
            $status = 500;
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to update student. Internal Server Error.",
                "student_id" => intval($id),
                "reason" => $t->getMessage()
            );
        }
        
        $db->closeConnection();
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function delete($request, $response, $args) {
        
        $id = intval($args['id']);
        
        try {
            
            $db = new Database();
            $conn = $db->getConnection();
            $query = "DELETE FROM students WHERE id=:id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            if($stmt->rowCount()==1) {
                        
                $status = 202;
                    
                $result_arr = array(
                    "status" => true,
                    "status_code" => $status,
                    "message" => "Student was deleted",
                    "student_id" => intval($id)
                );
            }
            else {
                        
                $status = 400;
                    
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to delete student",
                    "student_id" => intval($id),
                    "reason" => "student id not found"
                );
            }
        }
        catch(Throwable $t) {
            
            $status = 500;
            
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to delete student. Internal Server Error.",
                "student_id" => intval($id),
                "reason" => $t->getMessage()
            );
        }
        
        $db->closeConnection();
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function filterby($request, $response, $args) {
        
        try {
            
            $filterby = $args['fieldname'];
            
            if($filterby == "firstname" || $filterby == "lastname") {
                
                $db = new Database();
                $conn = $db->getConnection();     
                $query = "SELECT id, firstname, lastname, grade, birth_date FROM students WHERE {$filterby} LIKE :keyword"; // LIKE or '=' (for equal)
                $stmt = $conn->prepare($query);
                
                $data = json_decode($request->getBody());
                
                if(is_null($data)) {
                    
                    $status = 400;
                    
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to filter students",
                        "reason" => "Syntax error in json data"
                    );
                }
                elseif(!array_key_exists("keyword",$data)) {
                    
                    $status = 400;
                    
                    $result_arr = array(
                        "status" => false,
                        "status_code" => $status,
                        "message" => "Unable to filter students",
                        "reason" => "'keyword' field and value are missing from json data"
                    );
                }
                else {
                    
                    $data->keyword=htmlspecialchars(strip_tags($data->keyword));
                    
                    if(empty($data->keyword)) {
                        
                        $status = 400;
                    
                        $result_arr = array(
                            "status" => false,
                            "status_code" => $status,
                            "message" => "Unable to filter students",
                            "reason" => "'keyword' value is missing from json data"
                        );
                    }
                    else {
                        
                        $keyword = "{$data->keyword}%"; // starts with keyword
                        $stmt->bindParam(":keyword", $keyword);
                        $stmt->execute();
                        $num = $stmt->rowCount();
                        
                        $fetched_data = array();
                        if($num>0) {
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                extract($row);
                                $student_item = array(
                                    "id" => intval($id),
                                    "firstname" => $firstname,
                                    "lastname" => $lastname,
                                    "grade" => floatval($grade),
                                    "birth_date" => $birth_date
                                );
                                array_push($fetched_data, $student_item);
                            }
                            $message = "Students found";
                            
                        }
                        else {
                            $message = "No students found";
                        }
                    
                        $status = 200;
                        
                        $result_arr=array(
                            "status" => true,
                            "status_code" => $status,
                            "message" => $message,
                            "filterby" => $filterby,
                            "keyword" => $data->keyword,
                            "num_of_students" => $num,
                            "records" => $fetched_data
                        );
                    }
                }
            }
            else {
                
                $status = 400;
                
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to filter students",
                    "reason" => "'{$filterby}' is invalid field name. Valid field names for filtering are: 'firstname' and 'lastname'"
                );
            }
        }
        catch(Throwable $t) {
            
            $status = 500;
            
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to filter students. Internal Server Error.",
                "reason" => $t->getMessage()
            );
        }
        
        if(isset($db)) {
            
            $db->closeConnection();
        }
        
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function orderby($request, $response, $args) {
        
        try {
            
            $orderby = strtolower($args['fieldname']);
            
            if($orderby != "lastname" && $orderby != "grade") {
                
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to order students",
                    "reason" => "'{$orderby}' is invalid field name. Currently valid field names for ordering are: 'lastname' and 'grade'"
                );
            }
            elseif($request->getBody()=="") {
                
                $ordertype = "asc";
            }
            elseif(is_null($data=json_decode($request->getBody()))){
                
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to order students",
                    "reason" => "Syntax error in json data"
                );
            }
            elseif(!array_key_exists("ordertype",$data)) {
                    
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to order students",
                    "reason" => "'ordertype' field and value are missing from json data"
                );
            }
            elseif(empty($data->ordertype=htmlspecialchars(strip_tags($data->ordertype)))) {
                        
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to order students",
                    "reason" => "Valid order types are: 'asc' and 'desc'"
                );
            }
            elseif(($ordertype=strtolower($data->ordertype))!="asc" && $ordertype!="desc") {
                        
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to order students",
                    "reason" => "'{$ordertype}' is invalid order type. Valid order types are: 'asc' and 'desc'"
                );
            }
            
            if(!isset($status)) {
                
                $db = new Database();
                $conn = $db->getConnection();
                $query = "SELECT id, firstname, lastname, grade, birth_date FROM students ORDER BY {$orderby} {$ordertype}";
                $stmt = $conn->prepare($query);
                       
                $stmt->execute();
                $num = $stmt->rowCount();
                            
                $fetched_data = array();
                if($num>0) {
                                
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        extract($row);
                        $student_item = array(
                            "id" => intval($id),
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "grade" => floatval($grade),
                            "birth_date" => $birth_date
                        );
                        array_push($fetched_data, $student_item);
                    }
                    $message = "Students found";          
                }
                else {
                    $message = "No students found";
                }
                        
                $status = 200;    
                $result_arr=array(
                    "status" => true,
                    "status_code" => $status,
                    "message" => $message,
                    "orderby" => $orderby,
                    "ordertype" => $ordertype,
                    "num_of_students" => $num,
                    "records" => $fetched_data
                );
            }
        }
        catch(Throwable $t) {
            
            $status = 500;
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to order students. Internal Server Error.",
                "reason" => $t->getMessage()
            );
        }
        
        if(isset($db)) {
            
            $db->closeConnection();
        }
        
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }

    public function abovegrade($request, $response, $args) {
        
        try {
            
            $data = json_decode($request->getBody());
            
            if(is_null($data)) {
                
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to get students above a certain grade",
                    "reason" => "Syntax error in json data"
                );
            }
            elseif(!array_key_exists("above_grade",$data)) {
                    
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to get students above a certain grade",
                    "reason" => "'above_grade' field is missing from json data"
                );
            }
            elseif(!Student::isValidGrade($data->above_grade)) {
                    
                $status = 400;
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to get students above a certain grade",
                    "reason" => "Invalid value for 'above_grade' field in json data. It must be a float number >=0 and <=20"
                );
            }
            else {
                
                $db = new Database();
                $conn = $db->getConnection();
                $query = "SELECT id, firstname, lastname, grade, birth_date FROM students WHERE grade > :above_grade";
                $stmt = $conn->prepare($query);
                
                $stmt->bindParam(":above_grade", $data->above_grade);
                $stmt->execute();
                
                $num = $stmt->rowCount();       
                $fetched_data = array();
                if($num>0) {
                                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        extract($row);
                        $student_item = array(
                            "id" => intval($id),
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "grade" => floatval($grade),
                            "birth_date" => $birth_date
                        );
                        array_push($fetched_data, $student_item);
                    }
                    $message = "Students found above the certain grade";          
                }
                else {
                    
                    $message = "No students found above the certain grade";
                }
                            
                $status = 200;    
                $result_arr=array(
                    "status" => true,
                    "status_code" => $status,
                    "message" => $message,
                    "above_grade" => floatval($data->above_grade),
                    "num_of_students" => $num,
                    "records" => $fetched_data
                );
            }
        }
        catch (Throwable $t) {
            
            $status = 500;
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to get students above a certain grade. Internal Server Error.",
                "reason" => $t->getMessage()
            );
        }

        if(isset($db)){
            $db->closeConnection();
        }
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function search($request, $response, $args) {
        
        try {
            
            $keyword = $args['keyword'];

            $db = new Database();
            $conn = $db->getConnection();     
            $query = "SELECT id, firstname, lastname, grade, birth_date FROM students WHERE id=:id_keyword OR firstname LIKE :firstname_keyword OR lastname LIKE :lastname_keyword OR grade=:grade_keyword OR birth_date=:birth_date_keyword";
            $stmt = $conn->prepare($query);
            
            $keyword=htmlspecialchars(strip_tags($keyword));
            
            $id_keyword = "{$keyword}";
            $firstname_keyword = "{$keyword}%";
            $lastname_keyword = "{$keyword}%";
            $grade_keyword = "{$keyword}";
            $birth_date_keyword = "{$keyword}";
            
            $stmt->bindParam(":id_keyword", $id_keyword);
            $stmt->bindParam(":firstname_keyword", $firstname_keyword);
            $stmt->bindParam(":lastname_keyword", $lastname_keyword);
            $stmt->bindParam(":grade_keyword", $grade_keyword);
            $stmt->bindParam(":birth_date_keyword", $birth_date_keyword);
            
            $stmt->execute();
            $num = $stmt->rowCount();
                        
            $fetched_data = array();
            if($num>0) {
                            
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $student_item = array(
                        "id" => intval($id),
                        "firstname" => $firstname,
                        "lastname" => $lastname,
                        "grade" => floatval($grade),
                        "birth_date" => $birth_date
                    );
                    array_push($fetched_data, $student_item);
                }
                
                $message = "Students found";          
            }
            else {
                $message = "No students found";
            }
            
            $status = 200;
            
            $result_arr=array(
                "status" => true,
                "status_code" => $status,
                "message" => $message,
                "keyword" => $keyword,
                "num_of_students" => $num,
                "records" => $fetched_data
            );
        }
        catch(Throwable $t) {
            
            $status = 500;
            
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to search in students. Internal Server Error.",
                "reason" => $t->getMessage()
            );
        }
        
        if(isset($db)) {
            
            $db->closeConnection();
        }
        
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    public function getPaging($request, $response, $args) {
        
        if($request->getBody()=="") {
            $records_per_page = 5;
        }  
        elseif(is_null($data = json_decode($request->getBody()))) {
                
            $status = 400;
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to get students",
                "reason" => "Syntax error in json data"
            );
        }
        elseif(!array_key_exists("items_per_page",$data)) {
                    
            $status = 400;
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to get students",
                "reason" => "'items_per_page' field is missing from json data"
            );
        }
        elseif(!Student::isValidNumOfPages($data->items_per_page)) {
                    
            $status = 400;
            $result_arr = array(
                "status" => false,
                "status_code" => $status,
                "message" => "Unable to get students",
                "reason" => "Invalid value for 'items_per_page' field in json data. It must be an integer > 0"
            );
        }
        
        if(!isset($status)) {
            
            if(!isset($records_per_page)) {
                $records_per_page = intval($data->items_per_page);
            }
            $home_url = "http://localhost/api/v1";
            $page_url = "{$home_url}/students/paging";
        
            try {
                $page = intval($args['page']);
                $db = new Database();
                $conn = $db->getConnection();
                
                if($page > 0){
                    
                    $from_record = ($records_per_page * $page) - $records_per_page;
                
                    $total_rows = Student::CountTotalRows($conn);
                    $total_pages = ceil($total_rows / $records_per_page);
                    
                    $query = "SELECT id, firstname, lastname, grade, birth_date FROM students ORDER BY id LIMIT ?,?";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(1, $from_record, PDO::PARAM_INT);
                    $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);
                    
                    $stmt->execute();
                    
                    $num = $stmt->rowCount();
                }
                else {
                    
                    $num = 0;
                }
                
                $fetched_data = array();
                
                if($num>0) {
                    // fetch() is faster than fetchAll()
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        extract($row); // this will make $row['name'] to just $name only
                        $student_item = array(
                            "id" => intval($id),
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "grade" => floatval($grade),
                            "birth_date" => $birth_date
                        );
                        array_push($fetched_data, $student_item);
                    }
                    $message = "Students found";
                    
                    $paging_data = array();
                    $paging_data["first"] = $page>1 ? "{$page_url}/1" : "";

                    // range of links to show
                    $range = 2;
             
                    // display links to 'range of pages' around 'current page'
                    $initial_num = $page - $range;
                    $condition_limit_num = ($page + $range)  + 1;
             
                    $paging_data['pages']=array();
                    $page_count=0;
                 
                    for($x=$initial_num; $x<$condition_limit_num; $x++){
                        // be sure '$x is greater than 0' AND 'less than or equal to the $total_pages'
                        if(($x > 0) && ($x <= $total_pages)){
                            $paging_data['pages'][$page_count]["page"]=$x;
                            $paging_data['pages'][$page_count]["url"]="{$page_url}/{$x}";
                            $paging_data['pages'][$page_count]["current_page"] = $x==$page ? "yes" : "no";
             
                            $page_count++;
                        }
                    }
         
                    // button for last page
                    $paging_data["last"] = $page<$total_pages ? "{$page_url}/{$total_pages}" : "";
                    
                    $status = 200;
                    
                    $result_arr=array(
                        "status" => true,
                        "status_code" => $status,
                        "message" => $message,
                        "num_of_students" => $num,
                        "records" => $fetched_data,
                        "items_per_page" => $records_per_page,
                        "paging" => $paging_data
                    );
                }
                else {
                    $message = "No students found";
                    
                    $status = 200;
                    
                    $result_arr=array(
                        "status" => true,
                        "status_code" => $status,
                        "message" => $message,
                        "num_of_students" => $num,
                        "records" => $fetched_data,
                        "items_per_page" => $records_per_page
                    );
                }
            }
            catch(Throwable $t) {
                
                $status = 500;
                
                $result_arr = array(
                    "status" => false,
                    "status_code" => $status,
                    "message" => "Unable to get students. Internal Server Error.",
                    "reason" => $t->getMessage()
                );
            }
        }
        
        if(isset($db)){
            $db->closeConnection();
        }
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json')->write(json_encode($result_arr));
    }
    
    private function CountTotalRows($conn) {
        
        $query = "SELECT COUNT(*) AS total_rows FROM students";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = $row['total_rows'];
        
        return $result;
    }
    
    private function isValidDate($date){
        
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    private function isValidGrade($grade){
        
        return ( is_float($grade) || is_int($grade) ) && $grade >= 0 && $grade <= 20;
    }
    
    private function isValidNumOfPages($num_of_pages){
        
        return is_int($num_of_pages) && $num_of_pages > 0;
    }
    
    private function isValidName($name){
        
        return ctype_alpha($name) && strlen($name)>0;
    }
}

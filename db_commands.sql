create database students_db;
use students_db;

create table students (

    id int unsigned auto_increment primary key,
    firstname varchar(50) not null,
    lastname varchar(100) not null,
    grade decimal(3,1) not null,
    birth_date date not null
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


DELIMITER $$
CREATE PROCEDURE check_students(IN grade DECIMAL(3,1))
BEGIN
    IF grade < 0  || grade > 20 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Check constraint on students.grade failed. The field must be >= 0';
    END IF;
    
    IF grade > 20 THEN
        SIGNAL SQLSTATE '45001'
        SET MESSAGE_TEXT = 'check constraint on students.grade failed. The field must be <= 20';
    END IF;
END$$
DELIMITER ;


DELIMITER $$
CREATE TRIGGER students_before_insert BEFORE INSERT ON students
FOR EACH ROW
BEGIN
    CALL check_students(new.grade);
END$$   
DELIMITER ;


DELIMITER $$
CREATE TRIGGER students_before_update BEFORE UPDATE ON students
FOR EACH ROW
BEGIN
    CALL check_students(new.grade);
END$$   
DELIMITER ;
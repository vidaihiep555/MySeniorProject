<?php
/**
 * Database configuration
 */
define('DB_USERNAME', 'root');
//define('DB_PASSWORD', 'jcvzvJLLxupZQzRs');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_NAME', 'bikeuber');

define('CUSTOMER_LOGIN_SUCCESSFULL', 100);
define('CUSTOMER_NOT_ACTIVATE', 101);


define('DRIVER_LOGIN_SUCCESSFULL', 102);
define('DRIVER_NOT_ACTIVATE', 103);


define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);

//Itinerary status
define('ITINERARY_STATUS_CREATED', 1);//luc tao hanh trinh
define('ITINERARY_STATUS_ACCEPTED', 2);//da duoc accept
define('ITINERARY_STATUS_ONGOING', 3);//on-going
define('ITINERARY_STATUS_FINISHED', 4);//hanh trinh da ket thuc

define('ITINERARY_CREATED_SUCCESSFULLY', 5);
define('ITINERARY_CREATE_FAILED', 6);

define('USER_ACTIVATED_SUCCESSFULLY', 7);
define('USER_ACTIVATE_FAILED', 8);

define('WRONG_PASSWORD', 13);
define('LOGIN_SUCCESSFULL', 14);

define('STAFF_CREATED_SUCCESSFULLY', 15);
define('STAFF_CREATE_FAILED', 16);
define('STAFF_ALREADY_EXISTED', 17);
define('STAFF_NOT_REGISTER', 18);

define('ROLE_ADMIN',0);
define('ROLE_STAFF',1);

define('USER_NOT_REGISTER', 0);
define('USER_NOT_ACTIVATE', 1);//moi vua dk
define('USER_ACTIVATED', 2); //da kich hoat tai khoan thong qua email
define('USER_UPDATED_PROFILE', 3); //cap nhat thong tin tai khoan
define('USER_ACCEPT_UPDATED_PROFILE', 4); //admin accepted thong tin tai khoan ==> duoc phep dang hanh trinh
define('USER_LOCKED', 5);

define('DRIVER_CREATED_SUCCESSFULLY', 19);
define('DRIVER_CREATE_FAILED', 20);
define('DRIVER_ALREADY_EXISTED', 21);

define('DRIVER_NOT_ACCEPT', 1); //cap nhat thong tin nhung chua duoc comfirm
define('DRIVER_ACCEPTED', 2); //da verify thanh driver

define('DRIVER_BUSY', 0);
define('DRIVER_NOT_BUSY', 1);

define('VEHICLE_NOT_ACCEPT', 1);
define('VEHICLE_ACCEPT', 2);

define('USER_CREATED_FEEDBACK_SUCCESSFULLY', 22);
define('USER_CREATE_FEEDBACK_FAILED', 23);

define('VEHICLE_CREATED_SUCCESSFULLY', 24);
define('VEHICLE_CREATE_FAILED', 25);
define('VEHICLE_ALREADY_EXISTED', 26);

define('RATING_CREATED_SUCCESSFULLY', 28);
define('RATING_CREATE_FAILED', 29);
?>

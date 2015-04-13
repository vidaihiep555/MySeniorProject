<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '../libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;
// Staff id from db - Global Variable
$staff_id = NULL;
//Restricted user field
$restricted_user_field = array('user_id', 'email', 'api_key', 'created_at', 'status');
$restricted_customer_field = array('customer_id', 'email', 'api_key', 'created_at', 'status');

/**
 * Adding Middle Layer to authenticate User every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticateUser(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        //if (!$db->isValidApiKey($api_key,"customer") || $db->isLockUser($api_key)) {
        if (!$db->isValidApiKey($api_key,"customer")) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied.";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getCustomerId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Adding Middle Layer to authenticate Staff every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticateStaff(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key,"staff")) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $staff_id;
            // get user primary key id
            $staff_id = $db->getStaffId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Customer Registration
 * url - /user
 * method - POST
 * params - email, password
 */
$app->post('/customer', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            $response = array();

            // reading post params
            $email = $app->request->post('email');
            $password = $app->request->post('password');

            // validating email address
            validateEmail($email);
            // validating password
            validatePassword($password);

            $db = new DbHandler();
            $res = $db->createCustomer($email, $password);

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $customer = $db->getCustomerByEmail($email);
                $activation_code = $customer["api_key"];

                $content_mail = "Chao ban,<br>
                                Vui long nhan vao duong link sau de kich hoat tai khoan:
                                <a href='http://192.168.10.74/WebApp/controller/register.php?active_key=". $activation_code.
                                "'>Kich hoat tai khoan</a>";

                sendMail($email, $content_mail);

                $response["error"] = false;
                $response["message"] = "Đăng kí thành công. Vui lòng kích hoạt tài khoản qua email bạn vừa đăng kí!";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Xin lỗi! email bạn đăng kí đã tồn tại.";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Xin lỗi! Có lỗi xảy ra trong quá trình đăng kí.";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * User activation
 * url - /user
 * method - GET
 * params - activation_code
 */
$app->get('/active/:activation_code', function($activation_code) {
            $response = array();

            $db = new DbHandler();
            $res = $db->activateUser($activation_code);

            if ($res == USER_ACTIVATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Bạn đã kích hoạt tài khoản thành công.";
            } else if ($res == USER_ACTIVATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Xin lỗi! Kích hoạt tài khoản thất bại.";
            } 

            // echo json response
            echoRespnse(200, $response);
        });

/**
 * User Login
 * url - /user
 * method - POST
 * params - email, password
 */
$app->post('/user/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();

            $res = $db->checkLogin($email, $password);
            // check for correct email and password
            if ($res == LOGIN_SUCCESSFULL) {
                // get the user by email
                $user = $db->getCustomerByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['apiKey'] = $user['api_key'];
                    $response['customer_status'] = $user['status'];

                    $user_id = $db->getCustomerId($user['api_key']);

                    //$driver_status = $db->getDriverByField($user_id, 'status');
                    //$response['driver_status'] = $driver_status;

                    //$response['driver'] = $db->isDriver($user_id);
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "Có lỗi xảy ra! Vui lòng thử lại.";
                }
            } elseif ($res == WRONG_PASSWORD || $res == USER_NOT_REGISTER) {
                $response['error'] = true;
                $response['message'] = "Sai email hoặc mật khẩu!";
            } elseif ($res == USER_NOT_ACTIVATE) {
                $response['error'] = true;
                $response['message'] = "Tài khoản chưa được kích hoạt. Vui lòng kích hoạt tài khoản!";
            } elseif($res == USER_LOCKED) {
                $response['error'] = true;
                $response['message'] = "Tài khoản của bạn đang bị khóa!";
            }
            else{
                $response['error'] = true;
                $response['message'] = "Có lỗi xảy ra trong quá trình đăng nhập!";
            }

            echoRespnse(200, $response);
        });

$app->get('/forgotpass/:email', function($email) {
            $response = array();

            $db = new DbHandler();

            if ($db->isUserExists($email)) {
                $res = $db->getCustomerByEmail($email);

                if (isset($res)) {
                    $content_mail = "Chao ban,<br>
                                Vui long nhan vao duong link sau de doi mat khau:
                                <a href='http://192.168.10.74/WebApp/forgotpass.php?api_key=". $res['api_key'].
                                "'>Doi mat khau</a>";

                    sendMail($email, $content_mail);

                    $response["error"] = false;
                    $response["message"] = "Một email vừa được gửi đến địa chỉ email bạn nhập. Vui lòng làm theo hướng dẫn để lấy lại mật khẩu";
                } else {
                    $response["error"] = true;
                    $response["message"] = "Xin lỗi! Có lỗi xảy ra, vui lòng thử lại sau.";
                } 
            } else {
                $response["error"] = true;
                $response["message"] = "Email bạn nhập không chính xác!";
            }
            // echo json response
            echoRespnse(200, $response);
        });

/**
 * Get user information
 * method GET
 * url /user
 */
$app->get('/user', 'authenticateUser', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getCustomerByID($user_id);

            if ($result != NULL) {
                $response['error'] = false;
                $response['email'] = $result['email'];
                $response['apiKey'] = $result['api_key'];
                $response['fullname'] = $result['fullname'];
                $response['phone'] = $result['phone'];
                $response['personalID'] = $result['personalID'];
                $response['customer_avatar'] = $result['customer_avatar'];
                $response['created_at'] = $result['created_at'];
                $response['status'] = $result['status'];
                echoRespnse(200, $response);
            } else {
                $response['error'] = true;
                $response['message'] = 'Đường dẫn bạn yêu cầu không tồn tại!';
                echoRespnse(404, $response);
            }
        });

$app->get('/user/:field', 'authenticateUser', function($field) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getUserByField($user_id, $field);

            if ($result != NULL || $field == 'locked') {
                $response["error"] = false;
                $response[$field] = $result;
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Đường dẫn bạn yêu cầu không tồn tại!";
                echoRespnse(404, $response);
            }
        });

/**
 * Updating user
 * method PUT
 * params task, status
 * url - /user
 */
$app->put('/user', 'authenticateUser', function() use($app) {
            // check for required params
            verifyRequiredParams(array('fullname', 'phone', 'personalID', 'personalID_img', 'link_avatar'));

            global $user_id;            
            $fullname = $app->request->put('fullname');
            $phone = $app->request->put('phone');
            $personalID = $app->request->put('personalID');
            $personalID_img = $app->request->put('personalID_img');
            $link_avatar = $app->request->put('link_avatar');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateUser($user_id, $fullname, $phone, $personalID, $personalID_img, $link_avatar);
            if ($result) {
                // task updated successfully
                $response['error'] = false;
                $response['message'] = "Cập nhật thông tin thành công!";
            } else {
                // task failed to update
                $response['error'] = true;
                $response['message'] = "Cập nhật thông tin thất bại. Vui lòng thử lại!";
            }
            echoRespnse(200, $response);
        });

/**
 * Update user information
 * method PUT
 * url /user
 */
$app->put('/user/:field', 'authenticateUser', function($field) use($app) {
            global $restricted_user_field;
            if (!in_array($field, $restricted_user_field)) {
                // check for required params
                verifyRequiredParams(array('value'));
                global $user_id;
                $value = $app->request->put('value');

                $response = array();
                $db = new DbHandler();

                if ($field == 'password') {
                    validatePassword($value);

                    $result = $db->changePassword($user_id, $value);
                } else {
                    // fetch user
                    $result = $db->updateUserField($user_id, $field, $value);
                }

                if ($result) {
                    // user updated successfully
                    $response["error"] = false;
                    $response["message"] = "Cập nhật thông tin thành công!";
                } else {
                    // user failed to update
                    $response["error"] = true;
                    $response["message"] = "Cập nhật thông tin thất bại. Vui lòng thử lại!";
                }
            } else {
                $response["error"] = true;
                $response["message"] = "Cập nhật thông tin thất bại. Vui lòng thử lại!";
            }
            
            echoRespnse(200, $response);
        });

/**
 * Deleting user.
 * method DELETE
 * url /user
 */
$app->delete('/user', 'authenticateUser', function() {
            global $user_id;

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteUser($user_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Xóa người dùng thành công!";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Xóa người dùng thất bại. Vui lòng thử lại!";
            }
            echoRespnse(200, $response);
        });

/**
 * Driver Registration
 * url - /driver
 * method - POST
 * params - driver
 */
$app->post('/driver', 'authenticateUser', function() use ($app) {
            verifyRequiredParams(array('driver_license', 'driver_license_img'));
            global $user_id;
            $response = array();

            // reading post params
            $driver_license = $app->request->post('driver_license');
            $driver_license_img = $app->request->post('driver_license_img');

            $db = new DbHandler();
            $res = $db->createDriver($user_id, $driver_license, $driver_license_img);

            if ($res == DRIVER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Đăng kí thành công!";
            } else if ($res == DRIVER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Bạn đã đăng kí làm lái xe!";
            } else if ($res == DRIVER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Xin lỗi! Có lỗi xảy ra trong quá trình đăng kí.";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * Get driver information
 * method GET
 * url /driver
 */
$app->get('/driver', 'authenticateUser', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getDriverByUserID($user_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response['driver_license'] = $result['driver_license'];
                $response['driver_license_img'] = $result['driver_license_img'];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Đường dẫn bạn yêu cầu không tồn tại!";
                echoRespnse(404, $response);
            }
        });

/**
 * Get user information
 * method GET
 * url /user
 */
$app->get('/driver/:field', 'authenticateUser', function($field) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getDriverByField($user_id, $field);

            if ($result != NULL) {
                $response["error"] = false;
                $response[$field] = $result;
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Đường dẫn bạn yêu cầu không tồn tại!";
                echoRespnse(200, $response);
            }
        });

/**
 * Updating user
 * method PUT
 * params task, status
 * url - /user
 */
$app->put('/driver', 'authenticateUser', function() use($app) {
            // check for required params
            verifyRequiredParams(array('driver_license', 'driver_license_img'));

            global $user_id;            
            $driver_license = $app->request->put('driver_license');
            $driver_license_img = $app->request->put('driver_license_img');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateDriver($user_id, $driver_license, $driver_license_img);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Cập nhật thông tin thành công!";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Cập nhật thông tin thất bại. Vui lòng thử lại!";
            }
            echoRespnse(200, $response);
        });

/**
 * Update user information
 * method PUT
 * url /user
 */
$app->put('/driver/:field', 'authenticateUser', function($field) use($app) {
            // check for required params
            verifyRequiredParams(array('value'));
            global $user_id;
            $value = $app->request->put('value');

            $response = array();
            $db = new DbHandler();

            // fetch user
            $result = $db->updateDriverField($user_id, $field, $value);

            if ($result) {
                // user updated successfully
                $response["error"] = false;
                $response["message"] = "Cập nhật thông tin thành công!";
            } else {
                // user failed to update
                $response["error"] = true;
                $response["message"] = "Cập nhật thông tin thất bại. Vui lòng thử lại!";
            }
            
            echoRespnse(200, $response);
        });

/**
 * Deleting user.
 * method DELETE
 * url /user
 */
$app->delete('/driver', 'authenticateUser', function() {
            global $user_id;

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteDriver($user_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Xóa tài xế thành công!";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Xóa tài xế thất bại. Vui lòng thử lại!";
            }
            echoRespnse(200, $response);
        });



/**
 * Listing single task of particual user
 * method GET
 * url /tasks/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/itinerary/:id', function($itinerary_id) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getItinerary($itinerary_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response["itinerary_id"] = $result["itinerary_id"];
                $response["driver_id"] = $result["driver_id"];
                $response["customer_id"] = $result["customer_id"];
                $response["start_address"] = $result["start_address"];
                $response["start_address_lat"] = $result["start_address_lat"];
                $response["start_address_long"] = $result["start_address_long"];
                $response["end_address"] = $result["end_address"];
                $response["end_address_lat"] = $result["end_address_lat"];
                $response["end_address_long"] = $result["end_address_long"];
                $response["leave_date"] = $result["leave_date"];
                $response["duration"] = $result["duration"];
                $response["distance"] = $result["distance"];
                $response["cost"] = $result["cost"];
                $response["description"] = $result["description"];
                $response["status"] = $result["status"];
                $response["created_at"] = $result["created_at"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });

/**
 * Listing all itineraries of particual user
 * method GET
 * url /itineraries          
 */
$app->get('/itineraries', 'authenticateUser', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAllItinerariesWithDriverInfo();

            $response["error"] = false;
            $response["itineraries"] = array();

            // looping through result and preparing tasks array
            while ($itinerary = $result->fetch_assoc()) {
                $tmp = array();

                //itinerary info
                $tmp["itinerary_id"] = $itinerary["itinerary_id"];
                $tmp["driver_id"] = $itinerary["driver_id"];
                $tmp["customer_id"] = $itinerary["customer_id"];
                $tmp["start_address"] = $itinerary["start_address"];
                $tmp["start_address_lat"] = $itinerary["start_address_lat"];
                $tmp["start_address_long"] = $itinerary["start_address_long"];
                $tmp["end_address"] = $itinerary["end_address"];
                $tmp["end_address_lat"] = $itinerary["end_address_lat"];
                $tmp["end_address_long"] = $itinerary["end_address_long"];
                $tmp["leave_date"] = $itinerary["leave_date"];
                $tmp["duration"] = $itinerary["duration"];
                $tmp["distance"] = $itinerary["distance"];
                $tmp["cost"] = $itinerary["cost"];
                $tmp["description"] = $itinerary["description"];
                $tmp["status"] = $itinerary["status"];
                $tmp["created_at"] = $itinerary["created_at"];

                
                //user info
                $tmp["user_id"] = $itinerary["user_id"];
                $tmp["email"] = $itinerary["email"];
                $tmp["fullname"] = $itinerary["fullname"];
                $tmp["phone"] = $itinerary["phone"];
                $tmp["personalID"] = $itinerary["personalID"];
                $tmp["customer_avatar"] = $itinerary["customer_avatar"];

                //rating
                $tmp["average_rating"] = $db->getAverageRatingofDriver($itinerary["user_id"]);
                array_push($response["itineraries"], $tmp);
            }

            //print_r($response);

            //echo $response;
            echoRespnse(200, $response);

        });

/**
 * Listing all itineraries of driver
 * method GET
 * url /itineraries          
 */
$app->get('/itineraries/driver/:order', 'authenticateUser', function($order) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getDriverItineraries($user_id, $order);

            $response["error"] = false;
            $response["itineraries"] = array();

            //print_r($result);

            // looping through result and preparing tasks array
            while ($itinerary = $result->fetch_assoc()) {
                $tmp = array();
                //itinerary info
                $tmp["itinerary_id"] = $itinerary["itinerary_id"];
                $tmp["driver_id"] = $itinerary["driver_id"];
                $tmp["customer_id"] = $itinerary["customer_id"];
                $tmp["start_address"] = $itinerary["start_address"];
                $tmp["start_address_lat"] = $itinerary["start_address_lat"];
                $tmp["start_address_long"] = $itinerary["start_address_long"];
                $tmp["end_address"] = $itinerary["end_address"];
                $tmp["end_address_lat"] = $itinerary["end_address_lat"];
                $tmp["end_address_long"] = $itinerary["end_address_long"];
                $tmp["leave_date"] = $itinerary["leave_date"];
                $tmp["duration"] = $itinerary["duration"];
                $tmp["distance"] = $itinerary["distance"];
                $tmp["cost"] = $itinerary["cost"];
                $tmp["description"] = $itinerary["description"];
                $tmp["status"] = $itinerary["itinerary_status"];
                $tmp["created_at"] = $itinerary["created_at"];

                //driver info
                $tmp["driver_license"] = $itinerary["driver_license"];
                $tmp["driver_license_img"] = $itinerary["driver_license_img"];
                
                //user info
                $tmp["user_id"] = $itinerary["user_id"];
                $tmp["email"] = $itinerary["email"];
                $tmp["fullname"] = $itinerary["fullname"];
                $tmp["phone"] = $itinerary["phone"];
                $tmp["personalID"] = $itinerary["personalID"];
                $tmp["link_avatar"] = $itinerary["link_avatar"];
                array_push($response["itineraries"], $tmp);
                //print_r($itinerary);
                //echoRespnse(200, $itinerary);
            }
            

            //print_r($response);

            echoRespnse(200, $response);
        });
/**
 * Listing all itineraries of customer
 * method GET
 * url /itineraries          
 */
$app->get('/itineraries/customer/:order', 'authenticateUser', function($order) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getCustomerItineraries($user_id, $order);

            $response["error"] = false;
            $response["itineraries"] = array();

            // looping through result and preparing tasks array
            while ($itinerary = $result->fetch_assoc()) {
                $tmp = array();
                //itinerary info
                $tmp["itinerary_id"] = $itinerary["itinerary_id"];
                $tmp["driver_id"] = $itinerary["driver_id"];
                $tmp["customer_id"] = $itinerary["customer_id"];
                $tmp["start_address"] = $itinerary["start_address"];
                $tmp["start_address_lat"] = $itinerary["start_address_lat"];
                $tmp["start_address_long"] = $itinerary["start_address_long"];
                $tmp["end_address"] = $itinerary["end_address"];
                $tmp["end_address_lat"] = $itinerary["end_address_lat"];
                $tmp["end_address_long"] = $itinerary["end_address_long"];
                $tmp["leave_date"] = $itinerary["leave_date"];
                $tmp["duration"] = $itinerary["duration"];
                $tmp["distance"] = $itinerary["distance"];
                $tmp["cost"] = $itinerary["cost"];
                $tmp["description"] = $itinerary["description"];
                $tmp["status"] = $itinerary["status"];
                $tmp["created_at"] = $itinerary["created_at"];

                //driver info
                $tmp["driver_license"] = $itinerary["driver_license"];
                $tmp["driver_license_img"] = $itinerary["driver_license_img"];
                
                //user info
                $tmp["user_id"] = $itinerary["user_id"];
                $tmp["email"] = $itinerary["email"];
                $tmp["fullname"] = $itinerary["fullname"];
                $tmp["phone"] = $itinerary["phone"];
                $tmp["personalID"] = $itinerary["personalID"];
                $tmp["link_avatar"] = $itinerary["link_avatar"];
                array_push($response["itineraries"], $tmp);
            }

            print_r($response);
            echoRespnse(200, $response);
        });

//not finished yet: updated when accepted
/**
 * Updating existing itinerary
 * method PUT
 * params task, status
 * url - /itinerary/:id
 */
$app->put('/itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));
            global $user_id;
            $itinerary_fields = array();           

            $request_params = array();
            $request_params = $_REQUEST;
            // Handling PUT request params
            if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
                $app = \Slim\Slim::getInstance();
                parse_str($app->request()->getBody(), $request_params);
            }

            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateItinerary2($request_params, $itinerary_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Itinerary updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating when itinerary is accepted by customer
 * method PUT
 * params 
 * url - /accept_itinerary/:id
 */
$app->put('/customer_accept_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;
            //$itinerary_fields = array();           

            //$request_params = array();
            //$request_params = $_REQUEST;
            // Handling PUT request params
            /*if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
                $app = \Slim\Slim::getInstance();
                parse_str($app->request()->getBody(), $request_params);
            }*/

            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateCustomerAcceptedItinerary($itinerary_id, $user_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Customer accepted itinerary successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to accepted by customer. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating when itinerary is rejected by customer
 * method PUT
 * params 
 * url - /accept_itinerary/:id
 */
$app->put('/customer_reject_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;
            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateCustomerRejectedItinerary($itinerary_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Customer rejected itinerary successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to rejected by customer. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating when itinerary is accepted by driver
 * method PUT
 * params 
 * url - /accept_itinerary/:id
 */
$app->put('/driver_accept_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;
            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateDriverAcceptedItinerary($itinerary_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Driver accepted itinerary successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to accepted by driver. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating when itinerary is rejected by driver
 * method PUT
 * params 
 * url - /accept_itinerary/:id
 */
$app->put('/driver_reject_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;

            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateDrivereRectedItinerary($itinerary_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Driver rejected successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to rejected by driver. Please try again!";
            }
            echoRespnse(200, $response);
        });
//not finished 
//not finished yet: bi phat sau khi delete khi da duoc accepted
/**
 * Deleting itinerary. Users can delete only their itineraries
 * method DELETE
 * url /itinerary
 */
$app->delete('/itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            global $user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteItinerary($itinerary_id);
            if ($result) {
                // itinerary deleted successfully
                $response["error"] = false;
                $response["message"] = "Itinerary deleted succesfully";
            } else {
                // itinerary failed to delete
                $response["error"] = true;
                $response["message"] = "Itinerary failed to delete. Please try again!";
            }
            echoRespnse(200, $response);
        });



/**
 * Listing all itineraries of particual user
 * method GET
 * url /itineraries          
 */
$app->get('/drivers', 'authenticateUser', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAllDrivers();

            $response["error"] = false;
            $response["drivers"] = array();

            // looping through result and preparing tasks array
            while ($driver = $result->fetch_assoc()) {
                $tmp = array();

                //itinerary info
                $tmp["driver_id"] = $result["driver_id"];
                $tmp['email'] = $result['email'];
                $tmp['fullname'] = $result['fullname'];
                $tmp['phone'] = $result['phone'];
                $tmp['driver_lat'] = $result['driver_lat'];
                $tmp['driver_long'] = $result['driver_long'];
                $tmp['created_at'] = $result['created_at'];
                $tmp['status'] = $result['status'];
                $tmp["driver_avatar"] = $result["driver_avatar"];
                //rating
                $driver_id = $tmp["driver_id"];
                $tmp["average_rating"] = $db->getAverageRatingofDriver($driver_id);
                array_push($response["drivers"], $tmp);
            }

            //print_r($response);

            //echo $response;
            echoRespnse(200, $response);

        });








$app->post('/feedback', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'name', 'content'));

            $response = array();

            // reading post params
            $email = $app->request->post('email');
            $name = $app->request->post('name');
            $content = $app->request->post('content');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createFeedback($email, $name, $content);

            if ($res == USER_CREATED_FEEDBACK_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Cám ơn bạn đã gửi góp ý!";
            } else if ($res == USER_CREATE_FEEDBACK_FAILED) {
                $response["error"] = true;
                $response["message"] = "Xin lỗi! Có lỗi xảy ra trong quá trình gửi góp ý.";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Bạn chưa nhập ' . substr($error_fields, 0, -2) . ' !';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email không hợp lệ!';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Validating password
 */
function validatePassword($password) {
    $app = \Slim\Slim::getInstance();

    if ((strlen($password) < 6) || (strlen($password) > 12)) {

        $response["error"] = true;
        $response["message"] = 'Độ dài mật khẩu phải nằm trong khoảng 6 đến 12 kí tự!';
        echoRespnse(200, $response);
        $app->stop();
    } 

    if (preg_match('#[\\s]#', $password)) {
        $response["error"] = true;
        $response["message"] = 'Mật khẩu không được có khoảng trống!';
        echoRespnse(200, $response);
        $app->stop();
    } 
}

/**
 * Send activation email
 */
function sendMail($receiver_mail, $content) {
    require_once '../libs/PHPMailer/class.phpmailer.php';

    $mail               = new PHPMailer();
    $body               = $content;
    $body               = eregi_replace("[\]",'',$body);
    $mail->IsSMTP();                            // telling the class to use SMTP

    $mail->SMTPAuth     = true;                  // enable SMTP authentication
    $mail->SMTPSecure   = "tls";                 // sets the prefix to the servier
    $mail->Host         = "smtp.gmail.com";      // sets GMAIL as the SMTP server
    $mail->Port         = 587;                   // set the SMTP port for the GMAIL server
    $mail->Username     = "thanhbkdn92@gmail.com";  // GMAIL username
    $mail->Password     = "thanhkdt123@";            // GMAIL password

    $mail->SetFrom('thanhbkdn92@gmail.com', 'Ride Sharing Verification Team'); //Sender

    $mail->Subject    = "Activate account"; //Subject

    $mail->MsgHTML($body);

    $address = $receiver_mail; //Receiver
    $mail->AddAddress($address, "Guest"); //Send to?

    // $mail->AddAttachment("dinhkem/02.jpg");      // Attach
    // $mail->AddAttachment("dinhkem/200_100.jpg"); // Attach

    if(!$mail->Send()) {
      return "Lỗi gửi mail: " . $mail->ErrorInfo;
    } else {
      return "Mail đã được gửi!";
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>
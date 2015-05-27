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
        if ($db->isValidApiKey($api_key,"customer")) {

            global $user_id;
            // get user primary key id
            $user_id = $db->getCustomerId($api_key);
           
        } else {
            if($db->isValidApiKey($api_key,"driver")){
                global $user_id;
                // get user primary key id
                $user_id = $db->getDriverId($api_key);
            } else {
                 // api key is not present in users table
                $response["error"] = true;
                $response["message"] = "Access Denied.";
                echoRespnse(401, $response);
                $app->stop();
            }
            
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
 * Adding Middle Layer to authenticate User every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticateDriver(\Slim\Route $route) {
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
        if (!$db->isValidApiKey($api_key,"driver")) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied.";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getDriverId($api_key);
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
 * Adding Middle Layer to authenticate User every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticateCustomer(\Slim\Route $route) {
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

/////////////////////////////////////////////////////////////////////////////////

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
            if ($res == CUSTOMER_LOGIN_SUCCESSFULL) {
                // get the user by email
                $user = $db->getCustomerByEmail($email);

                if ($user != NULL) {
                    //echo "hể";
                    $response["error"] = false;
                    $response['apiKey'] = $user['api_key'];
                    $response['user_id'] = $user['customer_id'];
                    $response['customer_status'] = $user['status'];
                    $response['isDriver'] = false;

                    //$user_id = $db->getCustomerId($user['api_key']);

                    //$driver_status = $db->getDriverByField($user_id, 'status');
                    //$response['driver_status'] = $driver_status;

                    //$response['driver'] = $db->isDriver($user_id);
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "There is error. Please try again!";
                }
            } elseif ($res == DRIVER_LOGIN_SUCCESSFULL){
                $driver = $db->getDriverByEmail($email);

                if($driver != NULL){
                    $response["error"] = false;
                    $response['apiKey'] = $driver['api_key'];
                    $response['user_id'] = $driver['driver_id'];
                    $response['driver_status'] = $driver['status'];
                    $response['isDriver'] = true;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "There is error. Please try again";
                }
            } elseif ($res == WRONG_PASSWORD || $res == USER_NOT_REGISTER) {
                $response['error'] = true;
                $response['message'] = "Wrong email or password";
            } elseif ($res == DRIVER_NOT_ACTIVATE) {
                $response['error'] = true;
                $response['message'] = "Please active your account!";
            }  elseif ($res == CUSTOMER_NOT_ACTIVATE) {
                $response['error'] = true;
                $response['message'] = "Please active your account!";
            } else{
                $response['error'] = true;
                $response['message'] = "There is error. Please try again!";
            }

            echoRespnse(200, $response);
        });

///////////////////////////////////////// CUSTOMER ///////////////////////////////////////////////////

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

                $content_mail = "Hi,<br>
                                Please click on the link below to active your account:
                                <a href='http://192.168.10.74/WebApp/controller/register.php?active_key=". $activation_code.
                                "'>Acctive account</a>";

                sendMail($email, $content_mail);

                $response["error"] = false;
                $response["message"] = "Register success. Please activate your account via email!";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry! Your email registration is existing.";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Sorry! There are some errors.";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * Customer activation
 * url - /user
 * method - GET
 * params - activation_code
 */
$app->get('/active/:activation_code', function($activation_code) {
            $response = array();

            $db = new DbHandler();
            $res = $db->activateCustomer($activation_code);

            if ($res == USER_ACTIVATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Activate your account is successful!";
            } else if ($res == USER_ACTIVATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Activate your account is missing!";
            } 

            // echo json response
            echoRespnse(200, $response);
        });



$app->get('/forgotpass/:email', function($email) {
            $response = array();

            $db = new DbHandler();

            if ($db->isUserExists($email)) {
                $res = $db->getCustomerByEmail($email);

                if (isset($res)) {
                    $content_mail = $lang['FORGOTPASS_MSG'] = "Hi there,<br>
                    Please click on the link below to reset your password:
                                <a href='http://192.168.10.74/WebApp/forgotpass.php?api_key=". $res['api_key'].
                                "'>Reset password/a>";

                    sendMail($email, $content_mail);

                    $response["error"] = false;
                    $response["message"] = "An alert email is sent to you. Please check and do something following the guide.";
                } else {
                    $response["error"] = true;
                    $response["message"] = "Sorry! Have some mistakes. Please try again!";
                } 
            } else {
                $response["error"] = true;
                $response["message"] = "Your email address is not exactly!";
            }
            // echo json response
            echoRespnse(200, $response);
        });

/**
 * Get user information
 * method GET
 * url /user
 */
$app->get('/customer', 'authenticateUser', function() {
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
                $response['message'] = 'The link you request is not existing!.';
                echoRespnse(404, $response);
            }
        });


/**
 * Updating user
 * method PUT
 * params task, status
 * url - /user
 */
$app->put('/customer', 'authenticateCustomer', function() use($app) {
            // check for required params
            verifyRequiredParams(array('fullname', 'phone', 'personalID', 'customer_avatar'));

            global $user_id;            
            $fullname = $app->request->put('fullname');
            $phone = $app->request->put('phone');
            $personalID = $app->request->put('personalID');
            $customer_avatar = $app->request->put('customer_avatar');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateCustomer($user_id, $fullname, $phone, $personalID, $customer_avatar);
            if ($result) {
                // task updated successfully
                $response['error'] = false;
                $response['message'] = "Your update is successful!.";
            } else {
                // task failed to update
                $response['error'] = true;
                $response['message'] = "Your update is not successful! Please try again.";
            }
            echoRespnse(200, $response);
        });



/**
 * Deleting user.
 * method DELETE
 * url /user
 */
$app->delete('/customer', 'authenticateUser', function() {
            global $user_id;

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteCustomer($user_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Delete user is successful!.";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Delete user is failed!.";
            }
            echoRespnse(200, $response);
        });


/////////////////////////////////////// DRIVER ////////////////////////////////////////////////////////////

/**
 * Driver Registration
 * url - /driver
 * method - POST
 * params - driver
 */
/*$app->post('/driver', function() use ($app) {
            verifyRequiredParams(array('driver_license', 'driver_license_img'));
            $response = array();

            // reading post params
            $driver_license = $app->request->post('driver_license');
            $driver_license_img = $app->request->post('driver_license_img');

            $db = new DbHandler();
            $res = $db->createDriver($driver_license, $driver_license_img);

            if ($res == DRIVER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Registration is successful!.";
            } else if ($res == DRIVER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Sorry! Registration is missing.";
            }
            // echo json response
            echoRespnse(201, $response);
        });*/

/**
 * Driver Registration
 * url - /user
 * method - POST
 * params - email, password
 */
$app->post('/driver', function() use ($app) {
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
            $res = $db->createDriver($email, $password);

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $customer = $db->getCustomerByEmail($email);
                $activation_code = $customer["api_key"];

                $content_mail = "Hi,<br>
                                Please click on the link below to active your account:
                                <a href='http://192.168.10.74/WebApp/controller/register.php?active_key=". $activation_code.
                                "'>Acctive account</a>";

                sendMail($email, $content_mail);

                $response["error"] = false;
                $response["message"] = "Register success. Please activate your account via email!";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry! Your email registration is existing.";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Sorry! There are some errors.";
            }
            // echo json response
            echoRespnse(201, $response);
        });
/**
 * Get driver information
 * method GET
 * url /driver
 */
$app->get('/driver', 'authenticateDriver', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();
            // fetch task
            $driver = $db->getDriverByID($user_id);

            if ($driver != NULL) {
                $response["error"] = false;
                $response["driver_id"] = $driver["driver_id"];
                $response['email'] = $driver['email'];
                $response['fullname'] = $driver['fullname'];
                $response['phone'] = $driver['phone'];
                $response['driver_lat'] = $driver['driver_lat'];
                $response['driver_long'] = $driver['driver_long'];
                $response['created_at'] = $driver['created_at'];
                $response['status'] = $driver['status'];
                $response['busy_status'] = $driver['busy_status'];
                $response['personalID'] = $driver['personalID'];
                $response['personalID_img'] = $driver['personalID_img'];
                $response["driver_avatar"] = $driver["driver_avatar"];
                $response['driver_license'] = $driver['driver_license'];
                $response['driver_license_img'] = $driver['driver_license_img'];
                //rating
                $driver_id = $response["driver_id"];
                $response["average_rating"] = $db->getAverageRatingofDriver($driver_id);
                //$response['driver_license'] = $result['driver_license'];
                //$response['driver_license_img'] = $result['driver_license_img'];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!.";
                echoRespnse(404, $response);
            }
        });


$app->get('/customergetdriver/:driver_id', 'authenticateDriver', function($driver_id) {
            //global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $driver = $db->getDriverByID($driver_id);

            if ($driver != NULL) {
                $response["error"] = false;
                $response["driver_id"] = $driver["driver_id"];
                $response['email'] = $driver['email'];
                $response['fullname'] = $driver['fullname'];
                $response['phone'] = $driver['phone'];
                $response['driver_lat'] = $driver['driver_lat'];
                $response['driver_long'] = $driver['driver_long'];
                $response['created_at'] = $driver['created_at'];
                $response['status'] = $driver['status'];
                $response['busy_status'] = $driver['busy_status'];
                $response['personalID'] = $driver['personalID'];
                $response['personalID_img'] = $driver['personalID_img'];
                $response["driver_avatar"] = $driver["driver_avatar"];
                $response['driver_license'] = $driver['driver_license'];
                $response['driver_license_img'] = $driver['driver_license_img'];
                //rating
                //$driver_id = $response["driver_id"];
                $response["average_rating"] = $db->getAverageRatingofDriver($driver_id);
                //$response['driver_license'] = $result['driver_license'];
                //$response['driver_license_img'] = $result['driver_license_img'];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!.";
                echoRespnse(404, $response);
            }
        });


/**
 * Listing all itineraries of particual user
 * method GET
 * url /itineraries          
 */
$app->get('/drivers/:lat/:long', 'authenticateUser', function($lat, $long) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAllDriversTopTen($lat, $long);

            $response["error"] = false;
            $response["drivers"] = array();

            // looping through result and preparing tasks array
            while ($driver = $result->fetch_assoc()) {
                $tmp = array();

                //itinerary info
                $tmp["driver_id"] = $driver["driver_id"];
                $tmp['email'] = $driver['email'];
                $tmp['fullname'] = $driver['fullname'];
                $tmp['phone'] = $driver['phone'];
                $tmp['driver_lat'] = $driver['driver_lat'];
                $tmp['driver_long'] = $driver['driver_long'];
                $tmp['created_at'] = $driver['created_at'];
                $tmp['status'] = $driver['status'];
                $tmp['busy_status'] = $driver['busy_status'];
                $tmp['personalID'] = $driver['personalID'];
                $tmp['personalID_img'] = $driver['personalID_img'];
                $tmp["driver_avatar"] = $driver["driver_avatar"];
                $tmp['driver_license'] = $driver['driver_license'];
                $tmp['driver_license_img'] = $driver['driver_license_img'];
                //rating
                $driver_id = $tmp["driver_id"];
                $tmp["average_rating"] = $db->getAverageRatingofDriver($driver_id);
                array_push($response["drivers"], $tmp);
            }

            //print_r($response);

            //echo $response;
            echoRespnse(200, $response);

        });

/**
 * Updating user
 * method PUT
 * params task, status
 * url - /user
 */
/*$app->put('/driver', 'authenticateDriver', function() use($app) {
            // check for required params
            verifyRequiredParams(array('driver_license', 'driver_license_img'));

            global $user_id;
            $fullname = $app->request->put('fullname');
            $phone = $app->request->put('phone');
            $personalID = $app->request->put('personalID');
            $customer_avatar = $app->request->put('customer_avatar');
            $fullname = $app->request->put('driver_license');            
            $driver_license = $app->request->put('driver_license');
            $driver_license_img = $app->request->put('driver_license_img');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateDriver($user_id, $driver_license, $driver_license_img);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Your update is successful!";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Your update is not successful! Please try again.";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating user
 * method PUT
 * params task, status
 * url - /user
 */
/*$app->put('/driverbusy', 'authenticateDriver', function() use($app) {
            // check for required params
            verifyRequiredParams(array('busy_status'));

            global $user_id;            
            $busy_status = $app->request->put('busy_status');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateDriverBusyStatus($user_id, $busy_status);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Your update is successful!";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Your update is not successful! Please try again.";
            }
            echoRespnse(200, $response);
        });

$app->put('/driverbusylatlong', 'authenticateDriver', function() use($app) {
            // check for required params
            verifyRequiredParams(array('busy_status'));

            global $user_id;            
            $busy_status = $app->request->put('busy_status');
            $driver_lat = $app->request->put('driver_lat');
            $driver_long = $app->request->put('driver_long');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateDriverBusyStatusLatLong($user_id, $busy_status, $driver_lat, $driver_long);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Your update is successful!";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Your update is not successful! Please try again.";
            }
            echoRespnse(200, $response);
        });*/



$app->put('/driver/', 'authenticateDriver', function() use($app) {
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
            $result = $db->updateDriver2($request_params, $user_id);
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
 * Deleting user.
 * method DELETE
 * url /user
 */
$app->delete('/driver', 'authenticateDriver', function() {
            global $user_id;

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteDriver($user_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Successfully Deleted Driver";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Delete driver is failure! Please try again!";
            }
            echoRespnse(200, $response);
        });


/////////////////////////////////// ITINERARY /////////////////////////////////////////////////////////



$app->post('/itinerary', 'authenticateCustomer', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('start_address','start_address_lat','start_address_long','end_address',
                'end_address_lat','end_address_long','time_start', 'distance', 'description'));

            $response = array();
            
            $start_address = $app->request->post('start_address');
            $start_address_lat = $app->request->post('start_address_lat');
            $start_address_long = $app->request->post('start_address_long');
            $end_address = $app->request->post('end_address');
            $end_address_lat = $app->request->post('end_address_lat');
            $end_address_long = $app->request->post('end_address_long');
            $time_start = $app->request->post('time_start');
            $description = $app->request->post('description');
            $distance = $app->request->post('distance');

            //echo $start_address;

            global $user_id;
            $db = new DbHandler();

            // creating new itinerary
            $itinerary_id = $db->createItinerary($user_id, $start_address, $start_address_lat,$start_address_long,
             $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance);

            if ($itinerary_id != NULL) {
                $response["error"] = false;
                $response["message"] = "Itinerary created successfully";
                $response["itinerary_id"] = $itinerary_id;
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Failed to create itinerary. Please try again";
                echoRespnse(200, $response);
            }            
        });


$app->post('/zzz', 'authenticateCustomer', function() {
            global $user_id;
            verifyRequiredParams(array('start_address','start_address_lat','start_address_long','end_address',
                'end_address_lat','end_address_long','time_start', 'distance', 'description'));

            $response = array();
            
            $start_address = $app->request->post('start_address');
            $start_address_lat = $app->request->post('start_address_lat');
            $start_address_long = $app->request->post('start_address_long');
            $end_address = $app->request->post('end_address');
            $end_address_lat = $app->request->post('end_address_lat');
            $end_address_long = $app->request->post('end_address_long');
            $time_start = $app->request->post('time_start');
            $description = $app->request->post('description');
            $distance = $app->request->post('distance');

            $day = $app->request->post('day');
            $month = $app->request->post('month');
            $year = $app->request->post('year');
            $hour = $app->request->post('hour');

            $db = new DbHandler();
            // fetching all user tasks
            $result = $db->findSuitableDriver();

            //$response["error"] = false;
            $drivers = array();

            // looping through result and preparing tasks array
            while ($x = $result->fetch_assoc()) {
                $tmp = array();

                //itinerary info
                $tmp["driver_id"] = $x["driver_id"];
                $tmp["day"] = $x["day"];
                $tmp["month"] = $x["month"];
                $tmp["year"] = $x["year"];
                
                array_push($drivers, $tmp);
            }
            //$check;
            $nochooseid = NULL;
            $chooseid = NULL;
            //$id = -1;
            foreach($drivers as $driver){
                if(isset($nochooseid) && $nochooseid == $driver['driver_id']){
                    continue;
                }

                if(isset($chooseid) && $chooseid != $driver['driver_id'] && $nochooseid!= $chooseid){
                    break;
                }

                if($day==$driver['day'] && $month==$driver['month'] && $year==$driver['year']){//driver ko thoa man ban ron vao dung thoi diem do
                    $nochooseid = $driver['driver_id'];
                } else {//chuyen sang ke tiep
                    $chooseid = $driver['driver_id'];
                }
            }

            if(isset($chooseid)){
                if(isset($nochooseid)){
                    if($nochooseid!= $chooseid){
                        //create itinerary

                        $itinerary = $db->createItinerary($user_id, $chooseid, $start_address, $start_address_lat,$start_address_long,
                            $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance);

                        //create busytime for driver
                        $busytime = $db->createBusytime($driver_id, $day, $month, $year, $from_hour, $to_hour)


                        //return driver_id and itinerary_id to customer

                        if ($itinerary != NULL) {
                            $response["error"] = false;
                            //$response["itineraries"] = array();
                            //$tmp = array();
                            //$response["message"] = "Itinerary created successfully";
                            $tmp["itinerary_id"] = $itinerary["itinerary_id"];
                            $tmp["driver_id"] = $itinerary["driver_id"];
                            //$response["itinerary_id"] = $itinerary_id;
                            echoRespnse(201, $response);
                        } else {
                            $response["error"] = true;
                            $response["message"] = "Failed to create itinerary. Please try again!";
                            echoRespnse(200, $response);
                        }    
                    } else {
                        $response["error"] = true;
                        $response["message"] = "Can't find the siutable driver for you. Please try again!";
                    }
                } else {
                    $response["error"] = true;
                    $response["message"] = "Can't find the siutable driver for you. Please try again!";
                }
            } else {
                $response["error"] = true;
                $response["message"] = "Can't find the siutable driver for you. Please try again!";
            }

            //echo $response;
            echoRespnse(200, $response);

        });


$app->post('/calldriveritinerary', 'authenticateCustomer', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('driver_id','start_address','start_address_lat','start_address_long','end_address',
                'end_address_lat','end_address_long','time_start', 'distance', 'description'));

            $response = array();
            
            $driver_id = $app->request->post('driver_id');
            $start_address = $app->request->post('start_address');
            $start_address_lat = $app->request->post('start_address_lat');
            $start_address_long = $app->request->post('start_address_long');
            $end_address = $app->request->post('end_address');
            $end_address_lat = $app->request->post('end_address_lat');
            $end_address_long = $app->request->post('end_address_long');
            $time_start = $app->request->post('time_start');
            $description = $app->request->post('description');
            $distance = $app->request->post('distance');


            //echo $start_address;

            global $user_id;
            $db = new DbHandler();

            // creating new itinerary
            $result = $db->createCallDriverItinerary($user_id, $driver_id, $start_address, $start_address_lat,$start_address_long,
             $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance);

            if ($result != NULL) {
                $response["error"] = false;
                $response["itineraries"] = array();
                $tmp = array();
                //$response["message"] = "Itinerary created successfully";
                $tmp["itinerary_id"] = $result["itinerary_id"];
                $tmp["driver_id"] = $result["driver_id"];
                $tmp["customer_id"] = $result["customer_id"];
                $tmp["start_address"] = $result["start_address"];
                $tmp["start_address_lat"] = $result["start_address_lat"];
                $tmp["start_address_long"] = $result["start_address_long"];
                $tmp["end_address"] = $result["end_address"];
                $tmp["end_address_lat"] = $result["end_address_lat"];
                $tmp["end_address_long"] = $result["end_address_long"];
                $tmp["time_start"] = $result["time_start"];
                //$response["duration"] = $result["duration"];
                $tmp["distance"] = $result["distance"];
                //$response["cost"] = $result["cost"];
                $tmp["description"] = $result["description"];
                $tmp["status"] = $result["status"];
                $tmp["created_at"] = $result["created_at"];

                array_push($response["itineraries"], $tmp);
                //$response["itinerary_id"] = $itinerary_id;
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Failed to create itinerary. Please try again";
                echoRespnse(200, $response);
            }            
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
                $response["itineraries"] = array();
                $tmp = array();

                $tmp["itinerary_id"] = $result["itinerary_id"];
                $tmp["driver_id"] = $result["driver_id"];
                $tmp["customer_id"] = $result["customer_id"];
                $tmp["start_address"] = $result["start_address"];
                $tmp["start_address_lat"] = $result["start_address_lat"];
                $tmp["start_address_long"] = $result["start_address_long"];
                $tmp["end_address"] = $result["end_address"];
                $tmp["end_address_lat"] = $result["end_address_lat"];
                $tmp["end_address_long"] = $result["end_address_long"];
                $tmp["time_start"] = $result["time_start"];
                //$tmp["duration"] = $result["duration"];
                $tmp["distance"] = $result["distance"];
                //$tmp["cost"] = $result["cost"];
                $tmp["description"] = $result["description"];
                $tmp["status"] = $result["status"];
                $tmp["created_at"] = $result["created_at"];

                array_push($response["itineraries"], $tmp);
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
                $tmp["time_start"] = $itinerary["time_start"];
                $tmp["distance"] = $itinerary["distance"];
                //$tmp["cost"] = $itinerary["cost"];
                $tmp["description"] = $itinerary["description"];
                $tmp["status"] = $itinerary["status"];
                $tmp["created_at"] = $itinerary["created_at"];

                $tmp["driver_avatar"] = $itinerary["driver_avatar"];
                //rating
                //$tmp["average_rating"] = $db->getAverageRatingofDriver($itinerary["user_id"]);
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
                $tmp["time_start"] = $itinerary["time_start"];
                $tmp["distance"] = $itinerary["distance"];
                //$tmp["cost"] = $itinerary["cost"];
                $tmp["description"] = $itinerary["description"];
                $tmp["status"] = $itinerary["status"];
                $tmp["created_at"] = $itinerary["created_at"];

                array_push($response["itineraries"], $tmp);

                //echoRespnse(200, $itinerary);
            }

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
                $tmp["time_start"] = $itinerary["time_start"];
                $tmp["distance"] = $itinerary["distance"];
                $tmp["description"] = $itinerary["description"];
                $tmp["status"] = $itinerary["status"];
                $tmp["created_at"] = $itinerary["created_at"];
                array_push($response["itineraries"], $tmp);
            }

            //print_r($response);
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
$app->put('/update_accept_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;
            //$itinerary_fields = array();           

            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateAcceptedItinerary($itinerary_id, $user_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Itinerary accepted successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to accepted. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating when itinerary is rejected by customer
 * method PUT
 * params 
 * url - /accept_itinerary/:id
 */
$app->put('/update_ongoing_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;
            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateOngoingItinerary($itinerary_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Itinerary ongoing successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed updated to ongoing. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Updating when itinerary is accepted by driver
 * method PUT
 * params 
 * url - /accept_itinerary/:id
 */
$app->put('/update_finished_itinerary/:id', 'authenticateUser', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));

            global $user_id;
            $db = new DbHandler();
            $response = array();
            // updating task
            $result = $db->updateFinishedItinerary($itinerary_id);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Itinerary finished";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to finish. Please try again!";
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

///////////////////////////// FEEDBACK ///////////////////////////////////////////////


$app->post('/feedback', 'authenticateUser', function() use ($app) {
            global $user_id;
            // check for required params
            verifyRequiredParams(array('content'));

            $response = array();

            // reading post params
            $content = $app->request->post('content');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createFeedback($user_id, $content);

            if ($res == USER_CREATED_FEEDBACK_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Thank for your feedback!";
            } else if ($res == USER_CREATE_FEEDBACK_FAILED) {
                $response["error"] = true;
                $response["message"] = "Can not send your feedback. Please try again.";
            }
            // echo json response
            echoRespnse(201, $response);
        });

///////////////////////////////////// RATING ////////////////////////////////////////////////////


$app->get('/rating/:user_id/:rating_user_id', 'authenticateUser', function($user_id, $rating_user_id) {
            $response = array();
            $db = new DbHandler();

            if ($db->isUserExists1($user_id)) {
                $response['error'] = false;
                $rating = $db->getRating($user_id, $rating_user_id);
                
                if ($rating != NULL) {
                    $response['rating'] = $rating["rating"];;
                    echoRespnse(200, $response);
                } else {
                    $response["message"] = "The link you request is not existing!";
                    echoRespnse(404, $response);
                }
                echoRespnse(200, $response);

            } else {
                $response['error'] = true;
                $response['message'] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });

/**
 * Get driver information
 * method GET
 * url /driver
 */
$app->get('/average_rating/:user_id', 'authenticateUser', function($user_id) {

            $language = "en";

            $response = array();
            $db = new DbHandler();

            // fetch task
            $average_rating = $db->getAverageRatingofDriver($user_id);

            if ($average_rating != NULL) {
                $response["error"] = false;
                $response['average_rating'] = $average_rating["average_rating"];;
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });


/**
 * Comment creation
 * url - /comment
 * method - POST
 * params - 
 */
$app->post('/rating', 'authenticateCustomer', function() use ($app) {
            global $user_id;

            verifyRequiredParams(array('rating', 'rating_user_id'), $language);

            $response = array();

            $rating = $app->request->post('rating');
            $driver_id = $app->request->post('driver_id');

            $db = new DbHandler();
            $res = $db->createRating($user_id, $driver_id, $rating);

            if ($res == c) {
                $response["error"] = false;
                $response["message"] = "The link you request is not existing!";
            } else if ($res == RATING_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!";
            }
            // echo json response
            echoRespnse(201, $response);
        });


/**
 * Deleting user.
 * method DELETE
 * url /user
 */
$app->delete('/rating/:rating_id', 'authenticateUser', function($rating_id) {
            global $user_id;


            $db = new DbHandler();
            $response = array();

            $result = $db->deleteRating($rating_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Delete vehicle is successful";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Delete vehicle is not successful";
            }
            echoRespnse(200, $response);
        });



///////////////////////////////////// STATISTICS ////////////////////////////////////////////////

//Staticstic for admin
$app->get('/statistic/:field', 'authenticateStaff', function($field) {
            $response = array();
            $db = new DbHandler();

            if ($field == 'user'){
                $result = $db->statisticUserBy("123");
            } else if ($field == 'itinerary'){
                $result = $db->statisticItineraryBy("123");
            } else if ($field == 'total_money'){
                $result = $db->statisticMoneyBy("123");
            } else {

            }

            if (isset($result)) {
                $response['error'] = false;
                $response['stats'] = $result;

                echoRespnse(200, $response);

            } else {
                $response['error'] = true;
                $response['message'] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });


//staticstic for customer
$app->get('/statistic_customer/:field', 'authenticateUser', function($field) {
            global $user_id;

            $response = array();
            $db = new DbHandler();

            if ($field == 'itinerary'){
                $result = $db->statisticCustomerItineraryBy("123", $user_id);
            } else if ($field == 'total_money'){
                $result = $db->statisticCustomerMoneyBy("123", $user_id);
            } else {

            }

            if (isset($result)) {
                $response['error'] = false;
                $response['stats'] = $result;

                echoRespnse(200, $response);

            } else {
                $response['error'] = true;
                $response['message'] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });

$app->get('/statistic_driver/:field', 'authenticateUser', function($field) {
            global $user_id;

            $response = array();
            $db = new DbHandler();

            if ($field == 'itinerary'){
                $result = $db->statisticDriverItineraryBy("123", $user_id);
            } else if ($field == 'total_money'){
                $result = $db->statisticDriverMoneyBy("123", $user_id);
            } else {

            }

            if (isset($result)) {
                $response['error'] = false;
                $response['stats'] = $result;

                echoRespnse(200, $response);

            } else {
                $response['error'] = true;
                $response['message'] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });

/////////////////////////////////////// VEHICLE /////////////////////////////////////////////////


/**
 * Vehicle Registration
 * url - /vehicle
 * method - POST
 * params - vehicle
 */
$app->post('/vehicle', 'authenticateDriver', function() use ($app) {
            global $user_id;


            verifyRequiredParams(array('type', 'license_plate', 'license_plate_img', 'vehicle_img'));

            $response = array();

            $type = $app->request->post('type');
            $license_plate = $app->request->post('license_plate');
            $license_plate_img = $app->request->post('license_plate_img');
            //$reg_certificate = $app->request->post('reg_certificate');
            $vehicle_img = $app->request->post('vehicle_img');
            //$motor_insurance_img = $app->request->post('motor_insurance_img');

            $db = new DbHandler();
            $res = $db->createVehicle($user_id, $type, $license_plate, $license_plate_img,
                                        $vehicle_img);

            if ($res == VEHICLE_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You have registered this vehicle!";
            } else if ($res == VEHICLE_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Vehicle is already existed";
            } else if ($res == VEHICLE_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Vehicle Registration Failed";
            }
            // echo json response
            echoRespnse(201, $response);
        });

$app->get('/vehicles', 'authenticateDriver', function() {
            global $user_id;

            $db = new DbHandler();

            // fetch task
            $result = $db->getListVehicle($user_id);

            if ($result != NULL) {
                $response['error'] = false;
                $response['vehicles'] = array();

                while ($vehicle = $result->fetch_assoc()) {
                    array_push($response['vehicles'], $vehicle);               
                }
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "";
                echoRespnse(404, $response);
            }
        });

/**
 * Get driver information
 * method GET
 * url /driver
 */
$app->get('/vehicle/:vehicle_id', 'authenticateDriver', function($vehicle_id) {

            $response = array();
            $db = new DbHandler();

            // fetch task
            $vehicle = $db->getVehicle($vehicle_id);

            if ($vehicle != NULL) {
                $response["error"] = false;
                $response['vehicle_id'] = $vehicle["vehicle_id"];
                $response['driver_id'] = $vehicle["driver_id"];
                $response['type'] = $vehicle["type"];
                $response['license_plate'] = $vehicle["license_plate"];
                //$response['reg_certificate'] = $vehicle["reg_certificate"];
                $response['license_plate_img'] = $vehicle["license_plate_img"];
                $response['vehicle_img'] = $vehicle["vehicle_img"];
                //$response['motor_insurance_img'] = $vehicle["motor_insurance_img"];
                $response['status'] = $vehicle["status"];
                $response['created_at'] = $vehicle["created_at"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "";
                echoRespnse(404, $response);
            }
        });

/**
 * Updating user
 * method PUT
 * params task, status
 * url - /user
 */
$app->put('/vehicle/:vehicle_id', 'authenticateDriver', function($vehicle_id) use($app) { 

            $type = $app->request->put('type');
            $license_plate = $app->request->put('license_plate');
            //$reg_certificate = $app->request->put('reg_certificate');
            $license_plate_img = $app->request->put('license_plate_img');
            $vehicle_img = $app->request->put('vehicle_img');
            //$motor_insurance_img = $app->request->put('motor_insurance_img');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateVehicle($vehicle_id, $type, $license_plate, $license_plate_img, $vehicle_img);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Your update is successful!.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Your update is not successful! Please try again.";
            }
            echoRespnse(200, $response);
        });

/**
 * Deleting user.
 * method DELETE
 * url /user
 */
$app->delete('/vehicle/:vehicle_id', 'authenticateDriver', function($vehicle_id) {

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteVehicle($vehicle_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "";
            }
            echoRespnse(200, $response);
        });


/////////////////////////////////////// ADMIN //////////////////////////////////////////////////////


/**
 * Staff Registration
 * url - /staff
 * method - POST
 * params - email, password
 */
$app->post('/staff', function() use ($app) {

            // check for required params
            verifyRequiredParams(array('email'), $language);

            $response = array();

            // reading post params
            $role = $app->request->post('role');
            $email = $app->request->post('email');
            $fullname = $app->request->post('fullname');
            $personalID = $app->request->post('personalID');

            // validating email address
            validateEmail($email, $language);

            $db = new DbHandler();
            $res = $db->createStaff($role, $email, $fullname, $personalID);

            if ($res == STAFF_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Create a new staff is successful!.";
            } else if ($res == STAFF_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry! Your email registration is existing.";
            } else if ($res == STAFF_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Sorry! Registration is missing.";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * Get list of staff
 * method GET
 * url /user
 */
$app->get('/staffs', 'authenticateStaff', function() {
            $db = new DbHandler();

            // fetch task
            $result = $db->getListStaff();

            if ($result != NULL) {
                $response['error'] = false;
                $response['staffs'] = array();

                while ($staff = $result->fetch_assoc()) {
                    array_push($response['staffs'], $staff);               
                }

                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });


/**
 * Get one staff's ìnormation
 * method GET
 * url /user
 */
$app->get('/staffs/:staff_id', 'authenticateStaff', function($staff_id) {
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getStaffByStaffID($staff_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response['email'] = $result['email'];
                $response['apiKey'] = $result['api_key'];
                $response['fullname'] = $result['fullname'];
                $response['personalID'] = $result['personalID'];
                $response['staff_avatar'] = $result['staff_avatar'];
                $response['created_at'] = $result['created_at'];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });

                //-----------------------------------------------------------//

/**
 * User Login
 * url - /user
 * method - POST
 * params - email, password
 */
$app->post('/staff/login', function() use ($app) {

            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();

            $res = $db->checkLoginStaff($email, $password);
            // check for correct email and password
            if ($res == LOGIN_SUCCESSFULL) {
                // get the user by email
                $staff = $db->getStaffByEmail($email);

                if ($staff != NULL) {
                    $response["error"] = false;
                    $response['apiKey'] = $staff['api_key'];
                    $response['email'] = $staff['email'];
                    $response['fullname'] = $staff['fullname'];
                    $response['personalID'] = $staff['personalID'];
                    $response['created_at'] = $staff['created_at'];
                    $response['staff_avatar'] = $staff['staff_avatar'];
                    $response['staff_id'] = $staff['staff_id'];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "Have some mistakes, Please try again!.";
                }
            } elseif ($res == WRONG_PASSWORD || $res == STAFF_NOT_REGISTER) {
                $response['error'] = true;
                $response['message'] = "Wrong email or password!.";
            }

            echoRespnse(200, $response);
        });

/**
 * Get all staff's information
 * method GET
 * url /user
 */
$app->get('/staff', 'authenticateStaff', function() {
            global $staff_id;

            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getStaffByStaffID($staff_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response['role'] = $result['role'];
                $response['email'] = $result['email'];
                $response['apiKey'] = $result['api_key'];
                $response['fullname'] = $result['fullname'];
                $response['personalID'] = $result['personalID'];
                $response['created_at'] = $result['created_at'];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The link you request is not existing!";
                echoRespnse(404, $response);
            }
        });


/**
 * Update staff information
 * method GET
 * url /user
 */
$app->put('/staffs/:staff_id', 'authenticateStaff', function($staff_id) use($app) {
        
            $fullname = $app->request->put('fullname');
            $email = $app->request->put('email');
            $personalID = $app->request->put('personalID');
            $link_avatar = $app->request->put('link_avatar');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateStaff($staff_id, $fullname, $email, $personalID, $link_avatar);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Your update is successful!.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Your update is not successful! Please try again.";
            }
            echoRespnse(200, $response);
        });

/**
 * Deleting user.
 * method DELETE
 * url /staff/user
 */
$app->delete('/staffs/:staff_id', 'authenticateStaff', function($staff_id) {

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteStaff($staff_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Delete staff is successful!.";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Delete staff is failure!.";
            }
            echoRespnse(200, $response);
        });

            //////////////////////////////////////////////////

/**
 * Get all customer information
 * method GET
 * url /user
 */
$app->get('/staff/customer', 'authenticateStaff', function() {

            $response = array();
            $db = new DbHandler();

            $response['error'] = false;
            $response['customers'] = array();

            // fetch task
            $result = $db->getListCustomer();

            while ($user = $result->fetch_assoc()) {
                array_push($response['customers'], $user);               
            }

            echoRespnse(200, $response);
        });
        

/**
 * Get user information
 * method GET
 * url /staff/user
 */
$app->get('/staff/customer/:customer_id', 'authenticateStaff', function($customer_id) {

            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getCustomerByID($customer_id);

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
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });
           
        
/**
 * Get all driver information
 * method GET
 * url /user
 */
$app->get('/staff/driver', 'authenticateStaff', function() {

            $response = array();
            $db = new DbHandler();

            $response['error'] = false;
            $response['drivers'] = array();

            // fetch task
            $result = $db->getListDriver();

            while ($user = $result->fetch_assoc()) {
                array_push($response['drivers'], $user);               
            }

            echoRespnse(200, $response);
        });
/**
 * Get user information
 * method GET
 * url /staff/user
 */
$app->get('/staff/driver/:driver_id', 'authenticateStaff', function($driver_id) {

            $response = array();
            $db = new DbHandler();

            // fetch task
            $driver = $db->getDriverByID($driver_id);

            if ($driver != NULL) {
                $response["error"] = false;
                $response["driver_id"] = $driver["driver_id"];
                $response['email'] = $driver['email'];
                $response['fullname'] = $driver['fullname'];
                $response['phone'] = $driver['phone'];
                $response['driver_lat'] = $driver['driver_lat'];
                $response['driver_long'] = $driver['driver_long'];
                $response['created_at'] = $driver['created_at'];
                $response['status'] = $driver['status'];
                $response['busy_status'] = $driver['busy_status'];
                $response['personalID'] = $driver['personalID'];
                $response['personalID_img'] = $driver['personalID_img'];
                $response["driver_avatar"] = $driver["driver_avatar"];
                $response['driver_license'] = $driver['driver_license'];
                $response['driver_license_img'] = $driver['driver_license_img'];
                //rating
                $driver_id = $response["driver_id"];
                $response["average_rating"] = $db->getAverageRatingofDriver($driver_id);
                //$response['driver_license'] = $result['driver_license'];
                //$response['driver_license_img'] = $result['driver_license_img'];
                echoRespnse(200, $response);
                //echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });

/**
 * Deleting user.
 * method DELETE
 * url /staff/user
 */
$app->delete('/staff/user/:user_id', 'authenticateStaff', function($user_id) {

            $db = new DbHandler();
            $response = array();

            $result = $db->deleteUser($user_id);

            if ($result) {
                // user deleted successfully
                $response["error"] = false;
                $response["message"] = "Delete user is successful!.";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Delete user is failed!.";
            }
            echoRespnse(200, $response);
        });

/**
 * Listing all itineraries of particual user
 * method GET
 * url /itineraries          
 */
$app->get('/staff/itineraries', 'authenticateStaff', function() {
            global $staff_id;

            $response = array();
            $db = new DbHandler();
            // fetching all user tasks
            $result = $db->getAllItinerariesWithDriverInfo($staff_id);
            
            $response["error"] = false;
            $response["itineraries"] = $result;

            echoRespnse(200, $response);

        });


$app->get('/staff/itinerary/:id', 'authenticateStaff', function($itinerary_id) {

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
                $response["time_start"] = $result["time_start"];
                //$tmp["duration"] = $result["duration"];
                $response["distance"] = $result["distance"];
                //$tmp["cost"] = $result["cost"];
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

$app->put('/staff/itinerary/:id', 'authenticateStaff', function($itinerary_id) use($app) {
            // check for required params
            //verifyRequiredParams(array('task', 'status'));
            global $staff_id;

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
                $response["message"] =  "Itinerary updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Itinerary failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });

$app->delete('/staff/itinerary/:id', function($itinerary_id) use($app) {
            //global $staff_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteItinerary($itinerary_id);
            if ($result) {
                // itinerary deleted successfully
                $response["error"] = false;
                $response["message"] = "Itinerary deleted succesSfully";
            } else {
                // itinerary failed to delete
                $response["error"] = true;
                $response["message"] = "Itinerary failed to delete. Please try again!";
            }
            echoRespnse(200, $response);
        });


/////////////////////////////////////////////////////////////////////////////////////////////////


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
        $response["message"] = 'Please input into the field!' . substr($error_fields, 0, -2) . ' !';
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
        $response["message"] = 'Your email is not validation!';
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
        $response["message"] = 'The length of your password must limited from 6 to 12 characters!';
        echoRespnse(200, $response);
        $app->stop();
    } 

    if (preg_match('#[\\s]#', $password)) {
        $response["error"] = true;
        $response["message"] = 'Your password not contain the blank space.';
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
    $mail->Username     = "letrungvi@outlook.com";  // GMAIL username
    $mail->Password     = "shenlong1909";            // GMAIL password

    $mail->SetFrom('letrungvi@outlook.com', 'Verification Team'); //Sender

    $mail->Subject    = "Activate account"; //Subject

    $mail->MsgHTML($body);

    $address = $receiver_mail; //Receiver
    $mail->AddAddress($address, "Guest"); //Send to?

    // $mail->AddAttachment("dinhkem/02.jpg");      // Attach
    // $mail->AddAttachment("dinhkem/200_100.jpg"); // Attach

    if(!$mail->Send()) {
      return "Error sending email:" . $mail->ErrorInfo;
    } else {
      return "Email is sending!";
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
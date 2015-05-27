<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Halley Team
 * @link URL Tutorial link
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    /* ------------- `CUSTOMER` table method ------------------ */
    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createCustomer($email, $password) {
        require_once 'PassHash.php';

        // First check if user already existed in db
        if (!$this->isCustomerExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            $sql_query = "INSERT INTO customer(email, password, api_key, status) values(?, ?, ?, ". USER_NOT_ACTIVATE. ")";

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("sss", $email, $password_hash, $api_key);

                $result = $stmt->execute();
            } else {
                var_dump($this->conn->error);
            }

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
    }

    /**
     * Activate user
     * @param String $activation_code Activation code
     */
    public function activateCustomer($activation_code) {
        // fetching user by activation code
        $sql_query = "SELECT customer_id FROM customer WHERE api_key = ? AND status = ". USER_NOT_ACTIVATE;

        $stmt = $this->conn->prepare($sql_query);

        $stmt->bind_param("s", $activation_code);

        if ($stmt->execute()) {
            $stmt->bind_result($customer_id);

            $stmt->store_result();

            $stmt->fetch();
        }

        if ($stmt->num_rows > 0) {
            // Found user with the activation code
            // Now activate user

            $api_key = $this->generateApiKey();

            $sql_query = "UPDATE customer SET api_key = ?, status = ". USER_ACTIVATED. " WHERE customer_id = ". $customer_id;

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("s", $api_key);

                $result = $stmt->execute();
            } else {
                var_dump($customer_id);
                var_dump($this->conn->error);
            }

            $stmt->close();

            return USER_ACTIVATED_SUCCESSFULLY;
        } else {
            $stmt->close();

            return USER_ACTIVATE_FAILED;
        }       
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password, status FROM customer WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash, $status);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            
            if (PassHash::check_password($password_hash, $password)) {
                if ($status <= 1) {
                    return CUSTOMER_NOT_ACTIVATE;
                } 
                return CUSTOMER_LOGIN_SUCCESSFULL;
            } else {
                return WRONG_PASSWORD;
            }
        } else {
            $stmt->close();

            $stmt = $this->conn->prepare("SELECT password, status FROM driver WHERE email = ?");
            $stmt->bind_param("s", $email);

            $stmt->execute();

            $stmt->bind_result($password_hash, $status);

            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Found user with the email
                // Now verify the password

                $stmt->fetch();

                $stmt->close();

                
                if (PassHash::check_password($password_hash, $password)) {
                    if ($status <= 1) {
                        //echo "dddd";
                        return DRIVER_NOT_ACTIVATE;
                    }
                    return DRIVER_LOGIN_SUCCESSFULL;
                } else {
                    return WRONG_PASSWORD;
                }


            } else {
                return USER_NOT_REGISTER;
            }
            // user not existed with the email
            return USER_NOT_REGISTER;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    public function isCustomerExists($email) {
        $stmt = $this->conn->prepare("SELECT customer_id from customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getCustomerByEmail($email) {
        $stmt = $this->conn->prepare("SELECT customer_id, email, api_key, fullname, phone, personalID, 
                                         customer_avatar, status, created_at FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($customer_id, $email, $api_key, $fullname, $phone, $personalID,
                                    $customer_avatar, $status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["customer_id"] = $customer_id;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["fullname"] = $fullname;
            $user["phone"] = $phone;
            $user["personalID"] = $personalID;
            $user["customer_avatar"] = $customer_avatar;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getCustomerByID($user_id) {
        $stmt = $this->conn->prepare("SELECT email, api_key, fullname, phone, personalID, 
                                        customer_avatar, status, created_at FROM customer WHERE customer_id = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($email, $api_key, $fullname, $phone, $personalID,
                                    $customer_avatar, $status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["fullname"] = $fullname;
            $user["phone"] = $phone;
            $user["personalID"] = $personalID;
            $user["customer_avatar"] = $customer_avatar;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getListCustomer() {
        $stmt = $this->conn->prepare("SELECT customer_id, email, api_key, fullname, phone, personalID, 
                                         customer_avatar, status, created_at FROM customer");
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $users = $stmt->get_result();
            $stmt->close();
            return $users;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getCustomerId($api_key) {
        $stmt = $this->conn->prepare("SELECT customer_id FROM customer WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($customer_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $customer_id;
        } else {
            return NULL;
        }
    }
    

    /**
     * Change password
     * @param String $user_id id of user
     * @param String $password Password
     */
    public function changePassword($customer_id, $password) {

        // Generating password hash
        $password_hash = PassHash::hash($password);

        $stmt = $this->conn->prepare("UPDATE customer set password = ? WHERE customer_id = ?");
        $stmt->bind_param("si", $password_hash, $customer_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating user
     * @param String $user_id id of user
     * @param String $fullname Fullname
     * @param String $phone Phone Number
     * @param String $personalID Personal Identification
     * @param String $personalID_img Personal Identification Image
     * @param String $customer_avatar Link Avartar
     */
    public function updateCustomer($customer_id, $fullname, $phone, $personalID, $customer_avatar) {
        $stmt = $this->conn->prepare("UPDATE customer set fullname = ?, phone = ?, personalID = ?,
                                        customer_avatar = ?, status = 3
                                        WHERE customer_id = ?");

        $stmt->bind_param("ssssi", $fullname, $phone, $personalID, $customer_avatar, $customer_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating itinerary
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateCustomer2($customer_fields, $customer_id) {

        $q= "UPDATE customer SET ";
        foreach ($customer_fields as $key => $value) {
            //check whether the value is numeric
            if(!is_numeric($value)){
                if($key == 'password'){
                    $password_hash = PassHash::hash($value);
                    $q .= "{$key} = '{$password_hash}', ";
                } else {
                    $q .= "{$key} = '{$value}', ";
                }
                
            } else {
                    $q .= "{$key} = {$value}, ";              
            }            
        }

        $q = trim(($q));

        $nq = substr($q, 0, strlen($q) - 1 );

        $nq .= " WHERE customer_id = {$customer_id} LIMIT 1";

        $stmt = $this->conn->prepare($nq);       
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Delete user
     * @param String $user_id id of user
     */
    public function deleteCustomer($customer_id) {
        $stmt = $this->conn->prepare("DELETE FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    /* ------------- `DRIVER` table method ------------------ */
    ///////////////////////////////////////////////////////////////////////////////////////////////////////


    public function createDriver($email, $password) {
        require_once 'PassHash.php';

        // First check if user already existed in db
        if (!$this->isDriverExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            $sql_query = "INSERT INTO driver(email, password, api_key, status) values(?, ?, ?, ". USER_NOT_ACTIVATE. ")";

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("sss", $email, $password_hash, $api_key);

                $result = $stmt->execute();
            } else {
                var_dump($this->conn->error);
            }

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return DRIVER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return DRIVER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return DRIVER_ALREADY_EXISTED;
        }
    }


    public function isDriverExists($email) {
        $stmt = $this->conn->prepare("SELECT driver_id from driver WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getDriverByID($driver_id) {
        $q = "SELECT driver_id, email, api_key, fullname, phone, driver_lat, driver_long, personalID, personalID_img,";
        $q .= " driver_avatar, driver_license, driver_license_img, status, busy_status, created_at ";
        $q .= " FROM driver WHERE driver_id = ?";
        $stmt = $this->conn->prepare($q);

        $stmt->bind_param("s", $driver_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($driver_id, $email, $api_key, $fullname, $phone, $driver_lat, $driver_long, $personalID, $personalID_img,
                                    $driver_avatar, $driver_license, $driver_license_img, $status, $busy_status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["driver_id"] = $driver_id;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["fullname"] = $fullname;
            $user["phone"] = $phone;
            $user["driver_lat"] = $driver_lat;
            $user["driver_long"] = $driver_long;
            $user["personalID"] = $personalID;
            $user["personalID_img"] = $personalID_img;
            $user["driver_avatar"] = $driver_avatar;
            $user["driver_license"] = $driver_license;
            $user["driver_license_img"] = $driver_license_img;
            $user["status"] = $status;
            $user["busy_status"] = $busy_status;
            $user["created_at"] = $created_at;
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getDriverByEmail($email) {
        $q = "SELECT driver_id, email, api_key, fullname, phone, driver_lat, driver_long, personalID, personalID_img,";
        $q .= " driver_avatar, driver_license, driver_license_img, status, busy_status, created_at ";
        $q .= " FROM driver WHERE email = ?";
        //$stmt = $this->conn->prepare("SELECT driver_id, email, api_key, fullname, phone, personalID, 
        //                                 driver_avatar, status, busy_status, created_at FROM driver WHERE email = ?");

        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($driver_id, $email, $api_key, $fullname, $phone, $driver_lat, $driver_long, $personalID, $personalID_img,
                                    $driver_avatar, $driver_license, $driver_license_img, $status, $busy_status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["driver_id"] = $driver_id;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["fullname"] = $fullname;
            $user["phone"] = $phone;
            $user["driver_lat"] = $driver_lat;
            $user["driver_long"] = $driver_long;
            $user["personalID"] = $personalID;
            $user["personalID_img"] = $personalID_img;
            $user["driver_avatar"] = $driver_avatar;
            $user["driver_license"] = $driver_license;
            $user["driver_license_img"] = $driver_license_img;
            $user["status"] = $status;
            $user["busy_status"] = $busy_status;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    public function getListDriver() {
        //$q = "SELECT * FROM driver ";
        $q = "SELECT driver_id, email, fullname, phone, driver_lat, driver_long, personalID, personalID_img,";
        $q .= " driver_avatar, driver_license, driver_license_img, status, busy_status, created_at ";
        $q .= " FROM driver";

        $stmt = $this->conn->prepare($q);
        //$stmt->bind_param("dd", $lat, $long);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    public function getAllDriversTopTen($lat, $long) {
        //$q = "SELECT * FROM driver ";
        $q = "SELECT driver_id, email, fullname, phone, driver_lat, driver_long, personalID, personalID_img,";
        $q .= " driver_avatar, driver_license, driver_license_img, status, busy_status, created_at, (abs(driver_lat - ?) + abs(driver_long - ?)) AS distance";
        $q .= " FROM driver WHERE busy_status = 1 ORDER BY distance LIMIT 10";

        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("dd", $lat, $long);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    public function getAllDriversTopOne($lat, $long) {
        //$q = "SELECT * FROM driver ";
        $q = "SELECT driver_id, email, fullname, phone, driver_lat, driver_long, personalID, personalID_img,";
        $q .= " driver_avatar, driver_license, driver_license_img, status, busy_status, created_at, (abs(driver_lat - ?) + abs(driver_long - ?)) AS distance";
        $q .= " FROM driver WHERE busy_status = 1 ORDER BY distance LIMIT 1";

        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("dd", $lat, $long);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    /*public function isDriver($user_id) {
        $stmt = $this->conn->prepare("SELECT driver_id FROM driver WHERE driver_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }*/

    /**
     * Updating driver
     * @param String $user_id id of user
     * @param String $driver_license Driver License
     * @param String $driver_license_img Driver License Image
     */
    public function updateDriver($driver_id, $driver_license, $driver_license_img) {
        $stmt = $this->conn->prepare("UPDATE driver set driver_license = ?, driver_license_img = ?
                                        WHERE driver_id = ?");

        $stmt->bind_param("ssi", $driver_license, $driver_license_img, $driver_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating driver
     * @param String $user_id id of user
     * @param String $driver_license Driver License
     * @param String $driver_license_img Driver License Image
     */
    public function updateDriverBusyStatus($driver_id, $busy_status) {
        $stmt = $this->conn->prepare("UPDATE driver set busy_status = ?
                                        WHERE driver_id = ?");

        $stmt->bind_param("ii", $busy_status, $driver_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function updateDriverBusyStatusLatLong($driver_id, $busy_status, $driver_lat, $driver_long) {
        $stmt = $this->conn->prepare("UPDATE driver set busy_status = ?, driver_lat = ?, driver_long = ?   
                                        WHERE driver_id = ?");

        $stmt->bind_param("iddi", $busy_status,$driver_lat, $driver_long, $driver_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating itinerary
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateDriver2($driver_fields, $driver_id) {

        $q= "UPDATE driver SET ";
        foreach ($driver_fields as $key => $value) {
            //check whether the value is numeric
            if(!is_numeric($value)){
                if($key == 'password'){
                    $password_hash = PassHash::hash($value);
                    $q .= "{$key} = '{$password_hash}', ";
                } else {
                    $q .= "{$key} = '{$value}', ";
                }
                
            } else {
                    $q .= "{$key} = {$value}, ";              
            }            
        }

        $q = trim(($q));

        $nq = substr($q, 0, strlen($q) - 1 );

        $nq .= " WHERE driver_id = {$driver_id} LIMIT 1";

        $stmt = $this->conn->prepare($nq);       
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Delete driver
     * @param String $user_id id of user
     */
    public function deleteDriver($driver_id) {
        $stmt = $this->conn->prepare("DELETE FROM driver WHERE driver_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //not finished yet
    /**
     * Fetching all itineraries
     */
    public function getAllDrivers() {
        $q = "SELECT * FROM driver ";
        //$q = "SELECT driver_id, email, fullname, phone, driver_lat, driver_long, personalID, personalID_img,
        // driver_avatar, driver_license, driver_license_img, status FROM driver ";

        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getDriverId($api_key) {
        $stmt = $this->conn->prepare("SELECT driver_id FROM driver WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($driver_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $driver_id;
        } else {
            return NULL;
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    /* ------------- `ITINERARY` table method ------------------ */
    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    //not finished yet
    /**
     * Creating new itinerary
     * @param Integer $driver_id user id to whom itinerary belongs to
     * @param String $start_address, $end_address, $leave_day, $duration, $cost, $description are itinerary's properties
     */
    public function createItinerary($customer_id, $driver_id, $start_address, $start_address_lat,$start_address_long,
             $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance) {
        $q = "INSERT INTO itinerary(customer_id, driver_id, start_address, start_address_lat, start_address_long, 
            end_address, end_address_lat, end_address_long, time_start, description, distance, status) ";
                $q .= " VALUES(?,1,?,?,?,?,?,?,?,?,?,". ITINERARY_STATUS_CREATED.")";
        $stmt = $this->conn->prepare($q);
		
        $stmt->bind_param("iisddsddssd",
            $customer_id, $driver_id, $start_address, $start_address_lat, $start_address_long, 
            $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance);
        
        $result = $stmt->execute();
        $stmt->close();
        //echo $end_address;
        if ($result) {
            $new_itinerary_id = $this->conn->insert_id;
            
            // Itinerary successfully inserted
            $res = $this->getItinerary($new_itinerary_id);
            return $res;
        } else {
            //echo $q;
            return NULL;
        }

        // Check for successful insertion
        /*if ($result) {
            // Itinerary successfully inserted
            return ITINERARY_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create itinerary
            return ITINERARY_CREATE_FAILED;
        }*/

    }


    //not finished yet
    /**
     * Creating new itinerary
     * @param Integer $driver_id user id to whom itinerary belongs to
     * @param String $start_address, $end_address, $leave_day, $duration, $cost, $description are itinerary's properties
     */
    public function createCallDriverItinerary($customer_id, $driver_id, $start_address, $start_address_lat,$start_address_long,
             $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance) {
        $q = "INSERT INTO itinerary(customer_id, driver_id, start_address, start_address_lat, start_address_long, 
            end_address, end_address_lat, end_address_long, time_start, description, distance, status) ";
                $q .= " VALUES(?,?,?,?,?,?,?,?,?,?,?,". ITINERARY_STATUS_ONGOING.")";
        $stmt = $this->conn->prepare($q);
        
        $stmt->bind_param("iisddsddssd",
            $customer_id, $driver_id, $start_address, $start_address_lat, $start_address_long, 
            $end_address, $end_address_lat, $end_address_long, $time_start, $description, $distance);
        
        $result = $stmt->execute();
        $stmt->close();
        //echo $end_address;
        if ($result) {
            $new_itinerary_id = $this->conn->insert_id;
            
            // Itinerary successfully inserted
            //return $new_itinerary_id;

            $res = $this->getItinerary($new_itinerary_id);
            return $res;
        } else {
            //echo $q;
            return NULL;
        }

        // Check for successful insertion
        /*if ($result) {
            // Itinerary successfully inserted
            return ITINERARY_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create itinerary
            return ITINERARY_CREATE_FAILED;
        }*/

    }

    public function findSuitableDriver(){
        $q1 = "SELECT d.driver_id, t.day, t.month, t.year ";

        $q1 .= " FROM (SELECT * FROM driver WHERE busy_status = ". DRIVER_NOT_BUSY.") as d, busytime as t WHERE ";

        $q1 .= " d.driver_id = t.driver_id ";

        $stmt = $this->conn->prepare($q1);
        $stmt->execute();
        $drivers = $stmt->get_result();
        $stmt->close();
        return $drivers;
    }

    //not finished yet
    /**
     * Fetching single itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function getItinerary($itinerary_id) {
        $q = "SELECT * FROM itinerary WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$itinerary_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($itinerary_id, $driver_id, $customer_id, $start_address, $start_address_lat, $start_address_long,
                $end_address, $end_address_lat, $end_address_long,
                $time_start, $distance, $description, $status, $created_at);
            // TODO
            // $task = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $res["itinerary_id"] = $itinerary_id;
            $res["driver_id"] = $driver_id;
            $res["customer_id"] = $customer_id;
            $res["start_address"] = $start_address;
            $res["start_address_lat"] = $start_address_lat;
            $res["start_address_long"] = $start_address_long;
            $res["end_address"] = $end_address;
            $res["end_address_lat"] = $end_address_lat;
            $res["end_address_long"] = $end_address_long;
            $res["time_start"] = $time;
            $res["distance"] = $distance;
            $res["description"] = $description;
            $res["status"] = $status;
            $res["created_at"] = $created_at;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    //not finished yet
    /**
     * Fetching all itineraries
     */
    public function getAllItineraries() {
        $q = "SELECT * FROM itinerary";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    public function getAllItinerariesWithDriverInfo() {
        $q = "SELECT * FROM itinerary, driver WHERE itinerary.driver_id = driver.driver_id";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    //not finished yet
    /**
     * Fetching all itineraries of one driver
     * @param Integer $driver_id id of the driver
     */
    public function getDriverItineraries2($driver_id, $order) {
        $q = "SELECT itinerary_id, i.driver_id, i.customer_id, start_address, start_address_lat, start_address_long,
            end_address, end_address_lat, end_address_long, leave_date, duration, distance, description, i.status as itinerary_status, i.created_at,
            driver_license, driver_license_img, u.user_id, u.email, u.fullname, u.phone, personalID, customer_avatar ";
        $q .=    " FROM itinerary as i, driver as d, user as u ";
        $q .=     " WHERE i.driver_id = d.user_id AND d.user_id = u.user_id AND driver_id = ? ";

        if(isset($order)){
            //$q .= "ORDER BY " .$order;
			$q .= "ORDER BY i.driver_id";
        } else {
            $q .= "ORDER BY itinerary_status";
        }
        //$q = "SELECT * FROM itinerary WHERE driver_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$driver_id);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    //not finished yet
    /**
     * Fetching all itineraries of one customer
     * @param Integer $customer_id id of the customer
     */
    public function getCustomerItineraries($customer_id, $order) {

        $q = "SELECT * FROM itinerary WHERE customer_id = ? ";
        if(isset($order)){
            $q .= "ORDER BY " .$order;
        } else {
            $q .= "ORDER BY status";
        }
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$customer_id);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    public function getDriverItineraries($customer_id, $order) {

        $q = "SELECT * FROM itinerary WHERE driver_id = ? ";
        if(isset($order)){
            $q .= "ORDER BY " .$order;
        } else {
            $q .= "ORDER BY status";
        }
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$customer_id);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    //not finished yet
    /**
     * Updating itinerary
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateItinerary2($itinerary_fields, $itinerary_id) {

        $q= "UPDATE itinerary SET ";
        foreach ($itinerary_fields as $key => $value) {
            //check whether the value is numeric
            if(!is_numeric($value)){
                $q .= "{$key} = '{$value}', ";
            } else {
                $q .= "{$key} = {$value}, ";
            }            
        }

        $q = trim(($q));

        $nq = substr($q, 0, strlen($q) - 1 );

        $nq .= " WHERE itinerary_id = {$itinerary_id} LIMIT 1";

        $stmt = $this->conn->prepare($nq);       
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function checkItineraryStatus($itinerary_id){
        $q = "SELECT status FROM itinerary WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$itinerary_id);
        $stmt->execute();

        $stmt->bind_result($status);
            $stmt->close();

        if($status == null){
            return 0;
        } else {
            return $status;
        }
    }

    /**
     * Updating accepted itinerary by customer
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateAcceptedItinerary($itinerary_id, $driver_id) {
        //ITINERARY_STATUS_CUSTOMER_ACCEPTED
        $q = "UPDATE itinerary set driver_id = ?, status = 2 
                WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare($q);
        //echo $driver_id;
        $stmt->bind_param("ii",$driver_id, $itinerary_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating rejected itinerary by customer
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateOngoingItinerary($itinerary_id) {
        $q = "UPDATE itinerary set status = 3 
                WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i", $itinerary_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Updating accepted itinerary by driver
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateFinishedItinerary($itinerary_id) {
        $q = "UPDATE itinerary set status = 4 
                WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i", $itinerary_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    //not finished yet
    /**
     * Deleting a itinerary
     * @param String $itinerary_id id of the itinerary to delete
     */
    public function deleteItinerary($itinerary_id) {
        $stmt = $this->conn->prepare("DELETE FROM itinerary WHERE itinerary_id = ?");
        $stmt->bind_param("i", $itinerary_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /* ------------- Busytime table ------------------ */

    /**
     * Creating new itinerary
     * @param Integer $driver_id user id to whom itinerary belongs to
     * @param String $start_address, $end_address, $leave_day, $duration, $cost, $description are itinerary's properties
     */
    public function createBusytime($driver_id, $day, $month, $year, $from_hour, $to_hour) {
        $q = "INSERT INTO itinerary(driver_id, day, month, year, from_hour, to_hour) ";
                $q .= " VALUES(?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($q);
        
        $stmt->bind_param("iiiiii", $driver_id, $day, $month, $year, $from_hour, $to_hour);
        
        $result = $stmt->execute();
        $stmt->close();
        //echo $end_address;
        if ($result) {
            return $result;
        } else {
            //echo $q;
            return NULL;
        }

    }

    /* ------------- Feedback table ------------------ */

    public function createFeedback($customer_id, $content) {
        $sql_query = "INSERT INTO feedback(customer_id, content) values(?, ?)";

        // insert query
        if ($stmt = $this->conn->prepare($sql_query)) {
            $stmt->bind_param("is", $customer_id, $content);
            $result = $stmt->execute();
        } else {
            var_dump($this->conn->error);
        }

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // User successfully inserted
            return USER_CREATED_FEEDBACK_SUCCESSFULLY;
        } else {
            // Failed to create user
            return USER_CREATE_FEEDBACK_FAILED;
        }
    }


    /* ------------- Statistic ------------------ */

    //number of users created per month
    public function statisticUserBy($field) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(DATE_FORMAT(created_at,'%Y-%m')) as number 
                FROM user GROUP BY DATE_FORMAT(created_at,'%Y-%m')";
        
        $stmt = $this->conn->prepare($q);
        //$stmt->bind_param("i",$customer_id);
        $stmt->execute();
        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["number"] = $stat["number"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }

    //number of itineraries creted per month
    public function statisticItineraryBy($field) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(DATE_FORMAT(created_at,'%Y-%m')) as number 
                FROM itinerary GROUP BY DATE_FORMAT(created_at,'%Y-%m')";
        
        $stmt = $this->conn->prepare($q);
        //$stmt->bind_param("i",$customer_id);
        $stmt->execute();
        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["number"] = $stat["number"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }

    //total money come frome itineraries per month
    public function statisticMoneyBy($field) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, SUM(cost) as total_money 
                FROM itinerary GROUP BY DATE_FORMAT(created_at,'%Y-%m')";
        
        $stmt = $this->conn->prepare($q);
        //$stmt->bind_param("i",$customer_id);
        $stmt->execute();
        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["total_money"] = $stat["total_money"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }


    //Customer staticstic 
    //number of itineraries creted per month
    public function statisticCustomerItineraryBy($field, $customer_id) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(DATE_FORMAT(created_at,'%Y-%m')) as number 
                FROM (SELECT * FROM itinerary WHERE customer_id = ?) as i GROUP BY DATE_FORMAT(created_at,'%Y-%m')";
        $stmt = $this->conn->prepare($q);
        if ($stmt->bind_param("i",$customer_id)) {
            $stmt->execute();
        } else {
            var_dump($this->db->error);
        }

        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["number"] = $stat["number"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }

    //total money come frome itineraries per month
    public function statisticCustomerMoneyBy($field, $customer_id) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, SUM(cost) as total_money 
                FROM (SELECT * FROM itinerary WHERE customer_id = ?) as i GROUP BY DATE_FORMAT(created_at,'%Y-%m') ";
        
        $stmt = $this->conn->prepare($q);
        if ($stmt->bind_param("i",$customer_id)) {
            $stmt->execute();
        } else {
            var_dump($this->db->error);
        }
        
        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["total_money"] = $stat["total_money"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }

    //Driver Staticstic
    //number of itineraries creted per month
    public function statisticDriverItineraryBy($field, $driver_id) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(DATE_FORMAT(created_at,'%Y-%m')) as number 
                FROM (SELECT * FROM itinerary WHERE driver_id = ?) as i GROUP BY DATE_FORMAT(created_at,'%Y-%m')";
        
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$driver_id);
        $stmt->execute();
        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["number"] = $stat["number"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }

    //total money come frome itineraries per month
    public function statisticDriverMoneyBy($field, $driver_id) {
        $q = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, SUM(cost) as total_money 
                FROM (SELECT * FROM itinerary WHERE driver_id = ?) as i GROUP BY DATE_FORMAT(created_at,'%Y-%m')";
        
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$driver_id);
        $stmt->execute();
        $results = $stmt->get_result();

        $stats = array();
        // looping through result and preparing tasks array
        while ($stat = $results->fetch_assoc()) {
            $tmp = array();

            $tmp["month"] = $stat["month"];
            $tmp["total_money"] = $stat["total_money"];

            array_push($stats, $tmp);
        }

        $stmt->close();
        return $stats;
    }

    /* ------------- `Vehicle` table method ------------------ */

    public function createVehicle($driver_id, $type, $license_plate, $license_plate_img, $vehicle_img) {
        // First check if user already existed in db
        if (!$this->isVehicleExists($license_plate)) {

            $sql_query = "INSERT INTO vehicle(driver_id, type, license_plate, license_plate_img,
                                        vehicle_img, status) values(?, ?, ?, ?, ?, 1)";

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("issss", $driver_id, $type==NULL?'':$type, $license_plate==NULL?'':$license_plate
                                    , $license_plate_img==NULL?'':$license_plate_img
                                    , $vehicle_img==NULL?'':$vehicle_img);
                $result = $stmt->execute();
            } else {
                var_dump($this->conn->error);
            }

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return VEHICLE_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return VEHICLE_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return VEHICLE_ALREADY_EXISTED;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getListVehicle($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM vehicle WHERE driver_id = ?");

        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $vehicle = $stmt->get_result();
            $stmt->close();
            return $vehicle;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getVehicle($vehicle_id) {
        $stmt = $this->conn->prepare("SELECT vehicle_id, driver_id, type, license_plate,
                                        license_plate_img, vehicle_img, status, created_at
                                      FROM vehicle WHERE vehicle_id = ?");

        $stmt->bind_param("i", $vehicle_id);

        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($vehicle_id, $driver_id, $type, $license_plate, $license_plate_img, $vehicle_img, $status, $created_at);
            $stmt->fetch();
            $vehicle = array();
            $vehicle["vehicle_id"] = $vehicle_id;
            $vehicle["driver_id"] = $driver_id;
            $vehicle["type"] = $type;
            $vehicle["license_plate"] = $license_plate;
            $vehicle["license_plate_img"] = $license_plate_img;
            $vehicle["vehicle_img"] = $vehicle_img;
            $vehicle["status"] = $status;
            $vehicle["created_at"] = $created_at;
            $stmt->close();
            return $vehicle;
        } else {
            return NULL;
        }
    }

    public function updateVehicle($vehicle_id, $type, $license_plate, $license_plate_img, $vehicle_img) {
        require_once '/Config.php';
        $conn2 = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USERNAME, DB_PASSWORD);
        // set the PDO error mode to exception
        $conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $qry = "UPDATE vehicle set";
        $param = array();

        if (isset($type)) { 
            $qry .= " type = :type,"; 
        }
        if (isset($license_plate)) { 
            $qry .= " license_plate = :license_plate,"; 
        }
        if (isset($license_plate_img)) { 
            $qry .= " license_plate_img = :license_plate_img,"; 
        }
        if (isset($vehicle_img)) { 
            $qry .= " vehicle_img = :vehicle_img,"; 
        }

        $qry .= " status = 1 WHERE vehicle_id = :vehicle_id";

        $stmt = $conn2->prepare($qry);

        if (isset($type)) { 
            $stmt->bindParam(':type', $type);
        }
        if (isset($license_plate)) { 
            $stmt->bindParam(':license_plate', $license_plate);
        }
        if (isset($license_plate_img)) {  
            $stmt->bindParam(':license_plate_img', $license_plate_img);
        }
        if (isset($vehicle_img)) { 
            $stmt->bindParam(':vehicle_img', $vehicle_img);
        }

        $stmt->bindParam(':vehicle_id', $vehicle_id);
        $stmt->execute();
        $num_affected_rows = $stmt->rowCount();
        $conn2 = null;
        return $num_affected_rows > 0;
    }

    /**
     * Delete driver
     * @param String $user_id id of user
     */
    public function deleteVehicle($vehicle_id) {
        $stmt = $this->conn->prepare("DELETE FROM vehicle WHERE vehicle_id = ?");
        $stmt->bind_param("i", $vehicle_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isVehicleExists($license_plate) {
        $stmt = $this->conn->prepare("SELECT vehicle_id FROM vehicle WHERE license_plate = ?");
        $stmt->bind_param("s", $license_plate);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    /* ------------- `Rating` table method ------------------ */

    public function createRating($customer_id, $driver_id, $rating) {
        // First check if user already existed in db

            $sql_query = "INSERT INTO rating(customer_id, driver_id, rating) values(?, ?, ?)";

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("iii", $customer_id, $driver_id, $rating);
                $result = $stmt->execute();
            } else {
                var_dump($this->conn->error);
            }

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return RATING_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return RATING_CREATE_FAILED;
            }
    }

    public function getAverageRatingofDriver($user_id){
        $q = "SELECT AVG(rating) AS average_rating FROM rating WHERE driver_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$driver_id);
        $stmt->execute();
        $stmt->fetch();
        $stmt->bind_result($average_rating);
        $stmt->close();

        if($average_rating == null){
            return 0;
        } else {
            return $average_rating;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getRating($user_id, $rating_user_id) {
        $stmt = $this->conn->prepare("SELECT rating FROM rating WHERE user_id = ? AND rating_user_id = ?");

        $stmt->bind_param("ii", $user_id, $rating_user_id);

        if ($stmt->execute()) {

            $stmt->bind_result($rating);
            $stmt->close();
            if($rating == null){
                return 0;
            } else {
                return $rating;
            }
        } else {
            return NULL;
        }
    }


    /**
     * Delete driver
     * @param String $user_id id of user
     */
    public function deleteRating($rating_id) {
        $stmt = $this->conn->prepare("DELETE FROM rating WHERE rating_id = ?");
        $stmt->bind_param("i", $rating_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /* ------------- `staff` table method ------------------ */

    /**
     * Creating new staff
     * @param String $fullname Staff full name
     * @param String $email Staff login email id
     * @param String $personalID Staff personal ID
     */
    public function createStaff($role, $email, $fullname, $personalID) {
        require_once 'PassHash.php';

        // First check if user already existed in db
        if (!$this->isStaffExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($email);

            // Generating API key
            $api_key = $this->generateApiKey();

            $sql_query = "INSERT INTO staff(email, password, api_key, role, fullname, personalID) 
                            values(?, ?, ?, ?, ?, ?)";

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("sssiss", $email, $password_hash, $api_key, $role==NULL?ROLE_STAFF:$role,
                                    $fullname==NULL?' ':$fullname, $personalID==NULL?' ':$personalID);
                $result = $stmt->execute();
            } else {
                var_dump($this->conn->error);
            }

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return STAFF_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return STAFF_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return STAFF_ALREADY_EXISTED;
        }
    }

    /**
     * Checking staff login
     * @param String $email staff login email id
     * @param String $password staff login password
     * @return boolean User login status success/fail
     */
    public function checkLoginStaff($email, $password) {
        // fetching staff by email
        $stmt = $this->conn->prepare("SELECT password FROM staff WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                return LOGIN_SUCCESSFULL;
            } else {
                // staff password is incorrect
                return WRONG_PASSWORD;
            }
        } else {
            $stmt->close();
            // staff not existed with the email
            return STAFF_NOT_REGISTER;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getListStaff() {
        $stmt = $this->conn->prepare("SELECT staff_id, email, api_key, fullname, personalID, 
                                        staff_avatar, created_at FROM staff");
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $staffs = $stmt->get_result();
            $stmt->close();
            return $staffs;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching staff by email
     * @param String $email Staff email id
     */
    public function getStaffByEmail($email) {
        $stmt = $this->conn->prepare("SELECT email, api_key, fullname, personalID, created_at, staff_avatar, staff_id   
                                        FROM staff WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $email, $api_key, $fullname,$personalID, $created_at, $staff_avatar, $staff_id);
            $stmt->fetch();
            $staff = array();
            //$staff["role"] = $role;
            $staff["email"] = $email;
            $staff["api_key"] = $api_key;
            $staff["fullname"] = $fullname;
            $staff["personalID"] = $personalID;
            $staff["created_at"] = $created_at;
            $staff["staff_avatar"] = $staff_avatar;
            $staff["staff_id"] = $staff_id;
            $stmt->close();
            return $staff;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching staff by staff id
     * @param String $staff_id Staff id
     */
    public function getStaffByStaffID($staff_id) {
        $stmt = $this->conn->prepare("SELECT  email, api_key, fullname, personalID, created_at, staff_avatar 
                                        FROM staff WHERE staff_id = ?");
        $stmt->bind_param("s", $staff_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $email, $api_key, $fullname,$personalID, $created_at, $staff_avatar);
            $stmt->fetch();
            $staff = array();
            //$staff["role"] = $role;
            $staff["email"] = $email;
            $staff["api_key"] = $api_key;
            $staff["fullname"] = $fullname;
            $staff["personalID"] = $personalID;
            $staff["staff_avatar"] = $staff_avatar;
            $staff["created_at"] = $created_at;
            $stmt->close();
            return $staff;
        } else {
            return NULL;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isStaffExists($email) {
        $stmt = $this->conn->prepare("SELECT staff_id from staff WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching staff id by api key
     * @param String $api_key staff api key
     */
    public function getStaffId($api_key) {
        $stmt = $this->conn->prepare("SELECT staff_id FROM staff WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($staff_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $staff_id;
        } else {
            return NULL;
        }
    }

    public function updateStaff($staff_id, $fullname, $email, $personalID, $staff_avatar) {
        require_once '/Config.php';
        $conn2 = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USERNAME, DB_PASSWORD);
        // set the PDO error mode to exception
        $conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $qry = "UPDATE staff set";
        $param = array();

        if (isset($fullname)) { 
            $qry .= " fullname = :fullname"; 
        }
        if (isset($email)) { 
            $qry .= ", email = :email"; 
        }
        if (isset($personalID)) { 
            $qry .= ", personalID = :personalID"; 
        }
        if (isset($staff_avatar)) { 
            $qry .= ", staff_avatar = :staff_avatar"; 
        }

        $qry .= " WHERE staff_id = :staff_id"; 

        $stmt = $conn2->prepare($qry);

        if (isset($fullname)) { 
            $stmt->bindParam(':fullname', $fullname);
        }
        if (isset($email)) { 
            $stmt->bindParam(':email', $email);
        }
        if (isset($personalID)) {  
            $stmt->bindParam(':personalID', $personalID);
        }
        if (isset($customer_avatar)) { 
            $stmt->bindParam(':customer_avatar', $customer_avatar);
        }
        $stmt->bindParam(':staff_id', $staff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->rowCount();
        $conn2 = null;
        return $num_affected_rows > 0;
    }

    /**
     * Delete staff
     * @param String $staff_id id of staff
     */
    public function deleteStaff($staff_id) {
        $stmt = $this->conn->prepare("DELETE FROM staff WHERE staff_id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /* ------------- Utility method ------------------ */

    /**
     * Fetching api key
     * @param String $id id primary key in table
     */
    public function getApiKeyById($id, $page) {
        $stmt = $this->conn->prepare("SELECT api_key FROM ".$page." WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key, $page) {
        $stmt = $this->conn->prepare("SELECT ".$page."_id from ".$page." WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
}

?>

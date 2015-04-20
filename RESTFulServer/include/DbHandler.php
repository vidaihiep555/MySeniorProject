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
    public function activateUser($activation_code) {
        // fetching user by activation code
        $sql_query = "SELECT user_id FROM user WHERE api_key = ? AND status = ". USER_NOT_ACTIVATE;

        $stmt = $this->conn->prepare($sql_query);

        $stmt->bind_param("s", $activation_code);

        if ($stmt->execute()) {
            $stmt->bind_result($user_id);

            $stmt->store_result();

            $stmt->fetch();
        }

        if ($stmt->num_rows > 0) {
            // Found user with the activation code
            // Now activate user

            $api_key = $this->generateApiKey();

            $sql_query = "UPDATE user SET api_key = ?, status = ". USER_ACTIVATED. " WHERE user_id = ". $user_id;

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("s", $api_key);

                $result = $stmt->execute();
            } else {
                var_dump($user_id);
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

            if ($status <= 1) {
                return USER_NOT_ACTIVATE;
            } 
            elseif (PassHash::check_password($password_hash, $password)) {
                return LOGIN_SUCCESSFULL;
            } else {
                return WRONG_PASSWORD;
            }
        } else {
            $stmt->close();
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
        $stmt = $this->conn->prepare("SELECT email, api_key, fullname, phone, personalID, 
                                         customer_avatar, status, created_at FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
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
    public function getCustomerByID($user_id) {
        $stmt = $this->conn->prepare("SELECT email, api_key, fullname, phone, personalID, 
                                        customer_avatar, status, created_at FROM customer WHERE customer_id = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($email, $api_key, $fullname, $phone, $personalID,
                                    $customer_avatar, $status, $created_at, $locked);
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
     * Fetching user field by user_id
     * @param String $field User User field want to get
     * @param String $user_id User id
     */
    public function getUserByField($user_id, $field) {
        $stmt = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                                        WHERE TABLE_SCHEMA = 'rs' AND TABLE_NAME = 'user'");
        if ($stmt->execute()) {
            $fields = $stmt->get_result();
        }

        $fieldIsExitInTable = false;

        while ($row = $fields->fetch_assoc()) {
                if ($row['COLUMN_NAME'] == $field) {
                    $fieldIsExitInTable = true;
                    break;
                }           
        }

        if ($fieldIsExitInTable) {
            $qry = "SELECT ".$field." FROM user WHERE user_id = ?";
            $stmt = $this->conn->prepare($qry);
            $stmt->bind_param("s", $user_id);
            if ($stmt->execute()) {
                // $user = $stmt->get_result()->fetch_assoc();
                $stmt->bind_result($field);
                $stmt->fetch();
                $stmt->close();
                return $field;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getListUser() {
        $stmt = $this->conn->prepare("SELECT user_id, email, api_key, fullname, phone, personalID, 
                                        personalID_img, link_avatar, status, created_at, locked FROM user");
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

        $stmt = $this->conn->prepare("UPDATE customer set password = ?
                                        WHERE customer_id = ?");
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
     * @param String $link_avatar Link Avartar
     */
    public function updateUser($user_id, $fullname, $phone, $personalID, $personalID_img, $link_avatar) {
        $stmt = $this->conn->prepare("UPDATE user set fullname = ?, phone = ?, personalID = ?,
                                        personalID_img = ?, link_avatar = ?, status = 3
                                        WHERE user_id = ?");

        $stmt->bind_param("sssssi", $fullname, $phone, $personalID, $personalID_img, $link_avatar, $user_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function updateUser1($user_id, $status, $locked) {
        $stmt = $this->conn->prepare("UPDATE user set status = ?, locked = ?
                                        WHERE user_id = ?");

        $stmt->bind_param("iii", $status, $locked, $user_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Update user field by user_id
     * @param String $field User field want to update
     * @param String $user_id User id
     */
    public function updateUserField($user_id, $field, $value) {
        $stmt = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                                        WHERE TABLE_SCHEMA = 'rs' AND TABLE_NAME = 'user'");
        if ($stmt->execute()) {
            $fields = $stmt->get_result();
        }

        $fieldIsExitInTable = false;

        while ($row = $fields->fetch_assoc()) {
                if ($row['COLUMN_NAME'] == $field) {
                    $fieldIsExitInTable = true;
                    break;
                }           
        }

        if ($fieldIsExitInTable) {
            $stmt = $this->conn->prepare("UPDATE user set ".$field." = ?, status = 3 WHERE user_id = ?");
            $stmt->bind_param("si", $value, $user_id);
            $stmt->execute();

            $num_affected_rows = $stmt->affected_rows;

            $stmt->close();
            return $num_affected_rows > 0;
        } else {
            return false;
        }
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

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isLockUser($api_key) {
        $stmt = $this->conn->prepare("SELECT user_id from user WHERE api_key = ? AND locked=true");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    /* ------------- `DRIVER` table method ------------------ */
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createDriver($user_id, $driver_license, $driver_license_img) {
        // First check if user already existed in db
        if (!$this->isDriverExists($user_id)) {

            $sql_query = "INSERT INTO driver(user_id, driver_license, driver_license_img) values(?, ?, ?)";

            // insert query
            if ($stmt = $this->conn->prepare($sql_query)) {
                $stmt->bind_param("iss", $user_id, $driver_license==NULL?'':$driver_license, $driver_license_img==NULL?'':$driver_license_img);
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

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getDriverByUserID($user_id) {
        $stmt = $this->conn->prepare("SELECT driver_license, driver_license_img FROM driver WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($driver_license, $driver_license_img);
            $stmt->fetch();
            $user = array();
            $user["driver_license"] = $driver_license;
            $user["driver_license_img"] = $driver_license_img;
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
    public function isDriver($user_id) {
        $stmt = $this->conn->prepare("SELECT user_id FROM driver WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching user field by user_id
     * @param String $field User User field want to get
     * @param String $user_id User id
     */
    public function getDriverByField($user_id, $field) {
        $stmt = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                                        WHERE TABLE_SCHEMA = 'rs' AND TABLE_NAME = 'driver'");
        if ($stmt->execute()) {
            $fields = $stmt->get_result();
        }

        $fieldIsExitInTable = false;

        while ($row = $fields->fetch_assoc()) {
                if ($row['COLUMN_NAME'] == $field) {
                    $fieldIsExitInTable = true;
                    break;
                }           
        }

        if ($fieldIsExitInTable) {
            $qry = "SELECT ".$field." FROM driver WHERE user_id = ?";
            $stmt = $this->conn->prepare($qry);
            $stmt->bind_param("s", $user_id);
            if ($stmt->execute()) {
                // $user = $stmt->get_result()->fetch_assoc();
                $stmt->bind_result($field);
                $stmt->fetch();
                $stmt->close();
                return $field;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Updating driver
     * @param String $user_id id of user
     * @param String $driver_license Driver License
     * @param String $driver_license_img Driver License Image
     */
    public function updateDriver($user_id, $driver_license, $driver_license_img) {
        $stmt = $this->conn->prepare("UPDATE driver set driver_license = ?, driver_license_img = ?
                                        WHERE user_id = ?");

        $stmt->bind_param("ssi", $driver_license, $driver_license_img, $user_id);
        $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Update driver field by user_id
     * @param String $field User field want to update
     * @param String $user_id User id
     */
    public function updateDriverField($user_id, $field, $value) {
        $stmt = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                                        WHERE TABLE_SCHEMA = 'rs' AND TABLE_NAME = 'driver'");
        if ($stmt->execute()) {
            $fields = $stmt->get_result();
        }

        $fieldIsExitInTable = false;

        while ($row = $fields->fetch_assoc()) {
                if ($row['COLUMN_NAME'] == $field) {
                    $fieldIsExitInTable = true;
                    break;
                }           
        }

        if ($fieldIsExitInTable) {
            $stmt = $this->conn->prepare("UPDATE driver set ".$field." = ? WHERE user_id = ?");
            $stmt->bind_param("ss", $value, $user_id);
            $stmt->execute();

            $num_affected_rows = $stmt->affected_rows;

            $stmt->close();
            return $num_affected_rows > 0;
        } else {
            return false;
        }
    }

    /**
     * Delete driver
     * @param String $user_id id of user
     */
    public function deleteDriver($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM driver WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
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
    private function isDriverExists($user_id) {
        $stmt = $this->conn->prepare("SELECT user_id from driver WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }



    //not finished yet
    /**
     * Fetching all itineraries
     */
    public function getAllDrivers() {
        $q = "SELECT * FROM driver";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
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
    public function createItinerary($driver_id, $start_address, $start_address_lat,$start_address_long,
             $end_address, $end_address_lat, $end_address_long, $time, $description, $distance) {
        $q = "INSERT INTO itinerary(customer_id, start_address, start_address_lat, start_address_long, 
            end_address, end_address_lat, end_address_long, time, description, distance, status) ";
                $q .= " VALUES(?,?,?,?,?,?,?,?,?,?,". ITINERARY_STATUS_CREATED.")";
        $stmt = $this->conn->prepare($q);
		
        $stmt->bind_param("isddsddssd",
            $driver_id, $start_address, $start_address_lat, $start_address_long, 
            $end_address, $end_address_lat, $end_address_long, $time, $description, $distance);
        
        $result = $stmt->execute();
        $stmt->close();
        //echo $end_address;
        if ($result) {
            $new_itinerary_id = $this->conn->insert_id;
            
            // Itinerary successfully inserted
            return $new_itinerary_id;
            
        } else {
            echo $q;
            return NULL;
        }

        // Check for successful insertion
        if ($result) {
            // Itinerary successfully inserted
            return ITINERARY_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create itinerary
            return ITINERARY_CREATE_FAILED;
        }

    }

    public function createSimpleItinerary($customer_id, $driver_id, $start_address, $start_address_lat, $start_address_long){
        $q = "INSERT INTO itinerary(customer_id, driver_id, start_address, start_address_lat, start_address_long, status) ";
        $q .= " VALUES(?,?,?,?,?,". ITINERARY_STATUS_ONGOING.")";

        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("iisdd", $customer_id, $driver_id,$start_address, $start_address_lat, $start_address_long);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $new_itinerary_id = $this->conn->insert_id;
            
            // Itinerary successfully inserted
            return $new_itinerary_id;
            
        } else {
            //echo $q;
            return NULL;
        }
        // Check for successful insertion
        if ($result) {
            // Itinerary successfully inserted
            return ITINERARY_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create itinerary
            return ITINERARY_CREATE_FAILED;
        }
        
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
                $time, $distance, $description, $status, $created_at);
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
            $res["time"] = $time;
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
        $q = "SELECT * FROM itinerary, driver, user WHERE itinerary.driver_id = driver.user_id AND driver.user_id = user.user_id";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $itineraries = $stmt->get_result();
        $stmt->close();
        return $itineraries;
    }

    public function getAverageRatingofDriver($driver_id){
        $q = "SELECT AVG(rating) AS average_rating FROM rating WHERE driver_id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param("i",$driver_id);
        $stmt->execute();

        $stmt->bind_result($average_rating);
            $stmt->close();

        if($average_rating == null){
            return 0;
        } else {
            return $average_rating;
        }
    }

    //not finished yet
    /**
     * Fetching all itineraries of one driver
     * @param Integer $driver_id id of the driver
     */
    public function getDriverItineraries($driver_id, $order) {
        $q = "SELECT itinerary_id, i.driver_id, i.customer_id, start_address, start_address_lat, start_address_long,
            end_address, end_address_lat, end_address_long, leave_date, duration, distance, cost, description, i.status as itinerary_status, i.created_at,
            driver_license, driver_license_img, u.user_id, u.email, u.fullname, u.phone, personalID, link_avatar ";
        $q .=    "FROM itinerary as i, driver as d, user as u ";
        $q .=     "WHERE i.driver_id = d.user_id AND d.user_id = u.user_id AND driver_id = ? ";

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
        /*$q = "SELECT itinerary_id, i.driver_id, i.customer_id, start_address, start_address_lat, start_address_long,
            end_address, end_address_lat, end_address_long, leave_date, duration, distance, cost, description, i.status as itnerary_status, i.created_at,
            driver_license, driver_license_img, u.user_id, u.email, u.fullname, u.phone, personalID, link_avatar ";
        $q .=    "FROM itinerary as i, driver as d, user as u ";
        $q .=     "WHERE i.driver_id = d.user_id AND d.user_id = u.user_id AND customer_id = ? ";*/
        //echo $customer_id;
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

    //not finished yet
    /**
     * Updating itinerary before accept
     * @param Integer $task_id id of the task
     * @param String $task task text
     * @param String $status task status
     */
    public function updateItinerary($itinerary_id) {
        $q = "UPDATE itinerary set start_address = ?, end_address = ?, leave_day = ?, duration = ?, cost = ?, description = ? 
                WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare();
        $stmt->bind_param("sssidsi", $itinerary_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
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

        //print_r($nq);
        
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function checkItineraryStatus($itinerary_id){
        $q = "SELECT status FROM itinerary WHERE itinerary = ?";
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
    public function updateCustomerAcceptedItinerary($itinerary_id, $customer_id) {
        //ITINERARY_STATUS_CUSTOMER_ACCEPTED
        $q = "UPDATE itinerary set customer_id = ?, status = 2 
                WHERE itinerary_id = ?";
        $stmt = $this->conn->prepare($q);
        echo $customer_id;
        $stmt->bind_param("ii",$customer_id, $itinerary_id);
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
    public function updateCustomerRejectedItinerary($itinerary_id) {
        $q = "UPDATE itinerary set customer_id = null, status = 1 
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
    public function updateDriverAcceptedItinerary($itinerary_id) {
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
     * Updating rejected itinerary by driver
     * @param Aray $itinerary_fields properties of the itinerary
     * @param Integer $itinerary_id id of the itinerary
     */
    public function updateDrivereRectedItinerary($itinerary_id) {
        $q = "UPDATE itinerary set customer_id = null, status = 1 
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

    /* ------------- Feedback table ------------------ */

    public function createFeedback($email, $name, $content) {
        $sql_query = "INSERT INTO feedback(email, name, content) values(?, ?, ?)";

        // insert query
        if ($stmt = $this->conn->prepare($sql_query)) {
            $stmt->bind_param("sss", $email, $name, $content);
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
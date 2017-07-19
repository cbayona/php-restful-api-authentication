<?php
/**
 * Created by PhpStorm.
 * User: m1nk1m
 * Date: 2017-07-19
 * Time: 12:12 PM
 */


class UserService
{
    private $conn = null;


    /**
     * Constructor
     */
    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Login with credentials provided
     */
    public function login($email, $password)
    {
        $hashAndSalt = password_hash($password, PASSWORD_BCRYPT);

        $query = $this->conn->prepare("SELECT * FROM Users WHERE email=? AND password=?");
        $query->bindParam(1, $email, PDO::PARAM_STR);
        $query->bindParam(2, $password, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // result found && password verified?
        if($query->rowCount() > 0 && password_verify($result['password'], $hashAndSalt)) {

            if(!$this->update_last_logged($result['id'])) {

                // failure to update the login log
                return false;
            }

            // success
            return [
                'id' => $result['id'],
                'firstname' => $result['firstname'],
                'lastname' => $result['lastname']
            ];
        }

        // login failure
        return false;
    }


    /**
     * Updates timestamp in the last_logged column when logging in successfully
     */
    protected function update_last_logged($user_id)
    {
        $date = date('Y-m-d H:i:s');
        $query = $this->conn->prepare("UPDATE Users SET last_logged=? WHERE id =?");
        $query->bindParam(1, $date, PDO::PARAM_STR);
        $query->bindParam(2, $user_id, PDO::PARAM_INT);
        return $query->execute();
    }


    /**
     * Finds a user row by the user id and sets
     */
    public function find_by_id($id)
    {
        $query = $this->conn->prepare("SELECT * EXCEPT password FROM Users WHERE id='{$id}'");
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        if($query->rowCount() > 0) {
//            $this->set_user_info($result);
            return $result;
        }
        return false;
    }


    /**
     * Finds a user row by the user id and sets
     */
    public function already_exists($email)
    {
        $query = $this->conn->prepare("SELECT * FROM Users WHERE email='{$email}'");
        $query->execute();
        // already exists
        if($query->rowCount() > 0) {
            return true;
        }
        // doesn't exist
        return false;
    }
    public function create_user($firstname, $lastname, $dob, $email, $password)
    {
        if ($this->already_exists($email)) {
            return false;
        }
        $created_at = date('Y-m-d H:i:s');;
        $query = $this->conn->prepare("INSERT INTO Users SET 
                                                firstname=:firstname, 
                                                lastname=:lastname, 
                                                dob=:dob, 
                                                email=:email, 
                                                password=:password, 
                                                created_at=:created_at");
        $query->bindParam(":firstname", $firstname);
        $query->bindParam(":lastname", $lastname);
        $query->bindParam(":dob", $dob);
        $query->bindParam(":email", $email);
        $query->bindParam(":password", $password);
        $query->bindParam(":created_at", $created_at);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        if($query->rowCount() > 0) {
//            $this->set_user_info($result);
            return true;
        }
        return false;
    }
    public function update_user($user_id)
    {
        //TODO: user update implementation
    }
    protected function delete()
    {
        //TODO: user update implementation
    }
}
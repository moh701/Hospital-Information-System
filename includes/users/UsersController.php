<?php
include "../../includes/DBconn.php";

//$_SESSION['try'] = 0;
if (isset($_POST['type'])) :
    session_start();
    if(!isset($_SESSION['try'])){
        $_SESSION['try'] = 0;
    }
    $_SESSION['try'] += 1;
    $user = new User();
    switch ($_POST['type']) {

        case "login":
            echo $user->login($_POST['email'], $_POST['password'], $_POST['vercode']);
            break;
        case "addUser":
            echo $user->addUser($_POST['in_Email'], $_POST['in_Password'], $_POST['in_Role']);
            break;
    }
endif;


class User
{

    function login($username, $password, $vercode)
    {

//        echo $_SESSION['try'];
        $try = $_SESSION['try'];


        if ($try <= 6) {
            $_SESSION['last_login'] = time();
            $db = new DBconnection();
            $dbConn = $db->getConnection();

            $salt = "vmi^+rq<pe+5>";
            $hashedPass = sha1($password.$salt);


            $query = $dbConn->query("SELECT email, password, role
                        FROM users
                        WHERE email = '$username'
                        AND password = '$hashedPass' LIMIT 1");

            if ($_SESSION['vercode'] == $vercode) {
                if ($query->rowCount() > 0) {
                    $result = $query->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['email'] = $username;
                    $_SESSION['role'] = $result['role'];
                    return "success";
                } else {
                    return "Username or Password Error";
                }
            }
            return "Captcha code in invalid";

        } else {

            if (($_SESSION['last_login'] + 10) < time()) {
                $_SESSION['try'] = 0;
                $try = 0;
            }

            return "You should wait for 30 seconds";


        }

    }

    public function addUser($email, $password, $role)
    {
        $db = new DBconnection();
        $dbConn = $db->getConnection();
        $sql = "INSERT INTO users (email,password,role) VALUES (:email,:password,:role)";
        $query = $dbConn->prepare($sql);

        $salt = "vmi^+rq<pe+5>";
        $hashedPass = sha1($password.$salt);

        $query->bindparam(':email', $email);
        $query->bindparam(':password', $hashedPass );
        $query->bindparam(':role', $role);
        try {
            $query->execute();
            return "success";
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }


    function viewAllUsers()
    {
        $db = new DBconnection();
        $dbConn = $db->getConnection();
        return $dbConn->query("SELECT * FROM users ORDER BY user_id DESC");
    }
}

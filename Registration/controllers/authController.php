<?php

session_start();
require 'config/db.php';


$errors = array();
$username = "";
$email = "";

// jeśli użytnik klikną w guzik rejestracji
if (isset($_POST['signup-btn'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_2 = $_POST['password_2'];

    //walidacja 
    if (empty($username)) {
        $errors['username'] = 'Nazwa użytkownika jest wymagana';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email jest błędny';
    }

    if (empty($email)) {
        $errors['email'] = 'Email jest wymagany';
    }

    if (empty($password)) {
        $errors['password'] = 'Hasło jest wymagane';
    }
    if ($password !== $password_2) {
        $errors['password'] = 'Hasła nie powtarzają się';
    }

    $emailQuery = 'SELECT * FROM users WHERE email=? LIMIT 1';
    $stmt = $conn->prepare($emailQuery);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $userCount = $result->num_rows;
    $stmt->close();

    if ($userCount > 0) {
        $errors['email'] = 'Email już zajęty';
    }

    if (count($errors) === 0) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(50));
        $verified = false;

        $sql = 'INSERT INTO users (username, email, verified, token, password) VALUES (?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssbss', $username, $email, $verified, $token, $password);

        if ($stmt->execute()) {
            // logowanie użytnika
            $user_id = $conn->insert_id;
            $_SESSION['id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['verified'] = $verified;
            //flash message
            $_SESSION['message'] = "Zostałeś Zalogowany!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();

        } else {
            $errors['db_error'] = "Bład bazy danych: Nie udało się zarejestrować";
        }
    }
}
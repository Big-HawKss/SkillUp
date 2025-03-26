<?php
session_start(); 
require_once("../include/config.php"); 
$titre = SITE_NAME . ' - connexion/inscription';
$db = new PDO($dsn, DB_USER, DB_PASS); //connexion à la db
$message = ""; // Variable pour stocker les messages d'erreur ou de succès

function registration() {
    global $db, $message;
    $name = $_POST['name'];
    $firstName = $_POST['firstName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmedPassword = $_POST['cPassword'];

    if(!empty($name) && !empty($firstName) && !empty($email) && !empty($password) && !empty($confirmedPassword)) {
        if (!(strlen($name) > 40)) {
            if(!(strlen($firstName) > 15)){
                if(!(strlen($email) > 255)){
                    if(!(strlen($password) > 255)){
                        if ($password == $confirmedPassword) {
                            $currentDateTime = date('Y-m-d H:i:s');
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $sql = "INSERT INTO Utilisateurs (nom, prenom, e_mail, mot_de_passe, date_creation) 
                                    VALUES (:nom, :prenom, :email, :mot_de_passe, :date_creation)";
                            $request = $db->prepare($sql);
                            $request->bindParam(':nom', $name);
                            $request->bindParam(':prenom', $firstName);
                            $request->bindParam(':email', $email);
                            $request->bindParam(':mot_de_passe', $hashedPassword);
                            $request->bindParam(':date_creation', $currentDateTime);
                            $request->execute();
                        } else {
                            $message = "<p>Les mots de passes ne correspondent pas</p>";
                        }
                    }
                    else{
                        $message = "<p>Le mot de passe ne peut contenir que 255 au maximum</p>";
                    }
                }
                else{
                    $message = "<p>Le nom ne peut contenir que 250 caractères au maximum</p>";
                }
            }
            else{
                $message = "<p>Le prénom ne peut contenir que 15 caractères au maximum</p>";
            }
    
        } else {
            $message = "<p>Le nom ne peut contenir que 40 caractères au maximum</p>";
        }
    }
    else{
        $message = "<p>Vous devez remplir tout les champs</p>";
    }
}

function connection() {
    global $db, $message;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT id, nom, prenom, mot_de_passe FROM Utilisateurs WHERE e_mail = :email";
    $request = $db->prepare($sql);
    $request->bindParam(':email', $email);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);

    if (($result) && password_verify($password, $result['mot_de_passe'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_name'] = $result['nom'];
        $_SESSION['user_first_name'] = $result['prenom'];
        $_SESSION['user_email'] = $email;
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "<p>Email ou mot de passe incorrect !</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['connectionForm'])) {
        connection();
    } elseif (isset($_POST['registrationForm'])) {
        registration();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../<?php echo CSS_PATH; ?>/connection.css">
    <title><?php echo $titre; ?></title>
</head>
<body>
    <div class="background">
        <img src="../<?php echo IMG_PATH; ?>/skillup-logo.svg" alt="background" id="img-top"/>
        <img src="../<?php echo IMG_PATH; ?>/skillup-logo2.svg" alt="background" id="img-bottom"/>
    </div>
    <div class="content">
        <?php if ($message): ?>
                    <div class="message" class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <section class="form">
            <div class="toggle">
                <button id="login-btn" class="isSelected" onclick="toggleForm(false)">Connexion</button>
                <button onclick="toggleForm(true)" id="register-btn">Inscription</button>
            </div>
            <form id="login-form" class="isSelected" method="POST" action="connection.php">
                <label for="email">Email</label>
                <input type="email" id="email" maxlength="255" name="email">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" maxlength="255" name="password"/>
                <button type="submit" name="connectionForm">Connexion</button>
                <a href="">mot de passe oublié</a>
            </form>
            <form id="register-form" method="POST">
                <div class="mini-inputs">
                    <div class="input-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" name="name" maxlength="40"/>
                    </div>
                    <div class="input-group">
                        <label for="firstName">Prénom</label>
                        <input type="text" id="firstName" name="firstName" maxlength="15"/>
                    </div>
                </div>
                <label for="email">Email</label>
                <input type="email" id="email" maxlength="255" name="email">
                <label for="password" >Mot de passe</label>
                <input type="password" id="password" maxlength="255" name="password"/>
                <label for="check-pass">Confirmation</label>
                <input type="password" id="check-pass" maxlength="255" name="cPassword"/>
                <button type="submit" name="registrationForm">Inscription</button>
            </form>
        </section>
    </div>
</body>
<script src="../<?php echo JS_PATH; ?>/forms.js"></script>
</html>
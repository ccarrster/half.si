<?php
date_default_timezone_set("UTC");
?>
<!DOCTYPE html>
<html>
<head>
<title>half.si</title>
<script src="jquery-3.1.0.js"></script>
<link rel="icon" href="halfsi.png">
</head>
<body>
half.si
<img src="halfsi.png">
<?php


$dbaddress = "127.0.0.1";
$dbuser = "root";
$dbpassword = "F3ckth1s";
$dbschema = "halfsi";
$link = mysqli_connect($dbaddress, $dbuser, $dbpassword, $dbschema);

require_once 'php_mailer/class.phpmailer.php';
if(isset($_GET['verify'])){
    $updateQuery = "UPDATE request SET status = 'verified', statustimestamp = CURRENT_TIMESTAMP where verifyid = '".mysqli_real_escape_string($link, $_GET['verify'])."' and status = 'created';";
    $updateResult = mysqli_query($link, $updateQuery);
    if($updateResult === true){
        $rows =  mysqli_affected_rows($link);
        if($rows == 1){
            //TODO get the email address first, before doing the update
            $getEmailQuery = "SELECT email FROM request WHERE verifyid = '".mysqli_real_escape_string($link, $_GET['verify'])."' LIMIT 1;";
            $emailResults = mysqli_query($link, $getEmailQuery);
            if($emailResults !== false){
                while($requestRow = mysqli_fetch_array($emailResults)) {
                    $lookUpEmail = $requestRow['email'];
                    echo("<div>your half.si request is now added. You will recieve emails with offers. we sent you another email with links to remove your half.si request.</div>");
                    $result = sendEmailMessage("manage your half.si request", "to delete your half.si request click <a href='http://half.si?delete=".$_GET['verify']."'>half.si?delete=".$_GET['verify']."</a>.", $lookUpEmail);
                }
            } else {
                echo("error loading your half.si request email address.");
            }
        } else {
            //TODO should have a real answer for this one
            echo("could not verify a half.si request with that verify id. it could already be verified or not exist.");
        }
    } else {
        echo("error trying to verify half.si request.");
        error_log("Error with query ".$updateQuery);
    }
} elseif(isset($_POST['description']) && isset($_POST['quantity']) && isset($_POST['price']) && isset($_POST['email'])){
    $verifyId = uniqid("hsr", true);
    $query = "INSERT INTO request (description, quantity, price, email, verifyid) VALUES('".mysqli_real_escape_string($link, $_POST['description'])."', '".mysqli_real_escape_string($link, $_POST['quantity'])."', '".mysqli_real_escape_string($link, $_POST['price'])."', '".mysqli_real_escape_string($link, $_POST['email'])."', '".mysqli_real_escape_string($link, $verifyId)."');";
    $queryResult = mysqli_query($link, $query);
    if($queryResult === true){
        $result = sendEmailMessage("verify this half.si request", "someone posted a half.si request about a ".$_POST['description'].". if this was you, click <a href='http://half.si?verify=".$verifyId."'>half.si?verify=".$verifyId."</a>. if it was not you, you can just ignore this email.", $_POST['email']);
        if($result === true){
        echo("<div>we just send you an email, to finish your half.si request, verify it by clicking a link on the email.<div>");
        } else {
            echo("<div>we just tried to send you an email, to finish your half.si request, but we failed.<div>");
            error_log("Error sending email ".$_POST['email']);
        }
    } else {
        echo("<div>we could not save your half.si request. Sorry.</div>");
        error_log("Error running ".$query);
    }
} elseif(isset($_GET['delete'])){
    $updateQuery = "UPDATE request SET status = 'removed', statustimestamp = CURRENT_TIMESTAMP where verifyid = '".mysqli_real_escape_string($link, $_GET['delete'])."';";
    $updateResult = mysqli_query($link, $updateQuery);
    if($updateResult === true){
        $rows =  mysqli_affected_rows($link);
        if($rows == 1){
            echo("half.si request deleted");
        } else {
            echo("could not find a matching half.si request to delete");
        }
    } else {
        echo("error deleting half.si request");
        error_log("Error deleting ".$updateQuery);
    }
} else {
    if(isset($_POST['id']) && isset($_POST['email'])){
        $getEmailQuery = "SELECT email, description FROM request WHERE id = '".mysqli_real_escape_string($link, $_POST['id'])."' LIMIT 1;";
        $emailResults = mysqli_query($link, $getEmailQuery);
        if($emailResults !== false){
            while($requestRow = mysqli_fetch_array($emailResults)) {
                $lookUpEmail = $requestRow['email'];
                echo("<div>you want to go half.si on it.</div>");
                $result = sendEmailMessage("want to go half.si?", $_POST['email']." wants to go half.si on ".$requestRow['description']." and says ".$_POST['comments'], $lookUpEmail);
            }
        } else {
            echo("error loading their half.si request email address.");
        }
    }
    echo('<form method="post"><div>create a half.si request</div><div>description <input name="description" type="text"></div><div>quantity <input name="quantity" type="text"></div><div>price <input name="price" type="text"></div><div>email <input name="email" type="text">(private)</div><div><input type="submit" value="create half.si request"></div></form><div>current half.si requests</div><hr>');
    $getRequestsQuery = "SELECT id, description, quantity, price FROM request WHERE status = 'verified';";
    $requestResults = mysqli_query($link, $getRequestsQuery);
    if($requestResults !== false){
        while($requestRow = mysqli_fetch_array($requestResults)) {
            $id = $requestRow['id'];
            $description = $requestRow['description'];
            $quantity = $requestRow['quantity'];
            $price = $requestRow['price'];
            echo("<div><div>description ".$description."</div><div>quantity ".$quantity."</div><div>price ".$price."</div><div><form method='post'>email <input type='text' name='email'></div><div>comments <input type='text' name='comments'></div><div><input type='submit' value='go half.si on it'></div><input type='hidden' name='id' value='".$id."'></form></div></div><hr>");
        }
    } else {
        echo("error loading requests.");
        error_log("Error loading requests ".$getRequestsQuery);
    }
}
function sendEmailMessage($subject, $message, $email){
    
    $body['body'] = $message;
    $body['alt'] = $message;

    $subject = $subject;
    $addressemail = "halfsipost@gmail.com";

    $mail = new PHPMailer;
    $mail->From = $addressemail;
    $mail->FromName = 'half.si';
    $mail->AddAddress($email, $email);
    $mail->AddReplyTo($addressemail, 'Do Not Reply');

    $mail->WordWrap = 50;
    $mail->IsHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body['body'];
    $mail->AltBody = $body['alt'];

    $mail->IsSMTP();
    $mail->Host       = "mail.gmail.com";
    $mail->SMTPDebug  = 0;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "ssl";
    $mail->Host       = "smtp.gmail.com";
    $mail->Port       = 465;
    $mail->Username   = "halfsipost@gmail.com";
    $mail->Password   = "ipittythefool";
    $mailResult = $mail->Send();
    return $mailResult;
}
mysqli_close($link);
?>
<div>feedback/support halfsipost@gmail.com</div>
</body>
</html>
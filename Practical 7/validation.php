<?php
$nameErr="";
$emailErr="";
$mobileErr ="";
if (isset($_POST['submit'])){
    if(empty($_POST["name"])){
        $nameErr="name is required";

    }
    else{
        $name = filterdata($_POST["name"]);
        if(!preg_match("/^[a-zA-Z-']*$/",$name)){
            $nameErr =" only letters and white space allowed";
        }
    }

    if(empty($_POST["email"])){
        $emailErr="email  is required";
    }
        else{
            $email = filterdata($_POST["email"]);
            if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $emailErr = "Invalid Email Format";
        }
    }
        if(empty($_POST["phone"])){
            $mobileErr="Mobile is required";
            }
            else {
                $mobile = filterdata($_POST["phone"]);
                if(!preg_match("/^[0-9]{10}+$/",$mobile)){
                    $mobileErr = "Invalid  Format";
                }
            }
        }
function filterdata($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<html>
    <head>
    <title>form validation</title>
</head>
<body>
    <div class="container">
        <form method="post">
            <input type="text" name="name" placeholder="enter your name" value=<?php if(isset($_POST['name'])){echo $_POST['name'];}?>><br><br>
            <p><?php echo $nameErr;?></p>
            <input type="email" name="email" placeholder="enter your email" value="<?php if(isset($_POST['email'])) {echo $_POST['email'];}?>"><br><br>
            <p><?php echo $emailErr;?><p>
            <input type="phone" name="phone" placeholder="enter your phone" value="<?php if(isset($_POST['phone'])) {echo $_POST['phone'];}?>"><br><br>
            <p><?php echo $mobileErr;?><p>
            <input type="password" name="password" placeholder="*********"><br><br>
            <input type="submit" name="submit" value="SUBMIT"><br>
</form>
</div>
</body>
</html>
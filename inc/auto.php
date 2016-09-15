<head>
    <link href="stl.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Autorization</title>
</head>
<body>
   <?php if(!$_GET['reg']) { ?>
    <div class='genMod autoGen'>
        <div class='listName autoTitle'>Autorization</div>
        <div class='newListName'>
            <form method="post" action="inc/oper.php">
                <input class="newListNameInputTxt autorization" type="text" placeholder="Please enter your email" name="email"><br>
                <input class="newListNameInputTxt autorization" type="password" placeholder="Please enter your password" name="pass"><br>
                <input class="AddTaskBut" type="submit" value="login">
            </form>
        </div>
        <a href="auto.php?reg=1" title="Registration">Registration</a>
    </div>
    <?php } else { ?>
    
     <div class='genMod autoGen'>
        <div class='listName autoTitle'>Registration</div>
        <div class='newListName'>
            <form method="post" action="inc/oper.php">
                <input class="newListNameInputTxt autorization" type="text" placeholder="Please enter your email" name="r_email"><br>
                <input class="newListNameInputTxt autorization" type="password" placeholder="Please enter your password" name="r_pass1"><br>
                <input class="newListNameInputTxt autorization" type="password" placeholder="Please enter your password again" name="r_pass2"><br>
                <input class="AddTaskBut" type="submit" value="ok">
            </form>
            <?php if($_GET['reg'] == 2) echo "<span class='pass'>Your data of passwod shoud coincide twice</span>"; ?>
        </div>
    </div>
    
    <?php } ?>
    
</body>
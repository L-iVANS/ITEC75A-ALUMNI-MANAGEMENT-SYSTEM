<?php
    // Database configuration
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "alumni_management_system";

    // Create connection
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start session
    session_start();


    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_username']) && isset($_POST['log_password'])) {
        $login_identifier = $_POST['log_username'];
        $password = $_POST['log_password'];

        // Check in users table
        $user = check_login($conn, 'alumni', $login_identifier, $password);
        $user_type = 'alumni';

        // Check in admin table if not found in users
        if (!$user) {
            $user = check_login($conn, 'admin', $login_identifier, $password);
            $user_type = 'admin';
        }

        // Check in moderators table if not found in users and admin
        if (!$user) {
            $user = check_login($conn, 'coordinator', $login_identifier, $password);
            $user_type = 'coordinator';
        }

        if ($user) {
            // Login success, set session variables
            switch ($user_type) {
                case 'alumni':
                    $_SESSION['user_id'] = $user['student_id'];
                    break;
                case 'admin':
                    $_SESSION['user_id'] = $user['admin_id'];
                    break;
                case 'coordinator':
                    $_SESSION['user_id'] = $user['coor_id'];
                    break;
            }
            $_SESSION['name'] = $user["fname"] . " " . $user["mname"] . " " . $user["lname"];
            $_SESSION['username'] = $user['username'];
            $_SESSION['password'] = $user['password'];
            $_SESSION['email'] = $user['email'];

            if($user_type == 'admin'){
                // Redirect to a ADMIN DASHBOARD
                echo "
                    <script>
                        alert('Login Successfully');
                        window.location.href = '../adminPage/dashboard_admin.php';
                    </script>
                ";
            }elseif($user_type == 'coordinator'){
                // Redirect to COORDINATOR
                echo "
                    <script>
                        alert('Login Successfully');
                        window.location.href = '../coordinatorPage/dashboard_coor.php';
                    </script>
                ";

            }else{
                // Redirect to ALUMNI DASHBOARD
                echo "
                    <script>
                        alert('Login Successfully');
                        window.location.href = '../alumniPage/dashboard_user.php';
                    </script>
                ";

            }
            
        } else {
            // Login failed
            echo "
                <script>
                    alert('Invalid Username/Email and Password');
                    window.location.href = 'login.php';
                </script>
            ";
        } 
    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        $fname = ucwords($_POST['fname']);
        $mname = ucwords($_POST['mname']);
        $lname = ucwords($_POST['lname']);
        $gender = ucwords($_POST['gender']);
        $course = $_POST['course'];
        $fromYear = $_POST['startYear'];
        $toYear = $_POST['endYear'];
        $connected_to = ucwords($_POST['connected_to']);
        $contact = $_POST['contact'];
        $address = ucwords($_POST['address']);
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        $filePath = '../assets/profile_icon.jpg';
        // Read the image file into a variable
        $imageData = file_get_contents($filePath);
        // Escape special characters (optional, depends on usage)
        $imageDataEscaped = addslashes($imageData);

        $sql = "INSERT INTO alumni SET fname='$fname', mname='$mname', lname='$lname', gender='$gender', course='$course', batch_startYear='$fromYear', batch_endYear='$toYear', connected_to='$connected_to', contact='$contact', address='$address', email='$email', username='$username', password='$password', picture='$imageDataEscaped'";
        $result = $conn->query($sql);
        echo
            "
            <script>
                alert('Alumni Added Successfully');
                window.location.href = 'login.php';
            </script>
        ";
    }

    // Function to check login
    function check_login($conn, $table, $identifier, $password) {
        // Prepare the SQL query
        $sql = "SELECT * FROM $table WHERE (username = ? OR email = ?) AND password = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        // Bind the identifier (username or email) and password parameters to the query
        $stmt->bind_param("sss", $identifier, $identifier, $password);

            // Execute the query
            $stmt->execute();

        // Get the result set from the query
        $result = $stmt->get_result();

        // Check if a matching row was found
        if ($result->num_rows > 0) {
            // Fetch the row as an associative array
            return $result->fetch_assoc();
        }

        // Return false if no match was found
        return false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in || Sign up form</title>
    <!-- font awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="cvsu.png" type="image/svg+xml">
    <!-- css stylesheet -->
    <link rel="stylesheet" href="css/login.css">
    
</head>
<body>

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="#" method="POST">
                <h1>Sign Up</h1>
                
                <div class="infield">
                    <input type="text" placeholder="Username" name="username" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="email" placeholder="Email" name="email" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" placeholder="Password" name="password" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="First Name" name="fname" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Middle Name" name="mname" />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Last Name" name="lname" required/>
                    <label></label>
                </div>
                <div class="infield">
                        <select name="gender" id="gender"  required>
                            <option value="" selected hidden disabled>SELECT A GENDER</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                </div>
                <div class="infield">
                        <select name="course" id="course"  required>
                            <option value="" selected hidden disabled>SELECT A COURSE</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSCS">BSCS</option>
                            <option value="BSAB">BSAB</option>
                            <option value="BSTM">BSTM</option>
                        </select>
                </div>
                <div class="infield">
                        <select name="startYear" id="startYear" required>
                            <option value="" selected hidden disabled>Batch: From Year</option>
                            <?php
                                // Get the current year
                                $currentYear = date('Y');
                                
                                // Number of years to include before and after the current year
                                $yearRange = 50; // Adjust this number as needed
                                
                                // Generate options for years, from current year minus $yearRange to current year plus $yearRange
                                for ($year = $currentYear - $yearRange; $year <= $currentYear + $yearRange; $year++) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="infield">
                        <select name="endYear" id="endYear" required>
                            <option value="" selected hidden disabled>Batch: To Year</option>
                            <?php
                                // Get the current year
                                $currentYear = date('Y');
                                
                                // Number of years to include before and after the current year
                                $yearRange = 50; // Adjust this number as needed
                                
                                // Generate options for years, from current year minus $yearRange to current year plus $yearRange
                                for ($year = $currentYear - $yearRange; $year <= $currentYear + $yearRange; $year++) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                            ?>
                        </select>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Currently connected to" name="connected_to" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="number" placeholder="Contact" name="contact" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Address" name="address" required/>
                    <label></label>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>
        <div class="form-container log-in-container">
            <form action="#" method="POST">
                <h1>Log in</h1>
                <div class="infield">
                    <input type="text" placeholder="Username or Email" name="log_username" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" placeholder="Password" name="log_password" required/>
                    <label></label>
                </div>
                <a href="#" class="forgot">Forgot your password?</a>
                <button>Log In</button>
            </form>
        </div>
        <div class="overlay-container" id="overlayCon">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <img src="cvsu.png" usemap="#logo">
                    <map name="logo">
                        <area shape="poly" coords="101,8,200,106,129,182,73,182,1,110" href="../homepage.php">
                    </map>
                    <br>
                    <br>
                    <button class="ghost" id="logIn">Log In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <img src="cvsu.png" usemap="#logo">
                    <map name="logo">
                        <area shape="poly" coords="101,8,200,106,129,182,73,182,1,110" href="../homepage.php">
                    </map>
                    <br>
                    <br>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <!-- js code -->
    <!-- <script>
        const signUpButton = document.getElementById('signUp');
        const logInButton = document.getElementById('logIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });

        logInButton.addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });
    </script> -->

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const signUpButton = document.getElementById('signUp');
            const logInButton = document.getElementById('logIn');
            const container = document.getElementById('container');

            // Function to read URL parameters
            function getQueryParams() {
                const params = {};
                window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
                    params[key] = value;
                });
                return params;
            }

            // Check URL parameters and activate the appropriate tab
            const params = getQueryParams();
            if (params.tab === 'signup') {
                container.classList.add('right-panel-active');
            } else if (params.tab === 'login') {
                container.classList.remove('right-panel-active');
            }

            signUpButton.addEventListener('click', () => {
                container.classList.add('right-panel-active');
            });

            logInButton.addEventListener('click', () => {
                container.classList.remove('right-panel-active');
            });
        });
    </script>                            

</body>
</html>

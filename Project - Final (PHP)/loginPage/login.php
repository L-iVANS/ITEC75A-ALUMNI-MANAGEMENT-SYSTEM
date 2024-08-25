<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "alumni_management_system";
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    $account = $_SESSION['user_id'];
    $account_email = $_SESSION['user_email'];

    // Check if user is an admin
    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is an admin
        header('Location: ../adminPage/dashboard_admin.php');
        exit();
    }
    $stmt->close();

    // Check if user is a coordinator
    $stmt = $conn->prepare("SELECT * FROM coordinator WHERE coor_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is a coordinator
        header('Location: ../coordinatorPage/dashboard_coor.php');
        exit();
    }
    $stmt->close();

    // Check if user is an alumni
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is an alumni
        header('Location: ../alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();

    header('Location: ./login.php');
    exit();
}


$stud_id = "";
$fname = "";
$mname = "";
$lname = "";
$gender = "";
$course = "";
$fromYear = "";
$toYear = "";
$contact = "";
$address = "";
$email = "";
$username = "";
$log_email = "";
$pass = "";
$password = "";
$confirm_password = "";



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_email']) && isset($_POST['log_password'])) {
    $log_email = strtolower($_POST['log_email']);
    $pass = $_POST['log_password'];

    // Check in users table
    $user = check_alumni($conn, 'alumni', $log_email, $pass);
    $user_type = 'alumni';

    // Check in admin table if not found in users
    if (!$user) {
        $user = check_login($conn, 'admin', $log_email, $pass);
        $user_type = 'admin';
    }

    if (!$user) {
        $user = check_login($conn, 'coordinator', $log_email, $pass);
        $user_type = 'coordinator';
    }

    if (!$user) {
        $user = check_alumni($conn, 'pending', $log_email, $pass);
        $user_type = 'pending';
    }

    if (!$user) {
        $user = check_alumni($conn, 'alumni_archive', $log_email, $pass);
        $user_type = 'alumni_arc';
    }

    if (!$user) {
        $user = check_alumni($conn, 'declined_account', $log_email, $pass);
        $user_type = 'declined_account';
    }

    if ($user) {
        // Login success, set session variables
        switch ($user_type) {
            case 'alumni':
                $_SESSION['user_id'] = $user['alumni_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
            case 'admin':
                $_SESSION['user_id'] = $user['admin_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
            case 'coordinator':
                $_SESSION['user_id'] = $user['coor_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
        }

        if ($user_type == 'admin') {
            // Redirect to a ADMIN DASHBOARD
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Login Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = '../adminPage/dashboard_admin.php';
                    });
                });
            </script>
            ";
        } else if ($user_type == 'coordinator') {
            // Redirect to COORDINATOR
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Login Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                         window.location.href = '../coordinatorPage/dashboard_coor.php';
                    });
                });
            </script>
            ";
        } else if ($user_type == 'pending') {
            // PENDING ACCOUNT
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Your Account Is Under Review!',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";
        } else if ($user_type == 'alumni_arc') {
            // ARCHIED ACCOUNT
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Your Account Is Suspended!... If you Think it was Mistaken, Please Contact Adminitrator.',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";
        } else if ($user_type == 'declined_account') {
            // DECLINED ACCOUNT
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Your Application Had Been Declined',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";

            // Check if new password and confirm password match
        } else {
            // Redirect to ALUMNI DASHBOARD
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Login Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = '../alumniPage/dashboard_user.php';
                    });
                });
            </script>
            ";
        }
    } else {
        // Login failed
        echo "
        <script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Incorrect Student ID / Email and Password',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $stud_id = $_POST['student_id'];
    $fname = ucwords($_POST['fname']);
    $mname = ucwords($_POST['mname']);
    $lname = ucwords($_POST['lname']);
    $gender = ucwords($_POST['gender']);
    $course = $_POST['course'];
    $fromYear = $_POST['startYear'];
    $toYear = $_POST['endYear'];
    $contact = $_POST['contact'];
    $address = ucwords($_POST['address']);
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // email and user existing check
    $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email'");
    $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email'");
    $idCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE student_id='$stud_id'");
    $idCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE student_id='$stud_id'");

    // email and user existing check
    $emailCheck_pending = mysqli_query($conn, "SELECT * FROM pending WHERE email='$email'");
    $emailCheck_decline = mysqli_query($conn, "SELECT * FROM declined_account WHERE email='$email'");
    $idCheck_pending = mysqli_query($conn, "SELECT * FROM pending WHERE student_id='$stud_id'");
    $idCheck_decline = mysqli_query($conn, "SELECT * FROM declined_account WHERE student_id='$stud_id'");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


    if (mysqli_num_rows($emailCheck) > 0) {
        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Email Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
    } else if (mysqli_num_rows($emailCheck_archive) > 0) {
        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Email Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
    } else if (mysqli_num_rows($idCheck) > 0) {
        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Student ID Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
    } else if (mysqli_num_rows($idCheck_archive) > 0) {
        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Student ID Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
    } else if (mysqli_num_rows($emailCheck_pending) > 0) {
        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Email Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
    } else {

        // Check if new password and confirm password match
        if ($password !== $confirm_password) {
            // $errorMessage = "New password and confirm password do not match.";
            echo "<script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Password Does Not Match!',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";
        } else if (mysqli_num_rows($emailCheck_decline) > 0) {

            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Email Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
        } else if (mysqli_num_rows($idCheck_pending) > 0) {
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Student ID Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
            if (strlen($stud_id) > 10 || !ctype_digit($stud_id)) {
                echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Student ID must be a number and cannot exceed 10 digits',
                        timer: 2000,
                        showConfirmButton: true, 
                        confirmButtonColor: '#4CAF50', 
                        confirmButtonText: 'OK' 
                    });
                });
            </script>
        ";
            }
        } else if (mysqli_num_rows($idCheck_decline) > 0) {
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Student ID Already Exists',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
        ";
        } else {


            $filePath = '../assets/profile_icon.jpg';
            $imageData = file_get_contents($filePath);
            $imageDataEscaped = addslashes($imageData);

            $sql = "INSERT INTO pending SET student_id='$stud_id', fname='$fname', mname='$mname', lname='$lname', gender='$gender', course='$course', batch_startYear='$fromYear', batch_endYear='$toYear', contact='$contact', address='$address', email='$email', password='$password', picture='$imageDataEscaped'";
            $result = $conn->query($sql);

            if ($result) {
                // $successMessage = "Coordinator Edited Successfully";
                echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Account Successfully Registered',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './login.php';
                    });
                });
            </script>
            ";
            }
        }
    }
}

// LOGIN CHECK FOR ADMIN AND COORDINATOR
function check_login($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE email = ? AND password = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $log_email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return false;
}

function check_alumni($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE (student_id = ? OR email = ?) AND password = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $log_email, $log_email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="#" method="POST">
                <h1>Sign Up</h1>
                <div class="alert alert-danger text-center error-list" id="real-time-errors"></div>
                <div class="infield">
                    <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Password" id="password" name="password" onkeyup="validatePassword()" value="<?php echo htmlspecialchars($password); ?>" min="0" required />
                    <img id="togglePassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('password', 'togglePassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>

                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Confirm Password" id="confirm_password" onkeyup="validatePassword()" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" required />
                    <img id="toggleConfirmPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" id="student_id" placeholder="Student ID" maxlength="9" required pattern="\d{9}" title="Student ID must be exactly 9 digits" name="student_id" value="<?php echo htmlspecialchars($stud_id); ?>" />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="number" placeholder="First Name" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Middle Name" name="mname" value="<?php echo htmlspecialchars($mname); ?>" />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Last Name" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <select name="gender" id="gender" required>
                        <option value="" selected hidden disabled>Select a Gender</option>
                        <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="infield">
                    <select class="form-control" name="course" id="course" required>
                        <option value="" selected hidden disabled>Select a course</option>
                        <option value="BAJ" <?php echo ($course == 'BAJ') ? 'selected' : ''; ?>>BAJ</option>
                        <option value="BECEd" <?php echo ($course == 'BECEd') ? 'selected' : ''; ?>>BECEd</option>
                        <option value="BEEd" <?php echo ($course == 'BEEd') ? 'selected' : ''; ?>>BEEd</option>
                        <option value="BSBM" <?php echo ($course == 'BSBM') ? 'selected' : ''; ?>>BSBM</option>
                        <option value="BSOA" <?php echo ($course == 'BSOA') ? 'selected' : ''; ?>>BSOA</option>
                        <option value="BSEntrep" <?php echo ($course == 'BSEntrep') ? 'selected' : ''; ?>>BSEntrep</option>
                        <option value="BSHM" <?php echo ($course == 'BSHM') ? 'selected' : ''; ?>>BSHM</option>
                        <option value="BSIT" <?php echo ($course == 'BSIT') ? 'selected' : ''; ?>>BSIT</option>
                        <option value="BSCS" <?php echo ($course == 'BSCS') ? 'selected' : ''; ?>>BSCS</option>
                        <option value="BSc(Psych)" <?php echo ($course == 'BSc(Psych)') ? 'selected' : ''; ?>>BSc(Psych)</option>
                    </select>
                </div>
                <div class="infield">
                    <select class="form-control" name="startYear" id="startYear" required>
                        <option value="" selected hidden disabled>Batch: From Year</option>
                        <?php
                        // Get the current year
                        $currentYear = date('Y');

                        // Number of years to include before and after the current year
                        $yearRange = 21; // Adjust this number as needed

                        // Preserve the selected value after form submission
                        $selectedYear = isset($_POST['startYear']) ? $_POST['startYear'] : '';

                        // Generate options for years, from current year minus $yearRange to current year plus $yearRange
                        for ($year = $currentYear - $yearRange; $year <= $currentYear + $yearRange; $year++) {
                            $selected = ($year == $selectedYear) ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="infield">
                    <select class="form-control" name="endYear" id="endYear" required data-selected="<?php echo isset($_POST['endYear']) ? $_POST['endYear'] : ''; ?>">
                        <option value="" selected hidden disabled>Batch: To Year</option>
                        <?php
                        if (isset($_POST['startYear'])) {
                            $startYear = $_POST['startYear'];
                            $selectedEndYear = isset($_POST['endYear']) ? $_POST['endYear'] : '';

                            // Generate options for endYear starting from startYear + 1
                            for ($year = $startYear + 1; $year <= $currentYear + $yearRange; $year++) {
                                $selected = ($year == $selectedEndYear) ? 'selected' : '';
                                echo "<option value=\"$year\" $selected>$year</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="infield">
                    <input type="number" placeholder="Contact" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Address" name="address" value="<?php echo htmlspecialchars($address); ?>" required />
                    <label></label>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>
        <div class="form-container log-in-container">
            <form action="#" method="POST">
                <h1>Log in</h1>
                <div class="infield">
                    <input type="text" placeholder="Student ID / Email" name="log_email" required />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Password" id="log_password" name="log_password" required style="padding-right: 30px;" />
                    <img id="toggleLogPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('log_password', 'toggleLogPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <!-- <a href="#" class="forgot">Forgot your password?</a> -->
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
        function togglePasswordVisibility(inputId, toggleId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = document.getElementById(toggleId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.src = 'eye-open.png'; // assuming you have this icon for visibility
            } else {
                passwordInput.type = 'password';
                toggleButton.src = 'eye-close.png';
            }
        }

        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errors = [];
            if (password.length < 8) {
                errors.push('Password must be at least 8 characters long.');
            }
            if (!/[A-Z]/.test(password)) {
                errors.push('Password must contain at least one uppercase letter.');
            }
            if (!/[a-z]/.test(password)) {
                errors.push('Password must contain at least one lowercase letter.');
            }
            if (!/\d/.test(password)) {
                errors.push('Password must contain at least one digit.');
            }
            if (!/[!@#$%^&*]/.test(password)) {
                errors.push('Password must contain at least one special character.');
            }
            if (password !== confirmPassword) {
                errors.push('Passwords do not match.');
            }
            if (errors.length > 0) {
                document.getElementById('real-time-errors').innerHTML = errors.join('<br>');
                return false;
            }
            document.getElementById('real-time-errors').innerHTML = '';
            return true;
        }

        function validateForm(form) {
            const studentId = document.getElementById('student_id').value;
            if (studentId.length !== 9 || !/^\d{9}$/.test(studentId)) {
                alert('Student ID must be exactly 9 digits.');
                return false;
            }

            return validatePassword();
        }

        // Handle dynamic end year population
        document.getElementById('startYear').addEventListener('change', function () {
            const startYear = parseInt(this.value);
            const endYearSelect = document.getElementById('endYear');
            endYearSelect.innerHTML = '<option value="" selected hidden disabled>Batch: To Year</option>';
            for (let year = startYear + 1; year <= new Date().getFullYear() + 21; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === parseInt(endYearSelect.getAttribute('data-selected'))) {
                    option.selected = true;
                }
                endYearSelect.appendChild(option);
            }
        });

        // Initialize dynamic end year selection based on the current state
        if (document.getElementById('startYear').value) {
            const event = new Event('change');
            document.getElementById('startYear').dispatchEvent(event);
        }
    </script>
    <!-- JS for form transitions -->
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>
</body>

</html>
<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "alumni_management_system";
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check user session and type
if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    $account = $_SESSION['user_id'];
    $account_email = $_SESSION['user_email'];

    // Check if user is an admin
    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
    }
    $stmt->close();

    // Check if user is a coordinator
    $stmt = $conn->prepare("SELECT * FROM coordinator WHERE coor_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        header('Location: ../../coordinatorPage/dashboard_coor.php');
        exit();
    }
    $stmt->close();

    // Check if user is an alumni
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        header('Location: ../../alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();
} else {
    header('Location: ../../homepage.php');
    exit();
}

// Pagination configuration
$records_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $records_per_page;

// Get event_id from URL parameter
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Get search query if provided
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

// Initialize SQL query
$sql = "SELECT alumni.alumni_id, 
alumni.student_id, 
alumni.fname, 
alumni.mname, 
alumni.lname, 
alumni.gender, 
alumni.course, 
CONCAT(alumni.batch_startYear, '-', alumni.batch_endYear) 
AS batch, 
alumni.contact, 
alumni.address, 
alumni.email 
FROM alumni 
INNER JOIN event_choice 
ON alumni.alumni_id = event_choice.alumni_id
WHERE event_choice.event_choice = 'Going'
AND event_choice.event_id = ?";

if (!empty($search_query)) {
    $sql .= " AND (student_id LIKE ? 
                     OR fname LIKE ? 
                     OR mname LIKE ? 
                     OR lname LIKE ?
                     OR address LIKE ?
                     OR email LIKE ? 
                     OR course LIKE ?
                     OR CONCAT(alumni.batch_startYear, ' - ', alumni.batch_endYear) LIKE ?
                     OR contact LIKE ? 
                     OR gender = ?)";
}
$sql .= " ORDER BY student_id ASC LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($search_query)) {
    $search_wildcard = "%$search_query%";
    $stmt->bind_param(
        'issssssssiii', 
        $event_id, 
        $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard,
        $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard,
        strtolower($search_query), 
        $start_from, $records_per_page
    );
} else {
    $stmt->bind_param('iii', $event_id, $start_from, $records_per_page);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total number of records
$total_records_query = "
    SELECT COUNT(*) FROM alumni 
    INNER JOIN event_choice ON alumni.alumni_id = event_choice.alumni_id 
    WHERE event_choice.event_choice = 'Going' 
    AND event_choice.event_id = ?";

if (!empty($search_query)) {
    $total_records_query .= " AND (student_id LIKE ? 
                                     OR fname LIKE ? 
                                     OR mname LIKE ? 
                                     OR lname LIKE ?
                                     OR address LIKE ?
                                     OR email LIKE ? 
                                     OR course LIKE ?
                                     OR CONCAT(alumni.batch_startYear, ' - ', alumni.batch_endYear) LIKE ?
                                     OR contact LIKE ? 
                                     OR gender = ?)";
}

$total_records_stmt = $conn->prepare($total_records_query);

if (!empty($search_query)) {
    $total_records_stmt->bind_param(
        'isssssssssi',
        $event_id,
        $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard,
        $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard,
        strtolower($search_query)
    );
} else {
    $total_records_stmt->bind_param('i', $event_id);
}

$total_records_stmt->execute();
$total_records_result = $total_records_stmt->get_result();
$total_records_row = $total_records_result->fetch_array();
$total_records = $total_records_row[0];
$total_pages = ceil($total_records / $records_per_page);

// Close the statement
$stmt->close();
$total_records_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Alumni List</title>
    <link rel="stylesheet" href="./css/alumni.css">
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script>
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FOR PAGINATION -->
    <style>
        /*  DESIGN FOR SEARCH BAR AND PAGINATION */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            text-align: left;
        }

        .inline {
            border: 1px solid #dddddd;
            padding: 8px;
            font-size: 12px;
            white-space: nowrap;
            /* Prevent text from wrapping */
            overflow: hidden;
            /* Hide overflowing content */
            text-overflow: ellipsis;
            /* Display ellipsis for truncated text */
            max-width: 125px;
            /* Set a max-width to control truncation */

        }

        .act {
            max-width: 235px;
            text-align: center;
            /* Set a max-width to control truncation */
        }

        th {
            background-color: #368DB8;
            font-weight: bold;
            text-align: center;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;

        }

        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #f1f1f1;
            color: black;
            border: 1px solid #ccc;
            margin-right: 5px;
            /* Added margin to separate buttons */
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }

        .pagination .prev :hover {
            float: left;

            /* Float left for "Previous" link */
        }


        .pagination .next {
            float: right;
            /* Float right for "Next" link */
        }
    </style>

</head>

<body>
    <input type="checkbox" id="menu-toggle">
    <div class="sidebar">
        <div class="side-header">
            <h3><img src="https://cvsu-imus.edu.ph/student-portal/assets/images/logo-mobile.png"></img><span>CVSU</span></h3>
        </div>

        <div class="side-content">
            <div class="profile">
                <i class="bi bi-person-circle"></i>
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
                <!-- <h4>ADMIN</h4>
                <small style="color: white;">admin@email.com</small> -->
            </div>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="../dashboard_admin.php">
                            <span class="las la-home" style="color:#fff"></span>
                            <small>DASHBOARD</small>
                        </a>
                    </li>
                    <li>
                        <a href="../profile/profile.php">
                            <span class="las la-user-alt" style="color:#fff"></span>
                            <small>PROFILE</small>
                        </a>
                    </li>
                    <li>
                        <a href="../alumni/alumni.php" >
                            <span class="las la-th-list" style="color:#fff"></span>
                            <small>ALUMNI</small>
                        </a>
                    </li>
                    <li>
                        <a href="../coordinator/coordinator.php">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
                        </a>
                    </li>
                    <li>
                        <a href="../event/event.php" class="active">
                            <span class="las la-calendar" style="color:#fff"></span>
                            <small>EVENT</small>
                        </a>
                    </li>
                    <li>
                        <a href="../settings/about.php">
                            <span class="las la-cog" style="color:#fff"></span>
                            <small>SETTINGS</small>
                        </a>
                    </li>
                    <li>
                        <a href="../report/report.php">
                            <span class="las la-clipboard-check" style="color:#fff"></span>
                            <small>REPORT</small>
                        </a>
                    </li>
                    <li>
                        <a href="../archive/alumni_archive.php">
                            <span class="las la-archive" style="color:#fff"></span>
                            <small>ARCHIVE</small>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">

        <header>
            <div class="header-content">
                <label for="menu-toggle">
                    <span class="las la-bars bars" style="color: white;"></span>
                </label>

                <div class="header-menu">
                    <label for="">
                    </label>

                    <div class="user">


                        <a href="../logout.php">
                            <span class="las la-power-off" style="font-size: 30px; border-left: 1px solid #fff; padding-left:10px; color:#fff"></span>
                        </a>

                    </div>
                </div>
            </div>
        </header>


        <main>
            <div class="page-header">
                <h1><strong>Alumni</strong></h1>
            </div>

            <div class="container-fluid" id="main-container">
                <div class="container-fluid" id="content-container">
                    <div class="container-title">
                        <h3>Attending in the Event</h3>
                    </div>
                    <div class="congainer-fluid" id="column-header">
                        <div class="row">
                            <div class="col">
                                <div class="search">

                                    <form class="d-flex" role="search">
                                        <div class="container-fluid" id="search">
                                            <input class="form-control me-2" type="search" name="query" placeholder="Search Records..." aria-label="Search" value="<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>">
                                            <button class="btn btn-outline-success" type="submit" style="padding-left: 30px; padding-right: 39px;">Search</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                            <div class="col" style="text-align: end;">
                                <div class="add-button">
                                    <!-- <a style="text-decoration: none;" href='./add_alumni.php'>
                                        <button id="add-new-btn">Add New +</button>
                                    </a> -->
                                    <a class='btn btn-secondary border border-dark' href='./event.php' style="margin-left: 1%; padding-left: 4.1px; padding-right: 5.4px; white-space: nowrap;">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-content">
                        <table id="example" class="table-responsive table table-striped table-hover ">
                            <thead>

                                <tr>
                                    <th scope="col" class="inline">STUDENT ID</th>
                                    <th scope="col" class="inline">NAME</th>
                                    <th scope="col" class="inline">GENDER</th>
                                    <th scope="col" class="inline">COURSE</th>
                                    <th scope="col" class="inline">BATCH</th>
                                    <th scope="col" class="inline">CONTACT</th>
                                    <th scope="col" class="inline">ADDRESS</th>
                                    <th scope="col" class="inline">EMAIL</th>
                                    <!-- <th scope="col" class="inline">DATE CREATION</th> -->
                                    <!-- <th scope="col" class="inline">ACTION</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $fullname = $row["fname"] . " " . $row["mname"] . " " . $row["lname"];
                                        $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
                                ?>
                                        <tr>
                                            <td class="inline"><?php echo $row['student_id'] ?></td>
                                            <td class="inline"><?php echo htmlspecialchars($fullname) ?></td>
                                            <td class="inline"><?php echo $row['gender'] ?></td>
                                            <td class="inline"><?php echo $row['course'] ?></td>
                                            <td class="inline"><?php echo htmlspecialchars($batch) ?></td>
                                            <td class="inline"><?php echo $row['contact'] ?></td>
                                            <td class="inline"><?php echo $row['address'] ?></td>
                                            <td class="inline"><?php echo $row['email'] ?></td>
                                            <!-- <td class="inline"><?php echo $row['date_created'] ?></td> -->
                                           
                                <?php
                                    }
                                } else {
                                    $current_page = 0;
                                    echo '<tr><td colspan="12" style="text-align: center;">No records found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>

                    </div>

                    <div>
                        <!-- Pagination links -->
                        <div class="pagination" id="content" style="float:right; margin-right:1.5%">
                            <!-- next and previous -->
                            <?php
                            if ($current_page > 1) : ?>
                                <a href="?page=<?= ($current_page - 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="prev" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">&laquo; Previous</a>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages) : ?>
                                <a href="?page=<?= ($current_page + 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="next" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                        <p style="margin-left:2%;margin-top:2.3%;">Page <?= $current_page ?> out of <?= $total_pages ?></p>
                    </div>
                </div>
            </div>
            <!-- <div class="container-fluid" id="main-container">
                <div class="container-fluid" id="content-container">
                    
                </div>
            </div> -->
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', (event) => {
                let currentPage = 1;

                function loadPage(page) {
                    // Simulate an AJAX request to get page content
                    const contentDiv = document.getElementById('content');
                    contentDiv.innerHTML = Content for page ${page}; // Replace with actual AJAX call
                    currentPage = page;
                }

                document.getElementById('prevPage').addEventListener('click', (event) => {
                    event.preventDefault();
                    if (currentPage > 1) {
                        loadPage(currentPage - 1);
                    }
                });

                document.getElementById('nextPage').addEventListener('click', (event) => {
                    event.preventDefault();
                    loadPage(currentPage + 1);
                });

                // Initial load
                loadPage(currentPage);
            });

            // forsweetalert confirm
            // Debugging: Ensure SweetAlert2 is loaded
            document.addEventListener('DOMContentLoaded', function() {
                const archiveButtons = document.querySelectorAll('.archive');

                archiveButtons.forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.preventDefault(); // Prevent the default action (navigation)

                        const href = this.getAttribute('href'); // Get the href attribute

                        Swal.fire({
                            title: 'Do you want to continue?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e03444',
                            cancelButtonColor: '#ffc404',
                            confirmButtonText: 'Continue'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = href; // Proceed with the navigation if confirmed
                            }
                        });
                    });
                });
            });
        </script>
</body>

</html>
<!-- Rest of your HTML and pagination code -->

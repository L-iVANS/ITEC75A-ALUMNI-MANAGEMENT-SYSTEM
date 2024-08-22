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
        $user = $user_result->fetch_assoc();
    }
    $stmt->close();

    // Check if user is a coordinator
    $stmt = $conn->prepare("SELECT * FROM coordinator WHERE coor_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is a coordinator
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
        // User is an alumni
        header('Location: ../../alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();
} else {
    header('Location: ../../homepage.php');
    exit();
}

// Pagination configuration
$records_per_page = 10; // Number of records to display per page
$current_page = isset($_GET['page']) ? $_GET['page'] : 1; // Get current page number, default to 1

// Calculate the limit clause for SQL query
$start_from = ($current_page - 1) * $records_per_page;

// Sorting configuration
$sort_order = "ASC"; // Default sort order

if (isset($_GET['sort'])) {
    $sort_order = $_GET['sort'] == "desc" ? "DESC" : "ASC";
}

// Initialize variables
$sql = "SELECT * FROM alumni ";

// Check if search query is provided
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    // Modify SQL query to include search filter
    $sql .= "WHERE student_id LIKE '%$search_query%' 
            OR fname LIKE '%$search_query%' 
            OR mname LIKE '%$search_query%' 
            OR lname LIKE '%$search_query%'
            OR address LIKE '%$search_query%'
            OR email LIKE '%$search_query%' 
            OR course LIKE '%$search_query%'
            OR CONCAT(batch_startYear, ' - ', batch_endYear) LIKE '%$search_query%'
            OR date_created LIKE '%$search_query%'
            OR contact LIKE '%$search_query%' ";

    if (strtolower($search_query) === 'male' || strtolower($search_query) === 'female') {
        $sql .= "OR gender = '$search_query' ";
    }
}
// Apply sorting
// Default sorting column
$sort_column = "lname"; // Default sort by last name

// Check if a specific column is set for sorting
if (isset($_GET['column'])) {
    $sort_column = $_GET['column'];
}

// Apply sorting
$sql .= "ORDER BY $sort_column $sort_order ";
// Apply pagination
$sql .= "LIMIT $start_from, $records_per_page";


// $sql .= "ORDER BY student_id ASC ";

$result = $conn->query($sql);

// Count total number of records
$total_records_query = "SELECT COUNT(*) FROM alumni";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $total_records_query .= " WHERE alumni_id LIKE '%$search_query%' 
                              OR fname LIKE '%$search_query%' 
                              OR mname LIKE '%$search_query%' 
                              OR lname LIKE '%$search_query%' 
                              OR address LIKE '%$search_query%'
                              OR email LIKE '%$search_query%' 
                              OR course LIKE '%$search_query%'
                              OR CONCAT(batch_startYear, ' - ', batch_endYear) LIKE '%$search_query%'
                              OR date_created LIKE '%$search_query%'
                              OR contact LIKE '%$search_query%'
                               ";


    if (strtolower($search_query) === 'male' || strtolower($search_query) === 'female') {
        $sql .= "OR gender = '$search_query' ";
    }
}




$total_records_result = mysqli_query($conn, $total_records_query);
$total_records_row = mysqli_fetch_array($total_records_result);
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);



if (isset($_GET['ide'])) {
    echo "
        <script>
        // Wait for the document to load
        document.addEventListener('DOMContentLoaded', function() {
            // Use SweetAlert2 for the alert
            Swal.fire({
                title: 'Account Archived Successfully',
                timer: 2000,
                showConfirmButton: true, // Show the confirm button
                confirmButtonColor: '#4CAF50', // Set the button color to green
                confirmButtonText: 'OK' // Change the button text if needed
            });
        });
    </script>
    ";
}
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
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
                        <a href="./alumni.php" class="active">
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
                        <a href="../event/event.php">
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
                        <span>
                            <h2>Alumni Records</h2>
                        </span>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="container" style="margin-bottom:0px; margin-top: 10px;">
                                    <select class="form-control" name="course" id="course-filter" required>
                                        <option value="" selected hidden disabled>Select a course</option>
                                        <option value="BAJ">BAJ</option>
                                        <option value="BECEd">BECEd</option>
                                        <option value="BEEd">BEEd</option>
                                        <option value="BSBM">BSBM</option>
                                        <option value="BSOA">BSOA</option>
                                        <option value="BSEntrep">BSEntrep</option>
                                        <option value="BSHM">BSHM</option>
                                        <option value="BSIT">BSIT</option>
                                        <option value="BSCS">BSCS</option>
                                        <option value="BSc(Psych)">BSc(Psych)</option>
                                        <option value="">All Courses</option>
                                    </select>
                                    <br>
                                </div>
                            </div>
                            <div class="col">
                                
                            </div>
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
                                        <a style="text-decoration: none;" href='./add_alumni.php'>
                                            <button id="add-new-btn">Add New +</button>
                                        </a>
                                        <a class='btn btn-secondary border border-dark' href='./pendingAccount/pending.php' style="margin-left: 1%; padding-left: 4.1px; padding-right: 5.4px; white-space: nowrap;">Pending Account</a>
                                        <button id="print-table-btn" class="btn btn-primary">Print Table</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-content">
                            <table id="example" class="table-responsive table table-striped table-hover ">
                                <thead>
                                    <tr>
                                        <th scope="col" class="inline">STUDENT ID</th>
                                        <th scope="col" class="inline">
                                            <a href="?page=<?php echo $current_page; ?>&sort=<?php echo $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&query=<?php echo isset($_GET['query']) ? urlencode($_GET['query']) : ''; ?>&column=lname">
                                                Last Name
                                                <?php if (isset($_GET['column']) && $_GET['column'] == 'lname' && $sort_order == 'ASC'): ?>
                                                    <i class="bi bi-arrow-up"></i>
                                                <?php elseif (isset($_GET['column']) && $_GET['column'] == 'lname' && $sort_order == 'DESC'): ?>
                                                    <i class="bi bi-arrow-down"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th scope="col" class="inline">
                                            <a href="?page=<?php echo $current_page; ?>&sort=<?php echo $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&query=<?php echo isset($_GET['query']) ? urlencode($_GET['query']) : ''; ?>&column=fname">
                                                First Name
                                                <?php if (isset($_GET['column']) && $_GET['column'] == 'fname' && $sort_order == 'ASC'): ?>
                                                    <i class="bi bi-arrow-up"></i>
                                                <?php elseif (isset($_GET['column']) && $_GET['column'] == 'fname' && $sort_order == 'DESC'): ?>
                                                    <i class="bi bi-arrow-down"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th scope="col" class="inline">
                                            <a href="?page=<?php echo $current_page; ?>&sort=<?php echo $sort_order == 'ASC' ? 'desc' : 'asc'; ?>&query=<?php echo isset($_GET['query']) ? urlencode($_GET['query']) : ''; ?>&column=mname">
                                                Middle Name
                                                <?php if (isset($_GET['column']) && $_GET['column'] == 'mname' && $sort_order == 'ASC'): ?>
                                                    <i class="bi bi-arrow-up"></i>
                                                <?php elseif (isset($_GET['column']) && $_GET['column'] == 'mname' && $sort_order == 'DESC'): ?>
                                                    <i class="bi bi-arrow-down"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th scope="col" class="inline">GENDER</th>
                                        <th scope="col" class="inline">COURSE</th>
                                        <th scope="col" class="inline">BATCH</th>
                                        <th scope="col" class="inline">CONTACT</th>
                                        <th scope="col" class="inline">ADDRESS</th>
                                        <th scope="col" class="inline">EMAIL</th>
                                        <th scope="col" class="inline">DATE CREATION</th>
                                        <th scope="col" class="inline">ACTION</th>
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
                                                <td class="inline"><?php echo $row['lname'] ?></td>
                                                <td class="inline"><?php echo $row['fname'] ?></td>
                                                <td class="inline"><?php echo $row['mname'] ?></td>
                                                <td class="inline"><?php echo $row['gender'] ?></td>
                                                <td class="inline"><?php echo $row['course'] ?></td>
                                                <td class="inline"><?php echo htmlspecialchars($batch) ?></td>
                                                <td class="inline"><?php echo $row['contact'] ?></td>
                                                <td class="inline"><?php echo $row['address'] ?></td>
                                                <td class="inline"><?php echo $row['email'] ?></td>
                                                <td class="inline"><?php echo $row['date_created'] ?></td>
                                                <?php
                                                echo "
                                                <td class='inline act'>
                                                    <a class='btn btn-danger btn-sm archive' href='./del_alumni.php?id=$row[alumni_id]' style='font-size: 11.8px;'>Archive</a>
                                                    <a class='btn btn-outline-primary' href='./alumni_info.php?id=$row[alumni_id]' style='font-size: 11.8px;'>Details</a>
                                                </td>
                                            "; ?>
                                            </tr>
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
                                    <a href="?page=<?= ($current_page + 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="next" style="border-radius:4px;background-color:#f7b205;color:white;margin-bottom:13px;">Next &raquo;</a>
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
        <!-- <div class="container">
            <select class="form-control" name="course" id="course-filter" required>
                <option value="" selected hidden disabled>Select a course</option>
                <option value="BAJ">BAJ</option>
                <option value="BECEd">BECEd</option>
                <option value="BEEd">BEEd</option>
                <option value="BSBM">BSBM</option>
                <option value="BSOA">BSOA</option>
                <option value="BSEntrep">BSEntrep</option>
                <option value="BSHM">BSHM</option>
                <option value="BSIT">BSIT</option>
                <option value="BSCS">BSCS</option>
                <option value="BSc(Psych)">BSc(Psych)</option>
                <option value="">All Courses</option>

            </select> -->

        <script>
            document.addEventListener('DOMContentLoaded', (event) => {
                let currentPage = 1;

                function loadPage(page) {
                    // Simulate an AJAX request to get page content
                    const contentDiv = document.getElementById('content');
                    contentDiv.innerHTML = `Content for page ${page}`; // Replace with actual AJAX call
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // document.getElementById('print-table-btn').addEventListener('click', function() {
                //     var tableContent = document.querySelector('.table-content').innerHTML;

                //     if (!tableContent) {
                //         console.error('No content found for printing');
                //         return; // Exit if no content found
                //     }

                //     var printWindow = window.open('', '_blank', 'height=600,width=800');

                //     if (!printWindow) {
                //         console.error('Popup blocked');
                //         return; // Exit if the popup was blocked
                //     }

                //     printWindow.document.open();
                //     printWindow.document.write('<html><head><title>Print Table</title>');
                //     printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
                //     printWindow.document.write('</head><body>');
                //     printWindow.document.write('<table class="table table-striped table-bordered">');
                //     printWindow.document.write(tableContent);
                //     printWindow.document.write('</table>');
                //     printWindow.document.write('</body></html>');
                //     printWindow.document.close();

                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 1000);
            });
        </script>
        <script>
            document.getElementById('print-table-btn').addEventListener('click', function() {
                // Get the table content element
                var tableContent = document.querySelector('.table-content');

                // Use html2pdf to generate and download the PDF
                var opt = {
                    margin: 1,
                    filename: 'alumni_table.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'legal',
                        orientation: 'landscape'
                    }
                };

                // Convert the table content to PDF
                html2pdf().from(tableContent).set(opt).save();
            });
        </script>

        <script>
            document.getElementById('course-filter').addEventListener('change', function() {
                const selectedCourse = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('table tbody tr');

                tableRows.forEach(row => {
                    const courseCell = row.querySelector('td:nth-child(4)'); // Assuming the course is in the 4th column

                    if (courseCell) {
                        const courseText = courseCell.textContent.toLowerCase();

                        if (selectedCourse === "" || courseText.includes(selectedCourse)) {
                            row.style.display = ''; // Show the row
                        } else {
                            row.style.display = 'none'; // Hide the row
                        }
                    }
                });
            });
        </script>


</body>

</html>
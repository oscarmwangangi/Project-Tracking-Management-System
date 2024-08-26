<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
   
}
// Fetch ongoing projects
$ongoingProjects = $pdo->query("SELECT * FROM projects WHERE end_date IS NULL OR end_date > CURDATE()")->fetchAll(PDO::FETCH_ASSOC);

// Search functionality
$filteredProjects = $ongoingProjects;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $filteredProjects = array_filter($ongoingProjects, function($project) use ($searchQuery) {
        return stripos($project['name'], $searchQuery) !== false; // Case-insensitive search
    });
}
// Handle end date update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_end_date'])) {
    $project_id = $_POST['project_id'];
    $new_end_date = $_POST['new_end_date'];

    // Fetch the current end date
    $stmt = $pdo->prepare("SELECT end_date FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_end_date = $project['end_date'];
    $previous_end_date = $project['previous_end_date'];
     // Check if the end date has already been changed
     

    // Update the end date and store the previous end date
    $stmt = $pdo->prepare("UPDATE projects SET end_date = ?, previous_end_date = ? WHERE id = ?");
    $stmt->execute([$new_end_date, $current_end_date, $project_id]);

    header('Location: addedproject.php');
    exit();
}


// Adding a new step to an existing project
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_step'])) {
    $project_id = $_POST['project_id'];
    $new_step = $_POST['new_step'];
    $image_path = null;

    // Handle image upload if provided
    if (isset($_FILES['step_image']) && $_FILES['step_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        $image_path = $upload_dir . basename($_FILES['step_image']['name']);
        move_uploaded_file($_FILES['step_image']['tmp_name'], $image_path);
    }

    // Fetch existing steps
    $stmt = $pdo->prepare("SELECT steps FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $steps = json_decode($project['steps'], true);

    // Add the new step with the optional image
    $steps[] = ['step' => $new_step, 'completed' => false, 'image' => $image_path];

    // Update the project with the new steps
    $stmt = $pdo->prepare("UPDATE projects SET steps = ? WHERE id = ?");
    $stmt->execute([json_encode($steps), $project_id]);

    header('Location: addedproject.php');
    exit();
}

// Mark a step as complete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_step'])) {
    $project_id = $_POST['project_id'];
    $step_index = $_POST['step_index'];

    // Fetch existing steps
    $stmt = $pdo->prepare("SELECT steps FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $steps = json_decode($project['steps'], true);

    // Mark the step as complete
    $steps[$step_index]['completed'] = true;

    // Update the project with the modified steps
    $stmt = $pdo->prepare("UPDATE projects SET steps = ? WHERE id = ?");
    $stmt->execute([json_encode($steps), $project_id]);

    header('Location: addedproject.php');
    exit();
}
//delete for each step
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_step'])) {
    $project_id = $_POST['project_id'];
    $step_index = $_POST['step_index'];
        // Fetch existing steps
        $stmt = $pdo->prepare("SELECT steps FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        $steps = json_decode($project['steps'], true);
    
        // Remove the step from the array
        array_splice($steps, $step_index, 1);
    
        // Update the project with the modified steps
        $stmt = $pdo->prepare("UPDATE projects SET steps = ? WHERE id = ?");
        $stmt->execute([json_encode($steps), $project_id]);
 
        //echo alert may be removed non functional 
        echo('<script type="text/javascript">
       window.onload = function () { alert("Step deleted successfully"); } 
</script>');
        header('location: addedproject.php');
        exit();
    }
// Upload image for an existing step
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_image'])) {
    $project_id = $_POST['project_id'];
    $step_index = $_POST['step_index'];
    $image_path = null;

    // Handle image upload if provided
    if (isset($_FILES['step_image']) && $_FILES['step_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        $image_path = $upload_dir . basename($_FILES['step_image']['name']);
        if (move_uploaded_file($_FILES['step_image']['tmp_name'], $image_path)) {
            // Fetch existing steps
            $stmt = $pdo->prepare("SELECT steps FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            $steps = json_decode($project['steps'], true);

            // Update the step with the new image
            $steps[$step_index]['image'] = $image_path;

            // Update the project with the modified steps
            $stmt = $pdo->prepare("UPDATE projects SET steps = ? WHERE id = ?");
            $stmt->execute([json_encode($steps), $project_id]);

            header('Location: addedproject.php');
            exit();
        } else {
            echo "Failed to move uploaded file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ongoing Projects</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./styles.css">
    <!-- <script>
        // Save the scroll position before the form is submitted
        function saveScrollPosition() {
            localStorage.setItem('scrollPosition', window.scrollY);
        }

        // Restore the scroll position after the page reloads
        window.onload = function() {
            if (localStorage.getItem('scrollPosition')) {
                window.scrollTo(0, localStorage.getItem('scrollPosition'));
                localStorage.removeItem('scrollPosition');
            }
        };
    </script> -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="addedproject.php">My Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Add New Project</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="completed.php">Completed Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_admin.php">Add new admin</a>
                        </li>
                        <li class="nav-item" id="logout">
                            <a class="nav-link" href="./logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div id="myProjects" class="content-section">
                    <!-- <div class="d-flex flex-column justify-content-center align-items-center p-30 center-content">
                        <img src="../image/court of arms.png" alt="Court of Arms" height="130px" max-width="130px">
                        <div class="h1-wrapper">
                            <span class="line1">Ministry of Information Communications and</span>
                            <span class="line2">The Digital Economy</span>
                        </div>
                        <div class="h2-wrapper">
                            <span class="line1">State Department For ICT and Digital Economy</span>
                        </div>
                        <div class="h3-wrapper">
                            <span class="line1">Directorate of ICT Security and Audit Control</span>
                        </div>
                    </div> -->

                    <form method="GET" action="" class="mb-4" onsubmit="saveScrollPosition();">
                        <div class="form-group">
                            <label for="search">Search by Project Name</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Enter project name" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">


                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                    <ul class="list-group">
                        <?php if (empty($filteredProjects)): ?>
                            <li class="list-group-item">No projects found.</li>
                        <?php else: ?>
                            <?php foreach ($filteredProjects as $project): ?>
                                <li class="list-group-item">
                                    <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                                    <p>Start Date: <?php echo htmlspecialchars($project['start_date']); ?></p>
                                    <p>End Date: 
                                    <?php 
                                    $previous_end_date = $project['previous_end_date'];
                                    if ($previous_end_date == null) {
                                        echo htmlspecialchars($project['end_date']);
                                    
                                    }
                                   else{ echo htmlspecialchars($project['previous_end_date']); 
                                   }
                                    ?>
                                    
                                    
                                    <?php if ($project['previous_end_date']): ?>
                                        <br><small>(The date has been changed to: <?php echo htmlspecialchars($project['end_date']); ?>)</small>
                                    <?php endif; ?>
                                    <button class="btn btn-link btn-sm" data-toggle="collapse" data-target="#editDateForm_<?php echo $project['id']; ?>">Edit</button>
                                </p>
        
                                <!-- Edit End Date Form -->
                                <div id="editDateForm_<?php echo $project['id']; ?>" class="collapse">
                                    <form method="POST" action="addedproject.php">
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <div class="form-group">
                                            <label for="new_end_date_<?php echo $project['id']; ?>">New End Date:</label>
                                            <input type="date" name="new_end_date" id="new_end_date_<?php echo $project['id']; ?>" class="form-control" required>
                                        </div>
                                        <button type="submit" name="update_end_date" class="btn btn-primary">Update End Date</button>
                                    </form>
                                </div>




                                    <h4>Tracking stages:</h4>
                                    <ul>
                                        <?php
                                        $steps = !empty($project['steps']) ? json_decode($project['steps'], true) : [];
                                        foreach ($steps as $index => $step): ?>
                                            <li class="d-flex align-items-center mb-2">
                                                <div class="flex-grow-1"><?php echo htmlspecialchars($step['step']); ?>
                                                    <?php if (!empty($step['image'])): ?>
                                                        <br><img src="<?php echo htmlspecialchars($step['image']); ?>" alt="Step Image" style="max-width: 100px; max-height: 100px; margin-left: 10px;">
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($step['completed']): ?>
                                                    <span class="badge badge-success">Completed</span>
                                                <?php else: ?>
                                                    <form method="POST" action="addedproject.php" class="ml-2" onsubmit="saveScrollPosition();">
                                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                        <input type="hidden" name="step_index" value="<?php echo $index; ?>">
                                                        <button type="submit" name="complete_step" class="btn btn-success btn-sm">Complete</button>
                                                    </form>
                                                <?php endif; ?>

                                                 <!-- Delete Step Button -->
                                                 <form method="POST" onsubmit="return confirm('Are you sure you want to delete this step?');">
                                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                    <input type="hidden" name="step_index" value="<?php echo $index; ?>">
                                                    <button type="submit" name="delete_step" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </li>
                                    
                                                <!-- Upload image form for each step -->
                                                <form method="POST" action="addedproject.php" enctype="multipart/form-data" class="ml-2" onsubmit="return checkFileSize(event, this);">
                                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                <input type="hidden" name="step_index" value="<?php echo $index; ?>">
                                                <div class="form-group mb-0">
                                                    <input type="file" class="form-control-file" name="step_image">
                                                </div>
                                                <button type="submit" name="upload_image" class="btn btn-primary btn-sm">Upload Image</button>
                                            </form>
                                            <hr>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <form method="POST" action="addedproject.php" enctype="multipart/form-data" class="mt-3" onsubmit="saveScrollPosition();">
                                        <div class="form-group">
                                            <label for="new_step">Add New Tracking Stage</label>
                                            <input type="text" class="form-control" name="new_step" placeholder="New stage" required>
                                        </div>
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <button type="submit" name="add_step" class="btn btn-primary">Add New Tracking Stage</button>
                                    </form>
                                    <!-- Delete Button -->
                                    <form method="POST" action="delete_project.php" style="margin-top:10px;" onsubmit="saveScrollPosition();">
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <button type="submit" name="delete_project" class="btn btn-danger">Delete Project</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </main>
        </div>
    </div>
            <!-- Back to Top Icon -->
        <a href="#" id="back-to-top" class="btn btn-primary btn-lg back-to-top" role="button" aria-label="Back to top">
            ↑
        </a>

            <!-- Modal -->
   <!-- Modal -->
   <div class="modal fade" id="fileSizeModal" tabindex="-1" aria-labelledby="fileSizeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileSizeModalLabel">File Size Exceeded</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                The uploaded image is too large. Only images 930 KB or smaller are allowed.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


    <script src="scripts.js" ></script>                                                
    <!-- <script>
// Check the file size before submitting the form
function checkFileSize(event, form) {
    var fileInput = form.querySelector('input[type="file"]');
    if (fileInput.files.length > 0) {
        var fileSize = fileInput.files[0].size / 1024; // size in KB
        if (fileSize > 930) {
            event.preventDefault(); // Prevent form submission
            $('#fileSizeModal').modal('show'); // Show the modal
            return false;
        }
    }
    saveScrollPosition(); // Save scroll position if file size is okay
    return true;
}

// Attach the checkFileSize function to the form submission
document.querySelectorAll('form[onsubmit="saveScrollPosition();"]').forEach(function(form) {
    form.onsubmit = function(event) {
        return checkFileSize(event, this);
    };
});
</script>


    <script>
    // Show or hide the back-to-top button based on scroll position
    window.onscroll = function() {
        var backToTopButton = document.getElementById("back-to-top");
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            backToTopButton.style.display = "block";
        } else {
            backToTopButton.style.display = "none";
        }
    };

    // Scroll smoothly back to the top when the button is clicked
    document.getElementById("back-to-top").onclick = function(event) {
        event.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
</script> -->
</body>
</html>
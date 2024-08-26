<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
   
}
// Adding a new project
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_project'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $num_steps = $_POST['num_steps'];
    $steps = [];

    for ($i = 1; $i <= $num_steps; $i++) {
        $steps[] = ['step' => $_POST["step_$i"], 'completed' => false];
    }

    // Validate dates
    if (strtotime($end_date) <= strtotime($start_date)) {
        $_SESSION['project_status'] = 'invalid_dates';
    } else {
        try {
            // Insert project into the database
            $stmt = $pdo->prepare("INSERT INTO projects (name, description, start_date, end_date, steps) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $start_date, $end_date, json_encode($steps)]);
            $_SESSION['project_status'] = 'success';
        } catch (Exception $e) {
            $_SESSION['project_status'] = 'failure';
        }
    }

    header('Location: admin.php');
    exit();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="addedproject.php">My Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin.php">Add New Project</a>
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
                
                <div id="addProject" class="content-section">
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
                    <span class="line1">Directorate of ICT Seccurity and Audit Control</span>
                    </div>
             </div> -->
                            <h4>Add New Project</h4>
                    <form method="POST" action="admin.php">
                        <div class="form-group">
                            <label for="name">Project Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Project Name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label for="num_steps">Number of Steps</label>
                            <input type="number" class="form-control" name="num_steps" id="num_steps" min="1" required>
                        </div>
                        <div id="stepsContainer"></div>
                        <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Project added successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
            <?php if (isset($_SESSION['project_status'])): ?>
    <script>
        $(document).ready(function() {
            <?php if ($_SESSION['project_status'] == 'success'): ?>
                $('#successModal').modal('show');
            <?php elseif ($_SESSION['project_status'] == 'invalid_dates'): ?>
                alert('End date must be greater than the start date.');
            <?php else: ?>
                $('#failureModal').modal('show');
            <?php endif; ?>
            <?php unset($_SESSION['project_status']); ?>
        });
    </script>
<?php endif; ?>
        </div>
    </div>
    
</div>

<!-- Failure Modal -->
<div class="modal fade" id="failureModal" tabindex="-1" role="dialog" aria-labelledby="failureModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="failureModalLabel">Error</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Failed to add the project. Please try again.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php if (isset($_SESSION['project_status'])): ?>
    <script>
        $(document).ready(function() {
            <?php if ($_SESSION['project_status'] == 'success'): ?>
                $('#successModal').modal('show');
            <?php else: ?>
                $('#failureModal').modal('show');
            <?php endif; ?>
            <?php unset($_SESSION['project_status']); ?>
        });
    </script>
<?php endif; ?>

   
    <script>
        document.getElementById('num_steps').addEventListener('input', function() {
            var numSteps = this.value;
            var stepsContainer = document.getElementById('stepsContainer');
            stepsContainer.innerHTML = ''; // Clear previous steps

            for (var i = 1; i <= numSteps; i++) {
                var stepInput = document.createElement('div');
                stepInput.classList.add('form-group');
                stepInput.innerHTML = `
                    <label for="step_${i}">Step ${i}</label>
                    <input type="text" class="form-control" name="step_${i}" placeholder="Step ${i}" required>
                `;
                stepsContainer.appendChild(stepInput);
            }
        });

        
        
    </script>
</body>
</html>

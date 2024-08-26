<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
   
}
// Initialize search term
$searchTerm = '';

// Check if a search term is set
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchTerm = "%$searchTerm%";

    // Fetch projects matching the search term
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE end_date IS NOT NULL AND end_date <= CURDATE() AND name LIKE ?");
    $stmt->execute([$searchTerm]);
    $completedProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetch all completed projects if no search term is provided
    $completedProjects = $pdo->query("SELECT * FROM projects WHERE end_date IS NOT NULL AND end_date <= CURDATE()")->fetchAll(PDO::FETCH_ASSOC);
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

    header('Location: completed.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Completed Projects</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styles.css">
    <style>
        .step-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .completed {
            font-weight: bold;
            color: green;
        }
        .in-progress {
            font-weight: bold;
            color: red;
        }
    </style>
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
                            <a class="nav-link" href="admin.php">Add New Project</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="completed.php">Completed Projects</a>
                        </li>
                        <li class="nav-item" id="logout">
                            <a class="nav-link" href="./logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <!-- <div id="completedProjects" class="content-section">
                    <div class="d-flex flex-column justify-content-center align-items-center p-30 center-content">
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
                    <!-- Back to Top Icon -->
                    <a href="#" id="back-to-top" class="btn btn-primary btn-lg back-to-top" role="button" aria-label="Back to top">
                        ↑
                    </a>

                    <!-- Search Form -->
                    <form method="GET" action="completed.php" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control mt-4" name="search" placeholder="Search Projects" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <button class="btn btn-primary mt-3" type="submit">Search</button>
                    </form>

                    <h4>Completed Projects</h4>
                    <ul class="list-group">
                        <?php if (!empty($completedProjects)): ?>
                            <?php foreach ($completedProjects as $project): ?>
                                <li class="list-group-item">
                                    <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                                    <p>Start Date: <?php echo htmlspecialchars($project['start_date']); ?></p>
                                    <p>End Date: <?php echo htmlspecialchars($project['end_date']); ?></p>
                                    <h4>Steps:</h4>
                                    <ul>
                                        <?php
                                        $steps = json_decode($project['steps'], true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($steps)) {
                                            foreach ($steps as $index => $step) {
                                                echo '<li class="step-item">';
                                                echo '<span>' . htmlspecialchars($step['step']) . '</span>';
                                                echo '<span>';
                                                if ($step['completed']) {
                                                    echo '<span class="completed">(Completed)</span>';
                                                } else {
                                                    echo '<span class="in-progress">(In Progress)</span>';
                                                    echo '<form method="POST" action="completed.php" style="display:inline;">
                                                              <input type="hidden" name="project_id" value="'.$project['id'].'">
                                                              <input type="hidden" name="step_index" value="'.$index.'">
                                                              <button type="submit" name="complete_step" class="btn btn-success btn-sm" class="step-complete">Complete</button>
                                                          </form>';
                                                }
                                                echo '</span>';
                                                echo '</li>';
                                            }
                                        } else {
                                            echo '<li>No steps available or steps are malformed.</li>';
                                        }
                                        ?>
                                    </ul>
                                    <?php
                                    $allStepsCompleted = true;
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($steps)) {
                                        foreach ($steps as $step) {
                                            if (!$step['completed']) {
                                                $allStepsCompleted = false;
                                                break;
                                            }
                                        }
                                    }
                                    echo '<p class="mt-2 font-weight-bold">' . ($allStepsCompleted ? 'THIS PROJECT HAS BEEN COMPLETED' : 'THIS PROJECT IS DUE DATE AND STILL IN PROGRESS') . '</p>';
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item">No completed projects found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Store scroll position before refreshing the page
        window.onbeforeunload = function() {
            localStorage.setItem('scrollPos', window.scrollY);
        };

        // Restore scroll position after page load
        window.onload = function() {
            if (localStorage.getItem('scrollPos') !== null) {
                window.scrollTo(0, localStorage.getItem('scrollPos'));
                localStorage.removeItem('scrollPos'); // Optional: remove the scroll position after it's used
            }
        };
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
</script>

</body>
</html>


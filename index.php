<?php
session_start();
require 'db.php';

try {
    // Fetch projects
    $projects = $pdo->query("SELECT * FROM projects")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Error handling
    echo 'Error: ' . $e->getMessage();
    exit();
}

function calculateOverdueDays($endDate, $completedPercentage) {
    $currentDate = new DateTime();
    $endDate = new DateTime($endDate);

    if ($endDate < $currentDate && $completedPercentage < 100) {
        $interval = $endDate->diff($currentDate);
        return $interval->days;
    }
    return 0;
}

function calculateOverdueDaysCompleted($endDate, $completionDate) {
    $endDate = new DateTime($endDate);
    $completionDate = new DateTime($completionDate);

    if ($endDate < $completionDate) {
        $interval = $endDate->diff($completionDate);
        return $interval->days;
    }
    return 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Projects</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="index.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>
<body>
    <!-- <div class="d-flex flex-column justify-content-center align-items-center p-30 center-content">
        <img src="./image/court of arms.png" alt="Court of Arms" height="130px" max-width="130px">
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
    <div class="container">
        <form method="GET" action="">
            <div class="form-group">
                <label for="search" class="search">Search by Project Name</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Enter project name" onkeyup="filterProjects()">
            </div>
        </form>

        <div class="project-grid mt-3">
            <?php
            $searchQuery = '';
            if (!empty($_GET['search'])) {
                $searchQuery = $_GET['search'];
                $filteredProjects = array_filter($projects, function($project) use ($searchQuery) {
                    return stripos($project['name'], $searchQuery) !== false;
                });
            } else {
                $filteredProjects = $projects;
            }
            ?>

            <?php if ($filteredProjects): ?>
                <?php foreach ($filteredProjects as $project): ?>
                    <?php
                    // Calculate the completion percentage
                    $steps = !empty($project['steps']) ? json_decode($project['steps'], true) : [];
                    $completedSteps = array_filter($steps, function($step) {
                        return $step['completed'];
                    });
                    $totalSteps = count($steps);
                    $completedPercentage = $totalSteps > 0 ? (count($completedSteps) / $totalSteps) * 100 : 0;

                    // Determine the overdue days
                    $overdueDays = 0;
                    $completionDate = $project['completion_date'] ?? null;

                    if ($completedPercentage == 100 && $completionDate) {
                        $overdueDaysCompleted = calculateOverdueDaysCompleted($project['end_date'], $completionDate);
                    } else {
                        $overdueDays = calculateOverdueDays($project['end_date'], $completedPercentage);
                    }
                    ?>
                    <div class="list-group-item">
                        <h3><?php echo htmlspecialchars($project['name']); ?></h3>

                        <div class="chart-container" onclick="showDetailsInModal(<?php echo $project['id']; ?>,
                            '<?php echo addslashes(htmlspecialchars($project['name'])); ?>')">
                            <canvas id="chart-<?php echo $project['id']; ?>" width="150" height="150"></canvas>
                        </div>

                        <div id="details-<?php echo $project['id']; ?>" class="details">
                            <p><?php echo htmlspecialchars($project['description']); ?></p>
                            <p>Start Date: <?php echo htmlspecialchars($project['start_date']); ?></p>
                            <p>End Date: <?php echo htmlspecialchars($project['end_date']); ?></p>
                            <h4>Steps:</h4>
                            <ul>
                                <?php
                                if ($steps) {
                                    foreach ($steps as $step) {
                                        $statusClass = $step['completed'] ? 'completed' : 'in-progress';
                                        $statusText = $step['completed'] ? 'Completed' : 'In Progress';
                                        $imagePath = !empty($step['image']) ? 'uploads/' . htmlspecialchars($step['image']) : '';
                                        $imageHtml = !empty($imagePath) ? '<img src="' . $imagePath . '" alt="Step Image" class="step-image">' : '';
                                        echo '<li>' . htmlspecialchars($step['step']) . $imageHtml . '<span class="step-status ' . $statusClass . '">' . $statusText . '</span></li>';
                                    }
                                } else {
                                    echo '<li>No steps available</li>';
                                }
                                ?>
                            </ul>

                            <p class="<?php echo $completedPercentage == 100 ? 'completed' : 'in-progress'; ?>">
                                <?php echo $completedPercentage == 100 ? 'THIS PROJECT HAS BEEN COMPLETED' : 'PROJECT STILL IN PROGRESS'; ?></p>

                            <?php if ($completedPercentage == 100 && $completionDate && $overdueDaysCompleted > 0): ?>
                                <p class="overdue">
                                    This project was overdue by <?php echo $overdueDaysCompleted; ?> days.
                                </p>
                            <?php elseif ($overdueDays > 0): ?>
                                <p class="overdue">
                                    This project is overdue by <?php echo $overdueDays; ?> days.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <script>
                        function filterProjects() {
                            var input, filter, projectList, projectItems, projectName, i;
                            input = document.getElementById('search');
                            filter = input.value.toLowerCase();
                            projectList = document.getElementsByClassName('list-group-item');

                            for (i = 0; i < projectList.length; i++) {
                                projectName = projectList[i].getElementsByTagName('h3')[0];
                                if (projectName) {
                                    if (projectName.innerHTML.toLowerCase().indexOf(filter) > -1) {
                                        projectList[i].style.display = "";
                                    } else {
                                        projectList[i].style.display = "none";
                                    }
                                }
                            }
                        }

                        function showDetailsInModal(projectId, projectName) {
                            var detailsDiv = document.getElementById('details-' + projectId);
                            var modalContent = document.getElementById('modalContent');
                            var modalTitle = document.getElementById('detailsModalLabel');

                            modalContent.innerHTML = detailsDiv.innerHTML;
                            modalTitle.textContent = projectName;

                            $('#detailsModal').modal('show');
                        }

                        var ctx = document.getElementById('chart-<?php echo $project['id']; ?>').getContext('2d');
                        var myPieChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Completed (<?php echo round($completedPercentage); ?>%)', 'Incomplete (<?php echo round(100 - $completedPercentage); ?>%)'],
                                datasets: [{
                                    data: [<?php echo $completedPercentage; ?>, <?php echo 100 - $completedPercentage; ?>],
                                    backgroundColor: ['#36A2EB', '#eaeaea'],
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    datalabels: {
                                        display: true,
                                        color: '#000',
                                        font: {
                                            weight: 'bold',
                                            size: 16
                                        },
                                        formatter: function(value, context) {
                                            if (context.dataIndex === 0) {
                                                return value + '%';
                                            } else {
                                                return '';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No projects found.</p>
            <?php endif; ?>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">Project Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalContent">
                        <!-- Content will be dynamically injected here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" id="back-to-top" class="btn btn-primary btn-lg back-to-top" role="button" aria-label="Back to top">
            ↑
        </a>
    </div>
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

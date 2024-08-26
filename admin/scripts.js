function saveScrollPosition() {
    console.log('Saving scroll position:', window.scrollY);
    localStorage.setItem('scrollPosition', window.scrollY);
}

window.onload = function() {
    console.log('Restoring scroll position:', localStorage.getItem('scrollPosition'));
    if (localStorage.getItem('scrollPosition')) {
        window.scrollTo(0, localStorage.getItem('scrollPosition'));
        localStorage.removeItem('scrollPosition');
    }
};

function checkFileSize(event, form) {
    var fileInput = form.querySelector('input[type="file"]');
    if (fileInput.files.length > 0) {
        var fileSize = fileInput.files[0].size / 1024; // size in KB
        console.log('File size:', fileSize);
        if (fileSize > 930) {
            event.preventDefault(); // Prevent form submission
            $('#fileSizeModal').modal('show'); // Show the modal
            return false;
        }
    }
    saveScrollPosition(); // Save scroll position if file size is okay
    return true;
}

document.addEventListener('submit', function(event) {
    var form = event.target;
    if (form.matches('form[onsubmit="saveScrollPosition();"]')) {
        console.log('Form submission detected');
        if (!checkFileSize(event, form)) {
            return;
        }
    }
    saveScrollPosition(); // Save scroll position if form is valid
}, true);
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

// Validate dates before form submission
document.querySelector('form').addEventListener('submit', function(event) {
    var startDate = new Date(document.querySelector('input[name="start_date"]').value);
    var endDate = new Date(document.querySelector('input[name="end_date"]').value);

    if (endDate <= startDate) {
        alert('End date must be greater than the start date.');
        event.preventDefault(); // Prevent form submission
    }
});
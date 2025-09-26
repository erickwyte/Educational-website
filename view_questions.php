<?php
include 'includes/session_check.php';
require 'config.php';


// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $message = "<p class='error'>Please log in to view or save this question.</p>";
}

// Validate request
if (!isset($_GET['id'])) {
    die("Invalid request.");
}
$question_id = intval($_GET['id']);

// Fetch question PDF details
$query = "SELECT * FROM questions_pdfs WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Question query preparation failed: " . $conn->error);
}
$stmt->bind_param('i', $question_id);
$stmt->execute();
$result = $stmt->get_result();
$question = $result->fetch_assoc();
$stmt->close();

if (!$question) {
    die("Question not found.");
}

// Handle file path
$file_path = $question['file_path'];
if (strpos($file_path, '../Uploads/') === 0) {
    $file_path = $_SERVER['DOCUMENT_ROOT'] . str_replace('../', '/', $file_path);
} elseif (strpos($file_path, '/Uploads/') === 0) {
    $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path;
}

if (!file_exists($file_path)) {
    $filename_from_db = basename($question['file_path']);
    $possible_paths = [
        $_SERVER['DOCUMENT_ROOT'] . '/Uploads/' . $filename_from_db,
        $_SERVER['DOCUMENT_ROOT'] . '/Uploads/questions_pdfs/' . $filename_from_db,
        $file_path
    ];
    $found = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $file_path = $path;
            $found = true;
            break;
        }
    }
    if (!$found) {
        error_log("PDF not found: " . $question['file_path']);
        die("Error: File not found. Please check the file path in the database.");
    }
}

$pdf_url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_path);
if (strpos($pdf_url, '/') !== 0) {
    $pdf_url = '/' . $pdf_url;
}

// TRACK VIEW ACTIVITY
if ($user_id) {
    track_activity($user_id, ACTIVITY_VIEW, "Viewed Question: " . $question['title'] . " (ID: " . $question_id . ")");
} else {
    track_guest_activity(ACTIVITY_VIEW, "Viewed Question: " . $question['title'] . " (ID: " . $question_id . ")");
}

// Handle saving question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    if (!$user_id) {
        $message = "<p class='error'>Please log in to save this question.</p>";
    } else {
        $check_sql = "SELECT id FROM user_saved_questions WHERE user_id = ? AND question_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param('ii', $user_id, $question_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "<p class='error'>You have already saved this question.</p>";
        } else {
            $save_sql = "INSERT INTO user_saved_questions (user_id, question_id) VALUES (?, ?)";
            $stmt = $conn->prepare($save_sql);
            $stmt->bind_param('ii', $user_id, $question_id);
            if ($stmt->execute()) {
                $message = "<p class='success'>Question saved successfully!</p>";
                track_activity($user_id, ACTIVITY_LIKE, "Saved Question: " . $question['title'] . " (ID: " . $question_id . ")");
            } else {
                $message = "<p class='error'>Error saving question: " . $stmt->error . "</p>";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Question - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/view_questions.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="empty"></div>
    <div class="viewer-container">
        <div class="pdf-header">
            <h1 class="pdf-title"><i class="fas fa-question-circle"></i> <?php echo htmlspecialchars($question['title']); ?></h1>
            <div class="pdf-meta"></div>
        </div>
        <?php if (isset($message)) echo $message; ?>
        <div id="error" class="error-pdf" style="display:none;"></div>
        <div id="pdf-container"></div>
    </div>
    <div class="save-btn-container">
        <form method="post">
            <button type="submit" class="save-btn" name="save_question">
                <i class="fas fa-bookmark"></i> Save to Profile
            </button>
        </form>
    </div>
    <div class="scroll-to-top" id="scrollToTop">↑</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        let pdfDoc = null, scale = 1.5, totalPages = 0, renderedPages = 0, devicePixelRatio = window.devicePixelRatio || 1;
        const scrollToTopBtn = document.getElementById('scrollToTop');
        const pdfUrl = "<?php echo htmlspecialchars($pdf_url); ?>";
        const container = document.getElementById('pdf-container');
        const errorElement = document.getElementById('error');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) scrollToTopBtn.classList.add('visible');
            else scrollToTopBtn.classList.remove('visible');
        });
        scrollToTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.rendered) {
                    const pageNumber = parseInt(entry.target.dataset.page);
                    renderPage(pageNumber, entry.target);
                    entry.target.dataset.rendered = "true";
                }
            });
        }, { rootMargin: "200px 0px" });

        function setupPageContainers() {
            for (let pageNumber = 1; pageNumber <= totalPages; pageNumber++) {
                const pageContainer = document.createElement('div');
                pageContainer.className = 'page-wrapper';
                pageContainer.dataset.page = pageNumber;
                container.appendChild(pageContainer);
                observer.observe(pageContainer);
            }
        }

        function renderPage(pageNumber, containerElement) {
            pdfDoc.getPage(pageNumber).then(page => {
                const viewport = page.getViewport({ scale: scale * devicePixelRatio });
                const canvas = document.createElement('canvas');
                canvas.className = 'page-canvas';
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                const context = canvas.getContext('2d');
                page.render({ canvasContext: context, viewport }).promise.then(() => {
                    if (devicePixelRatio > 1) {
                        canvas.style.width = (viewport.width / devicePixelRatio) + 'px';
                        canvas.style.height = (viewport.height / devicePixelRatio) + 'px';
                    }
                    const pageNumberDiv = document.createElement('div');
                    pageNumberDiv.className = 'page-number';
                    pageNumberDiv.textContent = `Page ${pageNumber} of ${totalPages}`;
                    containerElement.innerHTML = "";
                    containerElement.appendChild(canvas);
                    containerElement.appendChild(pageNumberDiv);
                });
            }).catch(error => {
                console.error(error);
                containerElement.innerHTML = `<div class="error-pdf">Error rendering page ${pageNumber}</div>`;
            });
        }

        pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
            pdfDoc = pdf;
            totalPages = pdf.numPages;
            document.querySelector('.pdf-meta').innerHTML += `<span><i class="fas fa-file"></i> ${totalPages} pages</span>`;
            setupPageContainers();
        }).catch(error => {
            errorElement.style.display = 'block';
            errorElement.textContent = 'Error loading PDF: ' + error.message;
        });

        window.addEventListener('resize', () => {
            devicePixelRatio = window.devicePixelRatio || 1;
            container.innerHTML = '';
            renderedPages = 0;
            setupPageContainers();
        });
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php");
    exit;
}

require '../config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $pdf_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT file_path, filename FROM user_pdfs_uploads WHERE id = ?");
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $stmt->bind_result($file_path, $filename);
    $stmt->fetch();
    $stmt->close();

    if (!file_exists($file_path)) {
        $filename_from_db = basename($file_path);
        $possible_paths = [
            '../uploads/' . $filename_from_db,
            '../uploads/user_pdf_uploads/' . $filename_from_db,
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
            die("Error: File not found. Please check the file path in the database.");
        }
    }
} else {
    die("Error: Invalid PDF ID.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View PDF - <?php echo htmlspecialchars($filename); ?></title>
    <style>
        :root {
            --primary-green: #003300;
            --primary-green-hover: #004d00;
            --yellow: #FFD700;
            --white: #FFFFFF;
            --light-gray: #f5f5f5;
            --border-color: #e0e0e0;
        }
        
        body { 
            margin: 0; 
            padding: 0; 
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
        }
        
        .header {
            background-color: var(--primary-green);
            color: var(--white);
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        
        .back-btn {
            background-color: var(--white);
            color: var(--primary-green);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .viewer-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .controls-container {
            position: sticky;
            top: 0;
            z-index: 90;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .controls-container.hidden {
            transform: translateY(-100%);
        }
        
        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding: 15px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .controls button {
            background-color: var(--primary-green);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .controls button:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-2px);
        }
        
        .quality-select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            font-family: 'Poppins', sans-serif;
        }
        
        .download-btn {
            background-color: var(--yellow);
            color: #000;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            background-color: #e6c300;
            transform: translateY(-2px);
        }
        
        .page-info {
            margin-left: auto;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        #pdf-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
        }
        
        .page-wrapper {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .page-canvas {
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 4px;
            max-width: 100%;
            height: auto;
            background-color: var(--white);
            /* Improve rendering quality */
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        
        .page-number {
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            color: var(--primary-green);
            margin-top: 5px;
            font-size: 14px;
        }
        
        .loading {
            text-align: center;
            padding: 60px;
            font-size: 1.2rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .error {
            color: #dc3545;
            text-align: center;
            padding: 60px;
            font-size: 1.2rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background-color: var(--primary-green);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--primary-green);
            color: var(--white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 80;
            opacity: 0;
            visibility: hidden;
        }
        
        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-to-top:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 12px;
            }
            
            .header h1 {
                font-size: 1.2rem;
            }
            
            .viewer-container {
                padding: 15px;
            }
            
            .controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .page-info {
                margin-left: 0;
                order: -1;
                width: 100%;
                text-align: center;
            }
            
            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }
        
        /* High DPI display optimization */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .page-canvas {
                image-rendering: crisp-edges;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
   <!-- <div class="header">
        <h1>PDF Viewer: <?php echo htmlspecialchars($filename); ?></h1>
        <a href="users_pdfs_uploads.php" class="back-btn">← Back to PDF List</a>
    </div>-->
    
    <div class="viewer-container">
        <div class="controls-container" id="controlsContainer">
            <div class="controls">
                <button id="zoomIn">Zoom In</button>
                <button id="zoomOut">Zoom Out</button>
                <select id="qualitySelect" class="quality-select">
                    <option value="1">Standard Quality</option>
                    <option value="1.5" selected>High Quality</option>
                    <option value="2">Very High Quality</option>
                </select>
                <a href="<?php echo htmlspecialchars($file_path); ?>" download class="download-btn">Download PDF</a>
                <div class="page-info">
                    Loading PDF: <span id="progressText">0%</span>
                    <div class="progress-bar">
                        <div class="progress" id="progressBar"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="loading" class="loading">Loading PDF pages...</div>
        <div id="error" class="error" style="display: none;"></div>
        
        <div id="pdf-container"></div>
    </div>

    <div class="scroll-to-top" id="scrollToTop">↑</div>

    <!-- Load PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // PDF.js configuration
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        // Variables to manage PDF state
        let pdfDoc = null;
        let scale = 1.5; // Start with higher quality
        let totalPages = 0;
        let renderedPages = 0;
        let qualityLevel = 1.5; // Default quality
        let devicePixelRatio = window.devicePixelRatio || 1;
        
        // Scroll variables
        let lastScrollTop = 0;
        const controlsContainer = document.getElementById('controlsContainer');
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        // Get the PDF URL from PHP
        const pdfUrl = "<?php echo htmlspecialchars($file_path); ?>";
        const container = document.getElementById('pdf-container');
        const loadingElement = document.getElementById('loading');
        const errorElement = document.getElementById('error');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');
        const qualitySelect = document.getElementById('qualitySelect');
        
        // ===== SCROLL BEHAVIOR =====
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Show/hide controls based on scroll direction
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                controlsContainer.classList.add('hidden');
            } else {
                // Scrolling up
                controlsContainer.classList.remove('hidden');
            }
            
            lastScrollTop = scrollTop;
            
            // Show/hide scroll to top button
            if (scrollTop > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        // Scroll to top functionality
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // ===== QUALITY SETTINGS =====
        qualitySelect.addEventListener('change', function() {
            qualityLevel = parseFloat(this.value);
            reloadAllPages();
        });
        
        // ===== PDF RENDERING =====
        // Load the PDF document
        const loadingTask = pdfjsLib.getDocument(pdfUrl);
        loadingTask.promise.then(function(pdf) {
            pdfDoc = pdf;
            totalPages = pdf.numPages;
            
            // Render all pages
            renderAllPages();
        }).catch(function(error) {
            loadingElement.style.display = 'none';
            errorElement.style.display = 'block';
            errorElement.textContent = 'Error loading PDF: ' + error.message;
            console.error('Error loading PDF:', error);
        });
        
        // Function to render all pages
        function renderAllPages() {
            if (totalPages === 0) {
                loadingElement.textContent = 'No pages found in PDF.';
                return;
            }
            
            // Create a canvas for each page
            for (let pageNumber = 1; pageNumber <= totalPages; pageNumber++) {
                renderPage(pageNumber);
            }
        }
        
        // Function to render a single page with high quality
        function renderPage(pageNumber) {
            pdfDoc.getPage(pageNumber).then(function(page) {
                // Calculate scale with quality factor and device pixel ratio
                const renderScale = scale * qualityLevel * devicePixelRatio;
                
                const viewport = page.getViewport({ scale: renderScale });
                
                // Create canvas element
                const canvas = document.createElement('canvas');
                canvas.className = 'page-canvas';
                
                // Set canvas size with higher resolution
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                // Create page number indicator
                const pageNumberDiv = document.createElement('div');
                pageNumberDiv.className = 'page-number';
                pageNumberDiv.textContent = `Page ${pageNumber} of ${totalPages}`;
                
                // Create page container
                const pageContainer = document.createElement('div');
                pageContainer.className = 'page-wrapper';
                pageContainer.appendChild(canvas);
                pageContainer.appendChild(pageNumberDiv);
                container.appendChild(pageContainer);
                
                // Render PDF page into canvas context with higher quality
                const renderContext = {
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                };
                
                // Additional rendering options for better quality
                const renderTask = page.render(renderContext);
                
                renderTask.promise.then(function() {
                    // Scale down the canvas for crisp rendering on high DPI displays
                    if (devicePixelRatio > 1) {
                        canvas.style.width = (viewport.width / devicePixelRatio) + 'px';
                        canvas.style.height = (viewport.height / devicePixelRatio) + 'px';
                    }
                    
                    renderedPages++;
                    
                    // Update progress
                    const progress = Math.round((renderedPages / totalPages) * 100);
                    progressText.textContent = `${progress}%`;
                    progressBar.style.width = `${progress}%`;
                    
                    // Hide loading when all pages are rendered
                    if (renderedPages === totalPages) {
                        loadingElement.style.display = 'none';
                    }
                });
            }).catch(function(error) {
                console.error('Error rendering page:', error);
            });
        }
        
        // Zoom in button
        document.getElementById('zoomIn').addEventListener('click', function() {
            scale += 0.2;
            reloadAllPages();
        });
        
        // Zoom out button
        document.getElementById('zoomOut').addEventListener('click', function() {
            if (scale > 0.5) {
                scale -= 0.2;
                reloadAllPages();
            }
        });
        
        // Function to reload all pages with new zoom level
        function reloadAllPages() {
            // Clear container
            container.innerHTML = '';
            renderedPages = 0;
            
            // Show loading
            loadingElement.style.display = 'block';
            loadingElement.textContent = 'Rendering pages...';
            
            // Re-render all pages
            renderAllPages();
        }
        
        // Handle window resize for better rendering
        window.addEventListener('resize', function() {
            // Update device pixel ratio in case of zoom changes
            devicePixelRatio = window.devicePixelRatio || 1;
        });
    </script>
</body>
</html>
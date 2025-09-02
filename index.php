<?php
// Handle theme cookie setting
if (isset($_POST['set_theme'])) {
    $theme = $_POST['set_theme'] === 'dark' ? 'dark' : 'light';
    setcookie('portfolio_theme', $theme, time() + (365 * 24 * 60 * 60), '/');
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Cookie-based user tracking
$currentTheme = isset($_COOKIE['portfolio_theme']) ? $_COOKIE['portfolio_theme'] : 'light';
$darkThemeClass = ($currentTheme === 'dark') ? 'dark-theme' : '';

// Track visitor analytics using $_COOKIE
$visits = isset($_COOKIE['visits']) ? (int)$_COOKIE['visits'] + 1 : 1;
setcookie('visits', $visits, time() + (365 * 24 * 60 * 60), '/');

$firstVisit = isset($_COOKIE['first_visit']) ? $_COOKIE['first_visit'] : date('M Y');
if (!isset($_COOKIE['first_visit'])) {
    setcookie('first_visit', $firstVisit, time() + (365 * 24 * 60 * 60), '/');
}

setcookie('last_visit', date('M d, Y'), time() + (365 * 24 * 60 * 60), '/');

// Database connection and your existing code...
$host = "localhost:4308";
$user = "root";
$pass = "";
$db   = "p_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch Home section
$home_query = $conn->query("SELECT * FROM home WHERE id=1");
$home = $home_query ? $home_query->fetch_assoc() : null;

// Fetch About Me section
$about_query = $conn->query("SELECT * FROM about_me WHERE id=1");
$about = $about_query ? $about_query->fetch_assoc() : null;

// Fetch Education data
$education_query = $conn->query("SELECT * FROM education ORDER BY id DESC");
$education = $education_query ? $education_query->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Skills data
$skills_query = $conn->query("SELECT * FROM skills ORDER BY id DESC");
$skills = $skills_query ? $skills_query->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Experience data
$experience_query = $conn->query("SELECT * FROM experience ORDER BY id DESC");
$experience = $experience_query ? $experience_query->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Projects data
$projects_query = $conn->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $projects_query ? $projects_query->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rysul Aman Nirob - Portfolio</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>

<body class="<?php echo $darkThemeClass; ?>">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <span class="logo-text">Nirob</span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">HOME</a></li>
                <li><a href="#about" class="nav-link">ABOUT</a></li>
                <li><a href="#skills" class="nav-link">SKILLS</a></li>
                <li><a href="#portfolio" class="nav-link">PROJECTS</a></li>
                <li><a href="#contact" class="nav-link">CONTACT</a></li>
                <li><a href="/aaa/login.php" class="nav-link">ADMIN</a></li>
                <li>
                    <button id="theme-toggle" onclick="toggleTheme()" style="
                        background: transparent; 
                        border: 2px solid #00D4AA; 
                        color: #00D4AA; 
                        padding: 0.5rem; 
                        border-radius: 50%; 
                        cursor: pointer; 
                        font-size: 1rem; 
                        width: 40px; 
                        height: 40px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        margin-left: 1rem;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-<?php echo $currentTheme === 'dark' ? 'sun' : 'moon'; ?>" id="theme-icon"></i>
                    </button>
                </li>


            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Hello, I'm<br>
                    <span class="hero-name">Rysul Aman Nirob</span>
                </h1>
                <p class="hero-subtitle">
                    <?php echo htmlspecialchars($home['hero_subtitle']); ?>
                </p>
                <p class="hero-description">
                    <?php echo htmlspecialchars($home['hero_description']); ?>
                </p>
                <button class="cta-button" onclick="scrollToContact()">Hire Me!</button>
            </div>
            <div class="hero-image">
                <?php if (!empty($home['hero_image'])): ?>
                    <img src="<?php echo $home['hero_image']; ?>" alt="Hero Image" class="hero-img">
                <?php else: ?>
                    <img src="images/a.jpg" alt="Default Hero Image" class="hero-img">
                <?php endif; ?>
            </div>

            <!-- Social Links moved inside -->
            <div class="social-links">
                <a href="https://www.facebook.com/rysul.nirob.7/" class="social-link facebook" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://x.com/rysul_n" class="social-link twitter" target="_blank">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.instagram.com/being_nirob/?next=%2F" class="social-link instagram" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.linkedin.com/in/rysul-aman-nirob-6a3323370/" class="social-link linkedin" target="_blank">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img
                        src="<?php echo !empty($about['photo']) ? $about['photo'] : 'images/a.jpg'; ?>"
                        alt="About Me"
                        style="width:350px; height:350px; border-radius:20px; object-fit:cover;">
                </div>
                <div class="about-text">
                    <h2 class="section-title">About Me</h2>
                    <p class="about-description">
                        <?php echo !empty($about['description']) ? htmlspecialchars($about['description']) : 'Hello there! I\'m a web designer, and I\'m very passionate and dedicated to my work.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Educational Journey Section -->
    <section id="education" class="education">
        <div class="container">
            <h2 class="section-title center">Educational Journey</h2>
            <div class="education-timeline">
                <?php foreach ($education as $edu): ?>
                    <div class="education-item">
                        <span class="timeline-dot"></span>
                        <div class="education-card service-card">
                            <span class="education-year"><?php echo htmlspecialchars($edu['year']); ?></span>
                            <h3 class="education-degree"><?php echo htmlspecialchars($edu['degree']); ?></h3>
                            <p class="education-institute"><?php echo htmlspecialchars($edu['institute']); ?></p>
                            <p class="education-description">
                                <?php echo htmlspecialchars($edu['description']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="services">
        <div class="container">
            <h2 class="section-title center">SKILLS</h2>
            <p class="section-subtitle">My technical expertise and professional skills</p>

            <div class="services-grid">
                <?php foreach ($skills as $skill): ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <div class="circular-progress" data-percentage="<?php echo $skill['level'] ?? 0; ?>">
                                <div class="circle-bg"></div>
                                <div class="circle-progress"></div>
                                <div class="icon-content">
                                    <?php if (!empty($skill['icon'])): ?>
                                        <img src="<?php echo $skill['icon']; ?>" alt="<?php echo htmlspecialchars($skill['name']); ?>" style="width:50px; height:50px; border-radius:8px;">
                                    <?php else: ?>
                                        <i class="fa fa-code" style="font-size:2rem; color:#00D4AA;"></i>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($skill['level'])): ?>
                                    <div class="percentage-text"><?php echo $skill['level']; ?>%</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($skill['name']); ?></h3>
                        <p><?php echo htmlspecialchars($skill['description'] ?? 'Professional skill with hands-on experience.'); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>



    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio">
        <div class="container">
            <h2 class="section-title center">Projects</h2>
            <div class="portfolio-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="portfolio-item" data-category="web" onclick="openProjectModal(<?php echo $project['id']; ?>)">
                        <div class="portfolio-img">
                            <img src="<?php echo !empty($project['image']) ? $project['image'] : 'images/default-project.jpg'; ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <div class="portfolio-overlay">
                                <div class="portfolio-info">
                                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($project['short_description']); ?></p>
                                    <div style="margin-top: 1rem;">
                                        <span style="background: rgba(255,255,255,0.2); padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($project['category'] ?? 'Project'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Project Content Area (visible without hover) -->
                        <div class="portfolio-content">
                            <div>
                                <h3 class="portfolio-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p class="portfolio-description"><?php echo htmlspecialchars($project['short_description']); ?></p>
                            </div>
                            <div style="margin-top: auto;">
                                <span style="color: #00D4AA; font-size: 0.8rem; font-weight: 500;">
                                    <?php echo htmlspecialchars($project['category'] ?? 'Web Development'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Project Modal -->
    <div id="project-modal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
">
        <div style="
        position: relative;
        background: #fff;
        margin: 50px auto;
        padding: 2rem;
        border-radius: 15px;
        max-width: 700px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalSlideIn 0.3s ease-out;
    ">
            <!-- Close Button -->
            <button onclick="closeProjectModal()" style="
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 2rem;
            color: #999;
            cursor: pointer;
            z-index: 1;
        ">&times;</button>

            <!-- Project Image -->
            <img id="modal-project-image" style="
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        " alt="Project Image">

            <!-- Project Title -->
            <h2 id="modal-project-title" style="
            color: #008080;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        "></h2>

            <!-- Project Category -->
            <div style="margin-bottom: 1rem;">
                <strong style="color: #00D4AA;">Category:</strong>
                <span id="modal-project-category"></span>
            </div>

            <!-- Technologies -->
            <div style="margin-bottom: 1rem;">
                <strong style="color: #00D4AA;">Technologies:</strong>
                <span id="modal-project-technologies"></span>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #00D4AA;">Description:</strong>
                <p id="modal-project-description" style="
                line-height: 1.6;
                color: #666;
                margin-top: 0.5rem;
            "></p>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center;">
                <a id="modal-github-link" href="#" target="_blank" style="
                display: inline-block;
                background: #008080;
                color: white;
                padding: 12px 25px;
                text-decoration: none;
                border-radius: 25px;
                margin: 0 10px;
                transition: all 0.3s ease;
            ">
                    <i class="fab fa-github"></i> View Code
                </a>
                <a id="modal-demo-link" href="#" target="_blank" style="
                display: inline-block;
                background: #00D4AA;
                color: white;
                padding: 12px 25px;
                text-decoration: none;
                border-radius: 25px;
                margin: 0 10px;
                transition: all 0.3s ease;
            ">
                    <i class="fas fa-external-link-alt"></i> Live Demo
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Hover effects for modal buttons */
        #modal-github-link:hover {
            background: #006666 !important;
            transform: translateY(-2px);
        }

        #modal-demo-link:hover {
            background: #00B894 !important;
            transform: translateY(-2px);
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            #project-modal>div {
                margin: 20px auto;
                padding: 1.5rem;
            }

            #modal-project-title {
                font-size: 1.5rem !important;
            }

            #modal-github-link,
            #modal-demo-link {
                display: block !important;
                margin: 10px 0 !important;
                width: 100%;
                text-align: center;
            }
        }
    </style>

    <!-- Work Experience Section -->
    <section class="experience">
        <div class="container">
            <h2 class="section-title center">Work Experiences</h2>
            <div class="experience-grid">
                <?php foreach ($experience as $exp): ?>
                    <div class="experience-card">
                        <h3><?php echo htmlspecialchars($exp['role']); ?></h3>
                        <p class="experience-period"><?php echo htmlspecialchars($exp['year']); ?></p>
                        <p class="experience-company"><?php echo htmlspecialchars($exp['company']); ?></p>
                        <?php if (!empty($exp['location'])): ?>
                            <p class="experience-location"><?php echo htmlspecialchars($exp['location']); ?></p>
                        <?php endif; ?>
                        <p class="experience-description">
                            <?php echo htmlspecialchars($exp['description']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title center"></h2>
            <div class="contact-content-centered">
                <div class="contact-form-wrapper">
                    <div class="contact-form">
                        <h3>Get In Touch</h3>
                        <p>Feel free to reach out to me for any inquiries or collaborations.</p>

                        <form id="contactForm" action="contact.php" method="POST">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="subject" placeholder="Subject" required>
                            </div>
                            <div class="form-group">
                                <textarea name="message" placeholder="Message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Send Message</button>
                        </form>
                    </div>

                    <!-- Contact Info Below Form -->
                    <div class="contact-info-below">
                        <div class="contact-details">
                            <div class="contact-item">
                                <i class="fa fa-envelope"></i>
                                <span>perfeccnirob@gmail.com.com</span>
                            </div>
                            <div class="contact-item">
                                <i class="fa fa-phone"></i>
                                <span>+8801742460762</span>
                            </div>
                            <div class="contact-item">
                                <i class="fa fa-map-marker"></i>
                                <span>KUET, KHULNA, BANGLADESH</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="logo-text">Nirob</span>
                </div>
                <p class="footer-text">Copyrights © 2025 All rights reserved.</p>

                <!-- User Activity Display using $_COOKIE -->
                <div class="user-activity" style="
                    font-size: 0.8rem; 
                    color: <?php echo $currentTheme === 'dark' ? '#ffffff' : '#666'; ?>; 
                    margin-top: 0.5rem;
                    font-weight: 500;
                    text-align: center;
                ">
                    Visit #<?php echo $visits; ?> •
                    Member since <?php echo $firstVisit; ?> •
                    Theme: <?php echo ucfirst($currentTheme); ?>
                </div>

                <div class="footer-social">
                    <a href="https://www.facebook.com/rysul.nirob.7/" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/rysul_n" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/being_nirob/?next=%2F" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/rysul-aman-nirob-6a3323370/" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
    <script>
        // Project Modal Functions
        function openProjectModal(projectId) {
            fetch(`get_project.php?id=${projectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    // Populate modal with project data
                    document.getElementById('modal-project-title').textContent = data.title;
                    document.getElementById('modal-project-category').textContent = data.category || 'N/A';
                    document.getElementById('modal-project-technologies').textContent = data.technologies || 'N/A';
                    document.getElementById('modal-project-description').textContent = data.detailed_description || data.short_description;

                    // Handle project image
                    const projectImage = document.getElementById('modal-project-image');
                    if (data.image) {
                        projectImage.src = data.image;
                        projectImage.style.display = 'block';
                    } else {
                        projectImage.style.display = 'none';
                    }

                    // Handle links
                    const githubLink = document.getElementById('modal-github-link');
                    const demoLink = document.getElementById('modal-demo-link');

                    if (data.github_link) {
                        githubLink.href = data.github_link;
                        githubLink.style.display = 'inline-block';
                    } else {
                        githubLink.style.display = 'none';
                    }

                    if (data.demo_link) {
                        demoLink.href = data.demo_link;
                        demoLink.style.display = 'inline-block';
                    } else {
                        demoLink.style.display = 'none';
                    }

                    // Show modal
                    document.getElementById('project-modal').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading project details');
                });
        }

        function closeProjectModal() {
            document.getElementById('project-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('project-modal');
            if (event.target === modal) {
                closeProjectModal();
            }
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>
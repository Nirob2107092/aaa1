<?php
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

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <span class="logo-text">Nirob</span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">HOME</a></li>
                <li><a href="#about" class="nav-link">ABOUT</a></li>
                <li><a href="#services" class="nav-link">SKILLS</a></li>
                <li><a href="#portfolio" class="nav-link">PROJECTS</a></li>
                <li><a href="#contact" class="nav-link">CONTACT</a></li>
                <li><a href="/aaa/login.php" class="nav-link">ADMIN</a></li>

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
                <button class="cta-button">Hire Me!</button>
            </div>
            <div class="hero-image">
                <?php if (!empty($home['hero_image'])): ?>
                    <img src="<?php echo $home['hero_image']; ?>" alt="Hero Image" class="hero-img">
                <?php else: ?>
                    <img src="images/a.jpg" alt="Default Hero Image" class="hero-img">
                <?php endif; ?>
            </div>
        </div>
        <div class="social-links">
            <a href="https://www.facebook.com/rysul.nirob.7/" class="social-link facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://x.com/rysul_n" class="social-link twitter" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/being_nirob/?next=%2F" class="social-link instagram" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.linkedin.com/in/rysul-aman-nirob-6a3323370/" class="social-link linkedin" target="_blank"><i class="fab fa-linkedin-in"></i></a>
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
    <section class="services">
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
                <div class="portfolio-item" data-category="popular latest">
                    <div class="portfolio-placeholder">360 X 240</div>
                </div>
                <div class="portfolio-item" data-category="latest following">
                    <div class="portfolio-placeholder">360 X 240</div>
                </div>
                <div class="portfolio-item" data-category="popular upcoming">
                    <div class="portfolio-placeholder">360 X 240</div>
                </div>
                <div class="portfolio-item" data-category="following latest">
                    <div class="portfolio-placeholder">360 X 240</div>
                </div>
                <div class="portfolio-item" data-category="upcoming popular">
                    <div class="portfolio-placeholder">360 X 240</div>
                </div>
                <div class="portfolio-item" data-category="latest following">
                    <div class="portfolio-placeholder">360 X 240</div>
                </div>
            </div>
        </div>
    </section>

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
            <h2 class="section-title center">Contact Me</h2>
            <div class="contact-content">
                <div class="contact-form">
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
                <div class="contact-map">
                    <div class="map-placeholder">
                        <div class="map-content">
                            <h4>Address</h4>
                            <p>London, United Kingdom</p>
                            <div id="map" class="map-visual"></div>
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
</body>

</html>
<?php $conn->close(); ?>
<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = "localhost:4308";
$user = "root";
$pass = "";
$db   = "p_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Helper: sanitize input
function sanitize($str)
{
    global $conn;
    return $conn->real_escape_string(trim($str));
}

// Handle AJAX requests for education data
if (isset($_GET['action']) && $_GET['action'] === 'get_education' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM education WHERE id=$id");
    if ($result && $row = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Education not found']);
        exit;
    }
}

// Handle AJAX requests for skills data
if (isset($_GET['action']) && $_GET['action'] === 'get_skill' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM skills WHERE id=$id");
    if ($result && $row = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Skill not found']);
        exit;
    }
}

// Handle AJAX requests for experience data
if (isset($_GET['action']) && $_GET['action'] === 'get_experience' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM experience WHERE id=$id");
    if ($result && $row = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Experience not found']);
        exit;
    }
}

$home_updated = isset($_GET['success']) && $_GET['success'] === 'home';
$about_updated = isset($_GET['success']) && $_GET['success'] === 'about';
$education_added = isset($_GET['success']) && $_GET['success'] === 'education_added';

// Handle ALL POST actions BEFORE any HTML output!
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // About update
    if (isset($_POST['update_about'])) {
        $desc = sanitize($_POST['about_desc']);
        $photoPath = '';

        // Handle photo upload
        if (!empty($_FILES['about_photo']['name'])) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $photoPath = $uploadDir . basename($_FILES['about_photo']['name']);
            if (move_uploaded_file($_FILES['about_photo']['tmp_name'], $photoPath)) {
                // Photo uploaded successfully
            } else {
                $photoPath = ''; // Reset if upload failed
            }
        }

        // Update database
        if ($photoPath) {
            $sql = "UPDATE about_me SET description='$desc', photo='$photoPath' WHERE id=1";
        } else {
            $sql = "UPDATE about_me SET description='$desc' WHERE id=1";
        }

        if ($conn->query($sql)) {
            header("Location: admin.php?success=about#about");
            exit;
        } else {
            echo "Error updating about section: " . $conn->error;
        }
    }
    // Add Skill
    if (isset($_POST['add_skill']) && !empty($_POST['skill_name'])) {
        $name = sanitize($_POST['skill_name']);
        $level = !empty($_POST['skill_level']) ? intval($_POST['skill_level']) : NULL;
        $category = !empty($_POST['skill_category']) ? sanitize($_POST['skill_category']) : NULL;
        $description = !empty($_POST['skill_description']) ? sanitize($_POST['skill_description']) : NULL;

        $iconPath = '';
        $iconType = $_POST['icon_type'] ?? 'url';

        if ($iconType === 'url' && !empty($_POST['skill_icon_url'])) {
            $iconPath = sanitize($_POST['skill_icon_url']);
        } elseif ($iconType === 'upload' && !empty($_FILES['skill_icon']['name'])) {
            $iconPath = 'uploads/' . basename($_FILES['skill_icon']['name']);
            move_uploaded_file($_FILES['skill_icon']['tmp_name'], $iconPath);
        }

        $sql = "INSERT INTO skills (name, level, category, description" . ($iconPath ? ", icon" : "") . ") VALUES ('$name', $level, '$category', '$description'" . ($iconPath ? ", '$iconPath'" : "") . ")";
        $conn->query($sql);
        header("Location: admin.php?success=skill_added#skills");
        exit;
    }
    // Update Skill
    if (isset($_POST['update_skill']) && !empty($_POST['edit_skill_name'])) {
        $id = intval($_POST['edit_skill_id']);
        $name = sanitize($_POST['edit_skill_name']);
        $level = !empty($_POST['edit_skill_level']) ? intval($_POST['edit_skill_level']) : NULL;
        $category = !empty($_POST['edit_skill_category']) ? sanitize($_POST['edit_skill_category']) : NULL;
        $description = !empty($_POST['edit_skill_description']) ? sanitize($_POST['edit_skill_description']) : NULL;

        $iconPath = '';
        $iconType = $_POST['edit_icon_type'] ?? 'url';

        if ($iconType === 'url' && !empty($_POST['edit_skill_icon_url'])) {
            $iconPath = sanitize($_POST['edit_skill_icon_url']);
        } elseif ($iconType === 'upload' && !empty($_FILES['edit_skill_icon']['name'])) {
            $iconPath = 'uploads/' . basename($_FILES['edit_skill_icon']['name']);
            move_uploaded_file($_FILES['edit_skill_icon']['tmp_name'], $iconPath);
        }

        $sql = "UPDATE skills SET name='$name', level=$level, category='$category', description='$description'";
        if ($iconPath) $sql .= ", icon='$iconPath'";
        $sql .= " WHERE id=$id";
        $conn->query($sql);
        header("Location: admin.php?success=skill_updated#skills");
        exit;
    }
    // Delete Skill
    if (isset($_POST['delete_skill'])) {
        $id = intval($_POST['skill_id']);
        $conn->query("DELETE FROM skills WHERE id=$id");
        header("Location: admin.php?success=skill_deleted#skills");
        exit;
    }
    // Add Project
    if (isset($_POST['add_project']) && !empty($_POST['project_name'])) {
        $name = sanitize($_POST['project_name']);
        $desc = sanitize($_POST['project_desc']);
        $conn->query("INSERT INTO projects (name, description) VALUES ('$name', '$desc')");
        header("Location: admin.php");
        exit;
    }
    // Delete Project
    if (isset($_POST['delete_project'])) {
        $id = intval($_POST['project_id']);
        $conn->query("DELETE FROM projects WHERE id=$id");
        header("Location: admin.php");
        exit;
    }
    // Add Education
    if (isset($_POST['add_education']) && !empty($_POST['edu_institute'])) {
        $inst = sanitize($_POST['edu_institute']);
        $deg = sanitize($_POST['edu_degree']);
        $year = sanitize($_POST['edu_year']);
        $desc = sanitize($_POST['edu_description']);
        $conn->query("INSERT INTO education (institute, degree, year, description) VALUES ('$inst', '$deg', '$year', '$desc')");
        header("Location: admin.php?success=education#education");
        exit;
    }
    // Delete Education
    if (isset($_POST['delete_education'])) {
        $id = intval($_POST['edu_id']);
        $conn->query("DELETE FROM education WHERE id=$id");
        header("Location: admin.php");
        exit;
    }
    // Add Experience
    if (isset($_POST['add_experience']) && !empty($_POST['exp_company'])) {
        $comp = sanitize($_POST['exp_company']);
        $role = sanitize($_POST['exp_role']);
        $year = sanitize($_POST['exp_year']);
        $location = !empty($_POST['exp_location']) ? sanitize($_POST['exp_location']) : NULL;
        $description = !empty($_POST['exp_description']) ? sanitize($_POST['exp_description']) : NULL;
        $conn->query("INSERT INTO experience (company, role, year, location, description) VALUES ('$comp', '$role', '$year', '$location', '$description')");
        header("Location: admin.php?success=experience_added#experience");
        exit;
    }
    // Update Experience
    if (isset($_POST['update_experience']) && !empty($_POST['edit_exp_company'])) {
        $id = intval($_POST['edit_exp_id']);
        $comp = sanitize($_POST['edit_exp_company']);
        $role = sanitize($_POST['edit_exp_role']);
        $year = sanitize($_POST['edit_exp_year']);
        $location = !empty($_POST['edit_exp_location']) ? sanitize($_POST['edit_exp_location']) : NULL;
        $description = !empty($_POST['edit_exp_description']) ? sanitize($_POST['edit_exp_description']) : NULL;
        $conn->query("UPDATE experience SET company='$comp', role='$role', year='$year', location='$location', description='$description' WHERE id=$id");
        header("Location: admin.php?success=experience_updated#experience");
        exit;
    }
    // Delete Experience
    if (isset($_POST['delete_experience'])) {
        $id = intval($_POST['exp_id']);
        $conn->query("DELETE FROM experience WHERE id=$id");
        header("Location: admin.php?success=experience_deleted#experience");
        exit;
    }
    // Delete Contact
    if (isset($_POST['delete_contact'])) {
        $id = intval($_POST['contact_id']);
        $conn->query("DELETE FROM contacts WHERE id=$id");
        header("Location: admin.php");
        exit;
    }
    // Update Home Section
    if (isset($_POST['update_home'])) {
        $subtitle = sanitize($_POST['hero_subtitle']);
        $desc = sanitize($_POST['hero_description']);
        $imgPath = '';
        if (!empty($_FILES['hero_image']['name'])) {
            $imgPath = 'uploads/' . basename($_FILES['hero_image']['name']);
            move_uploaded_file($_FILES['hero_image']['tmp_name'], $imgPath);
        }
        $sql = "UPDATE home SET hero_subtitle='$subtitle', hero_description='$desc'";
        if ($imgPath) $sql .= ", hero_image='$imgPath'";
        $sql .= " WHERE id=1";
        $conn->query($sql);
        header("Location: admin.php?success=home#home");
        exit;
    }
    // Update Education
    if (isset($_POST['update_education']) && !empty($_POST['edit_edu_institute'])) {
        $id = intval($_POST['edit_edu_id']);
        $inst = sanitize($_POST['edit_edu_institute']);
        $deg = sanitize($_POST['edit_edu_degree']);
        $year = sanitize($_POST['edit_edu_year']);
        $desc = sanitize($_POST['edit_edu_description']);
        $conn->query("UPDATE education SET institute='$inst', degree='$deg', year='$year', description='$desc' WHERE id=$id");
        header("Location: admin.php?success=education_updated#education");
        exit;
    }
}

// Fetch current data
$about = $conn->query("SELECT * FROM about_me WHERE id=1")->fetch_assoc();
$skills = $conn->query("SELECT * FROM skills");
$projects = $conn->query("SELECT * FROM projects");
$education = $conn->query("SELECT * FROM education");
$experience = $conn->query("SELECT * FROM experience");
$contacts = $conn->query("SELECT * FROM contacts");
$home_query = $conn->query("SELECT * FROM home WHERE id=1");
$home = $home_query ? $home_query->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .sidebar {
            width: 220px;
            background: #008080;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding-top: 2rem;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 1rem 2rem;
            display: block;
            transition: background 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #00D4AA;
        }

        .main {
            margin-left: 220px;
            padding: 2rem;
        }

        h2 {
            color: #008080;
            margin-top: 0;
        }

        .section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.5rem;
            margin: 0.5rem 0 1rem 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="file"] {
            margin-bottom: 1rem;
        }

        button {
            background: #00D4AA;
            color: #fff;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #008080;
        }

        .logout {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            color: #fff;
        }

        .img-preview {
            max-width: 120px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            border: 1px solid #eee;
            padding: 0.5rem;
            text-align: left;
        }

        .table th {
            background: #f4f6f8;
        }

        .actions button {
            margin-right: 0.5rem;
        }

        /* Admin icon size fix */
        .admin-icon {
            font-size: 2rem;
            background: #fff;
            color: #008080;
            border-radius: 50%;
            padding: 10px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        #success-alert {
            display: none;
            background-color: #d4edda;
            color: #155724;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
            border-radius: 0.25rem;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        // Sidebar navigation
        function showSection(id) {
            document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
            document.getElementById(id).style.display = 'block';
            document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
            document.getElementById('link-' + id).classList.add('active');
        }
        window.onload = function() {
            var hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                showSection(hash);
            } else {
                showSection('about');
            }
            <?php if ($home_updated): ?>
                var alertBox = document.getElementById('success-alert');
                alertBox.style.display = 'block';
                setTimeout(function() {
                    alertBox.style.display = 'none';
                }, 2000);
            <?php endif; ?>
        }

        // Image preview for hero image
        document.addEventListener('DOMContentLoaded', function() {
            var heroImageInput = document.getElementById('hero_image');
            if (heroImageInput) {
                heroImageInput.addEventListener('change', function(e) {
                    var preview = document.getElementById('hero-img-preview');
                    if (e.target.files && e.target.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(ev) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }
        });

        // Icon type toggle for Add form
        document.addEventListener('DOMContentLoaded', function() {
            const iconUrlRadio = document.getElementById('icon_url');
            const iconUploadRadio = document.getElementById('icon_upload');
            const iconUrlField = document.getElementById('icon-url-field');
            const iconUploadField = document.getElementById('icon-upload-field');

            if (iconUrlRadio && iconUploadRadio) {
                iconUrlRadio.addEventListener('change', function() {
                    if (this.checked) {
                        iconUrlField.style.display = 'block';
                        iconUploadField.style.display = 'none';
                    }
                });

                iconUploadRadio.addEventListener('change', function() {
                    if (this.checked) {
                        iconUrlField.style.display = 'none';
                        iconUploadField.style.display = 'block';
                    }
                });
            }

            // Edit form toggles
            const editIconUrlRadio = document.getElementById('edit_icon_url');
            const editIconUploadRadio = document.getElementById('edit_icon_upload');
            const editIconUrlField = document.getElementById('edit-icon-url-field');
            const editIconUploadField = document.getElementById('edit-icon-upload-field');

            if (editIconUrlRadio && editIconUploadRadio) {
                editIconUrlRadio.addEventListener('change', function() {
                    if (this.checked) {
                        editIconUrlField.style.display = 'block';
                        editIconUploadField.style.display = 'none';
                    }
                });

                editIconUploadRadio.addEventListener('change', function() {
                    if (this.checked) {
                        editIconUrlField.style.display = 'none';
                        editIconUploadField.style.display = 'block';
                    }
                });
            }
        });
    </script>
</head>

<body>
    <div class="sidebar">
        <!-- Admin Profile Section -->
        <div style="text-align:center; margin-bottom:2rem;">
            <i class="fa fa-user admin-icon"></i>
            <div style="font-weight:bold; font-size:1.2rem;">Martyn Vorm</div>
            <div style="font-size:0.95rem; color:#e0f7fa;">Admin Panel</div>
        </div>
        <a href="javascript:void(0)" id="link-home" onclick="showSection('home')">Home</a>
        <a href="javascript:void(0)" id="link-about" onclick="showSection('about')">About</a>
        <a href="javascript:void(0)" id="link-skills" onclick="showSection('skills')">Skills</a>
        <a href="javascript:void(0)" id="link-projects" onclick="showSection('projects')">Projects</a>
        <a href="javascript:void(0)" id="link-education" onclick="showSection('education')">Education</a>
        <a href="javascript:void(0)" id="link-experience" onclick="showSection('experience')">Experience</a>
        <a href="javascript:void(0)" id="link-contact" onclick="showSection('contact')">Contact</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
    <div class="main">
        <!-- About Section -->
        <div class="section" id="about">
            <h2>Edit About Me</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Description:</label>
                <textarea name="about_desc" rows="5"><?php echo htmlspecialchars($about['description']); ?></textarea>
                <label>Photo:</label><br>
                <img id="about-img-preview"
                    src="<?php echo !empty($about['photo']) ? $about['photo'] : 'images/a.jpg'; ?>"
                    class="img-preview"
                    alt="About Photo"
                    style="display:block;"><br>
                <input type="file" name="about_photo" id="about_photo">
                <button type="submit" name="update_about">Update</button>
            </form>
        </div>
        <script>
            document.getElementById('about_photo').addEventListener('change', function(e) {
                var preview = document.getElementById('about-img-preview');
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.src = ev.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        </script>
        <!-- Skills Section -->
        <div class="section" id="skills" style="display:none;">
            <h2>Skills Management</h2>

            <!-- Add Skill Button -->
            <button onclick="showAddSkillForm()" style="background:#008080; margin-bottom:1rem;">
                <i class="fa fa-plus"></i> Add Skill
            </button>

            <!-- Add Skill Form (Hidden by default) -->
            <div id="add-skill-form" style="display:none; background:#f8f9fa; padding:1.5rem; border-radius:8px; margin-bottom:2rem;">
                <h3>Add New Skill</h3>
                <form method="POST" enctype="multipart/form-data">
                    <label>Skill Name:</label>
                    <input type="text" name="skill_name" placeholder="e.g., JavaScript, PHP, React" required>

                    <label>Proficiency Level (%):</label>
                    <input type="number" name="skill_level" placeholder="e.g., 85" min="0" max="100">

                    <label>Category:</label>
                    <input type="text" name="skill_category" placeholder="e.g., Programming, Design, Tools">

                    <label>Description:</label>
                    <textarea name="skill_description" rows="3" placeholder="Describe your experience with this skill"></textarea>

                    <!-- Icon Options -->
                    <label>Icon Type:</label>
                    <div style="margin-bottom:1rem;">
                        <input type="radio" name="icon_type" value="url" id="icon_url" checked>
                        <label for="icon_url" style="margin-right:1rem;">Icon URL</label>

                        <input type="radio" name="icon_type" value="upload" id="icon_upload">
                        <label for="icon_upload">Upload File</label>
                    </div>

                    <div id="icon-url-field">
                        <label>Icon URL:</label>
                        <input type="url" name="skill_icon_url" placeholder="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/cplusplus/cplusplus-original.svg">
                        <small style="color:#666; display:block; margin-top:0.5rem;">
                            You can use DevIcons: <a href="https://devicon.dev/" target="_blank">https://devicon.dev/</a>
                        </small>
                    </div>

                    <div id="icon-upload-field" style="display:none;">
                        <label>Upload Icon/Image:</label>
                        <input type="file" name="skill_icon" accept="image/*">
                    </div>

                    <div style="margin-top:1rem;">
                        <button type="submit" name="add_skill">Save Skill</button>
                        <button type="button" onclick="hideAddSkillForm()" style="background:#6c757d;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Skills Table -->
            <div style="background:#fff; border-radius:8px; overflow:hidden;">
                <table class="table">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Skill Name</th>
                            <th>Level (%)</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $skills_result = $conn->query("SELECT * FROM skills ORDER BY id DESC");
                        while ($row = $skills_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if (!empty($row['icon'])): ?>
                                        <img src="<?php echo $row['icon']; ?>" style="width:30px; height:30px; border-radius:4px;" alt="Skill Icon">
                                    <?php else: ?>
                                        <i class="fa fa-code" style="font-size:20px; color:#008080;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['level'] ?? 'N/A'); ?>%</td>
                                <td><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></td>
                                <td class="actions">
                                    <button onclick="viewSkill(<?php echo $row['id']; ?>)" style="background:#17a2b8; padding:0.4rem 0.8rem; margin-right:0.3rem;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button onclick="editSkill(<?php echo $row['id']; ?>)" style="background:#ffc107; padding:0.4rem 0.8rem; margin-right:0.3rem;">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this skill?')">
                                        <input type="hidden" name="skill_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_skill" style="background:#dc3545; padding:0.4rem 0.8rem;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Projects Section -->
        <div class="section" id="projects" style="display:none;">
            <h2>Projects</h2>
            <form method="POST">
                <label>Project Name:</label>
                <input type="text" name="project_name" placeholder="Project name">
                <label>Description:</label>
                <textarea name="project_desc" rows="3"></textarea>
                <button type="submit" name="add_project">Add Project</button>
            </form>
            <table class="table">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $projects->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td class="actions">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="project_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_project">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <!-- Education Section -->
        <div class="section" id="education" style="display:none;">
            <h2>Education Management</h2>

            <!-- Add Education Button -->
            <button onclick="showAddEducationForm()" style="background:#008080; margin-bottom:1rem;">
                <i class="fa fa-plus"></i> Add Education
            </button>

            <!-- Add Education Form (Hidden by default) -->
            <div id="add-education-form" style="display:none; background:#f8f9fa; padding:1.5rem; border-radius:8px; margin-bottom:2rem;">
                <h3>Add New Education</h3>
                <form method="POST">
                    <label>Institution:</label>
                    <input type="text" name="edu_institute" placeholder="University/School name" required>
                    <label>Degree/Course:</label>
                    <input type="text" name="edu_degree" placeholder="Degree or course title" required>
                    <label>Year/Duration:</label>
                    <input type="text" name="edu_year" placeholder="e.g., 2018-2022" required>
                    <label>Description:</label>
                    <textarea name="edu_description" rows="3" placeholder="Additional details (optional)"></textarea>
                    <div style="margin-top:1rem;">
                        <button type="submit" name="add_education">Save Education</button>
                        <button type="button" onclick="hideAddEducationForm()" style="background:#6c757d;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Education Table -->
            <div style="background:#fff; border-radius:8px; overflow:hidden;">
                <table class="table">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>ID</th>
                            <th>Institution</th>
                            <th>Degree</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $education->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['institute']); ?></td>
                                <td><?php echo htmlspecialchars($row['degree']); ?></td>
                                <td><?php echo htmlspecialchars($row['year']); ?></td>
                                <td class="actions">
                                    <button onclick="viewEducation(<?php echo $row['id']; ?>)" style="background:#17a2b8; padding:0.4rem 0.8rem; margin-right:0.3rem;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button onclick="editEducation(<?php echo $row['id']; ?>)" style="background:#ffc107; padding:0.4rem 0.8rem; margin-right:0.3rem;">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this education record?')">
                                        <input type="hidden" name="edu_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_education" style="background:#dc3545; padding:0.4rem 0.8rem;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Experience Section -->
        <div class="section" id="experience" style="display:none;">
            <h2>Work Experience Management</h2>

            <!-- Add Experience Button -->
            <button onclick="showAddExperienceForm()" style="background:#008080; margin-bottom:1rem;">
                <i class="fa fa-plus"></i> Add Experience
            </button>

            <!-- Add Experience Form (Hidden by default) -->
            <div id="add-experience-form" style="display:none; background:#f8f9fa; padding:1.5rem; border-radius:8px; margin-bottom:2rem;">
                <h3>Add New Work Experience</h3>
                <form method="POST">
                    <label>Company Name:</label>
                    <input type="text" name="exp_company" placeholder="Company name" required>
                    <label>Job Title/Role:</label>
                    <input type="text" name="exp_role" placeholder="e.g., Senior Developer" required>
                    <label>Duration:</label>
                    <input type="text" name="exp_year" placeholder="e.g., 2020-2023" required>
                    <label>Location:</label>
                    <input type="text" name="exp_location" placeholder="e.g., New York, Remote">
                    <label>Description:</label>
                    <textarea name="exp_description" rows="3" placeholder="Job responsibilities and achievements"></textarea>
                    <div style="margin-top:1rem;">
                        <button type="submit" name="add_experience">Save Experience</button>
                        <button type="button" onclick="hideAddExperienceForm()" style="background:#6c757d;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Experience Table -->
            <div style="background:#fff; border-radius:8px; overflow:hidden;">
                <table class="table">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $experience_result = $conn->query("SELECT * FROM experience ORDER BY id DESC");
                        while ($row = $experience_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['company']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td><?php echo htmlspecialchars($row['year']); ?></td>
                                <td class="actions">
                                    <button onclick="viewExperience(<?php echo $row['id']; ?>)" style="background:#17a2b8; padding:0.4rem 0.8rem; margin-right:0.3rem;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button onclick="editExperience(<?php echo $row['id']; ?>)" style="background:#ffc107; padding:0.4rem 0.8rem; margin-right:0.3rem;">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this experience?')">
                                        <input type="hidden" name="exp_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_experience" style="background:#dc3545; padding:0.4rem 0.8rem;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Contact Section -->
        <div class="section" id="contact" style="display:none;">
            <h2>Contact Messages</h2>
            <table class="table">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $contacts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                        <td class="actions">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="contact_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_contact">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <!-- Home Section -->
        <div class="section" id="home" style="display:none;">
            <h2>Edit Home Section</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="hero_subtitle">Hero Subtitle:</label>
                <input type="text" name="hero_subtitle" id="hero_subtitle" value="<?php echo isset($home['hero_subtitle']) ? htmlspecialchars($home['hero_subtitle']) : ''; ?>">
                <label for="hero_description">Hero Description:</label>
                <textarea name="hero_description" id="hero_description" rows="4"><?php echo isset($home['hero_description']) ? htmlspecialchars($home['hero_description']) : ''; ?></textarea>
                <label for="hero_image">Hero Image:</label><br>
                <img
                    id="hero-img-preview"
                    src="<?php echo !empty($home['hero_image']) ? $home['hero_image'] : 'images/a.jpg'; ?>"
                    class="img-preview"
                    alt="Hero Image"
                    style="display:block;"><br>
                <input type="file" name="hero_image" id="hero_image">
                <button type="submit" name="update_home">Update Home Section</button>
            </form>
        </div>
    </div>
    <!-- Sweet Alert-like popup -->
    <div id="success-alert" style="
        display:none;
        position:fixed;
        top:30px;
        left:50%;
        transform:translateX(-50%);
        background:#00D4AA;
        color:#fff;
        padding:1rem 2rem;
        border-radius:8px;
        box-shadow:0 2px 8px rgba(0,0,0,0.15);
        font-size:1.1rem;
        z-index:9999;
        text-align:center;
    ">
        <i class="fa fa-check-circle" style="margin-right:8px;"></i>
        <span id="alert-message">Action Completed!</span>
    </div>

    <!-- View Education Modal -->
    <div id="view-education-modal" style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,0.5);
        z-index:10000;
    ">
        <div style="
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%,-50%);
            background:#fff;
            padding:2rem;
            border-radius:8px;
            width:500px;
            max-width:90%;
        ">
            <h3>Education Details</h3>
            <div id="view-education-content"></div>
            <button onclick="closeViewModal()" style="background:#6c757d; margin-top:1rem;">Close</button>
        </div>
    </div>

    <!-- Edit Education Modal -->
    <div id="edit-education-modal" style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,0.5);
        z-index:10000;
    ">
        <div style="
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%,-50%);
            background:#fff;
            padding:2rem;
            border-radius:8px;
            width:500px;
            max-width:90%;
        ">
            <h3>Edit Education</h3>
            <form method="POST" id="edit-education-form">
                <input type="hidden" name="edit_edu_id" id="edit_edu_id">
                <label>Institution:</label>
                <input type="text" name="edit_edu_institute" id="edit_edu_institute" required>
                <label>Degree/Course:</label>
                <input type="text" name="edit_edu_degree" id="edit_edu_degree" required>
                <label>Year/Duration:</label>
                <input type="text" name="edit_edu_year" id="edit_edu_year" required>
                <label>Description:</label>
                <textarea name="edit_edu_description" id="edit_edu_description" rows="3"></textarea>
                <div style="margin-top:1rem;">
                    <button type="submit" name="update_education">Update Education</button>
                    <button type="button" onclick="closeEditModal()" style="background:#6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Skill Modal -->
    <div id="view-skill-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:2rem; border-radius:8px; width:500px; max-width:90%;">
            <h3>Skill Details</h3>
            <div id="view-skill-content"></div>
            <button onclick="closeViewSkillModal()" style="background:#6c757d; margin-top:1rem;">Close</button>
        </div>
    </div>

    <!-- Edit Skill Modal -->
    <div id="edit-skill-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:2rem; border-radius:8px; width:500px; max-width:90%;">
            <h3>Edit Skill</h3>
            <form method="POST" id="edit-skill-form" enctype="multipart/form-data">
                <input type="hidden" name="edit_skill_id" id="edit_skill_id">

                <label>Skill Name:</label>
                <input type="text" name="edit_skill_name" id="edit_skill_name" required>

                <label>Proficiency Level (%):</label>
                <input type="number" name="edit_skill_level" id="edit_skill_level" min="0" max="100">

                <label>Category:</label>
                <input type="text" name="edit_skill_category" id="edit_skill_category">

                <label>Description:</label>
                <textarea name="edit_skill_description" id="edit_skill_description" rows="3"></textarea>

                <label>Current Icon:</label>
                <div id="current_skill_icon" style="margin-bottom:1rem;"></div>

                <!-- Icon Options for Edit -->
                <label>Icon Type:</label>
                <div style="margin-bottom:1rem;">
                    <input type="radio" name="edit_icon_type" value="url" id="edit_icon_url" checked>
                    <label for="edit_icon_url" style="margin-right:1rem;">Icon URL</label>

                    <input type="radio" name="edit_icon_type" value="upload" id="edit_icon_upload">
                    <label for="edit_icon_upload">Upload File</label>
                </div>

                <div id="edit-icon-url-field">
                    <label>Icon URL:</label>
                    <input type="url" name="edit_skill_icon_url" id="edit_skill_icon_url" placeholder="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/cplusplus/cplusplus-original.svg">
                </div>

                <div id="edit-icon-upload-field" style="display:none;">
                    <label>New Icon File:</label>
                    <input type="file" name="edit_skill_icon" accept="image/*">
                </div>

                <div style="margin-top:1rem;">
                    <button type="submit" name="update_skill">Update Skill</button>
                    <button type="button" onclick="closeEditSkillModal()" style="background:#6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Experience Modal -->
    <div id="view-experience-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:2rem; border-radius:8px; width:500px; max-width:90%;">
            <h3>Experience Details</h3>
            <div id="view-experience-content"></div>
            <button onclick="closeViewExperienceModal()" style="background:#6c757d; margin-top:1rem;">Close</button>
        </div>
    </div>

    <!-- Edit Experience Modal -->
    <div id="edit-experience-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:2rem; border-radius:8px; width:500px; max-width:90%;">
            <h3>Edit Experience</h3>
            <form method="POST" id="edit-experience-form">
                <input type="hidden" name="edit_exp_id" id="edit_exp_id">
                <label>Company Name:</label>
                <input type="text" name="edit_exp_company" id="edit_exp_company" required>
                <label>Job Title/Role:</label>
                <input type="text" name="edit_exp_role" id="edit_exp_role" required>
                <label>Duration:</label>
                <input type="text" name="edit_exp_year" id="edit_exp_year" required>
                <label>Location:</label>
                <input type="text" name="edit_exp_location" id="edit_exp_location">
                <label>Description:</label>
                <textarea name="edit_exp_description" id="edit_exp_description" rows="3"></textarea>
                <div style="margin-top:1rem;">
                    <button type="submit" name="update_experience">Update Experience</button>
                    <button type="button" onclick="closeEditExperienceModal()" style="background:#6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show alert with custom message
        function showAlert(message) {
            var alertBox = document.getElementById('success-alert');
            var alertMessage = document.getElementById('alert-message');
            if (alertMessage) {
                alertMessage.textContent = message;
            }
            alertBox.style.display = 'block';
            setTimeout(function() {
                alertBox.style.display = 'none';
            }, 3000);
        }

        // Check for success flags and show appropriate alerts
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($home_updated): ?>
                showAlert('Home section updated successfully!');
            <?php elseif ($about_updated): ?>
                showAlert('About section updated successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'education_added'): ?>
                showAlert('Education added successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'education_deleted'): ?>
                showAlert('Education deleted successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'education_updated'): ?>
                showAlert('Education updated successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'skill_added'): ?>
                showAlert('Skill added successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'skill_updated'): ?>
                showAlert('Skill updated successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'skill_deleted'): ?>
                showAlert('Skill deleted successfully!');
            <?php endif; ?>
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>
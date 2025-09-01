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

// Handle AJAX requests for project data
if (isset($_GET['action']) && $_GET['action'] === 'get_project' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM projects WHERE id=$id");
    if ($result && $row = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Project not found']);
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
    if (isset($_POST['add_project']) && !empty($_POST['project_title'])) {
        $title = sanitize($_POST['project_title']);
        $short_desc = sanitize($_POST['project_short_desc']);
        $detailed_desc = sanitize($_POST['project_detailed_desc']);
        $technologies = sanitize($_POST['project_technologies']);
        $github = sanitize($_POST['project_github']);
        $demo = sanitize($_POST['project_demo']);
        $category = sanitize($_POST['project_category']);

        $imagePath = '';
        if (!empty($_FILES['project_image']['name'])) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imagePath = $uploadDir . basename($_FILES['project_image']['name']);
            move_uploaded_file($_FILES['project_image']['tmp_name'], $imagePath);
        }

        $sql = "INSERT INTO projects (title, short_description, detailed_description, technologies, github_link, demo_link, category" . ($imagePath ? ", image" : "") . ") VALUES ('$title', '$short_desc', '$detailed_desc', '$technologies', '$github', '$demo', '$category'" . ($imagePath ? ", '$imagePath'" : "") . ")";
        $conn->query($sql);
        header("Location: admin.php?success=project_added#projects");
        exit;
    }
    // Update Project
    if (isset($_POST['update_project']) && !empty($_POST['edit_project_title'])) {
        $id = intval($_POST['edit_project_id']);
        $title = sanitize($_POST['edit_project_title']);
        $short_desc = sanitize($_POST['edit_project_short_desc']);
        $detailed_desc = sanitize($_POST['edit_project_detailed_desc']);
        $technologies = sanitize($_POST['edit_project_technologies']);
        $github = sanitize($_POST['edit_project_github']);
        $demo = sanitize($_POST['edit_project_demo']);
        $category = sanitize($_POST['edit_project_category']);

        $imagePath = '';
        if (!empty($_FILES['edit_project_image']['name'])) {
            $uploadDir = 'uploads/';
            $imagePath = $uploadDir . basename($_FILES['edit_project_image']['name']);
            move_uploaded_file($_FILES['edit_project_image']['tmp_name'], $imagePath);
        }

        $sql = "UPDATE projects SET title='$title', short_description='$short_desc', detailed_description='$detailed_desc', technologies='$technologies', github_link='$github', demo_link='$demo', category='$category'";
        if ($imagePath) $sql .= ", image='$imagePath'";
        $sql .= " WHERE id=$id";
        $conn->query($sql);
        header("Location: admin.php?success=project_updated#projects");
        exit;
    }
    // Delete Project
    if (isset($_POST['delete_project'])) {
        $id = intval($_POST['project_id']);
        $conn->query("DELETE FROM projects WHERE id=$id");
        header("Location: admin.php?success=project_deleted#projects");
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

        /* Improved Modal Styles */
        .project-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .project-modal-content {
            position: relative;
            background: #fff;
            margin: 0 auto;
            padding: 2rem;
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-sizing: border-box;
        }

        /* Form Grid Responsive */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-full-width {
            grid-column: 1 / -1;
        }

        /* Mobile Responsive for Admin Panel */
        @media (max-width: 768px) {
            .admin-container {
                padding: 0.5rem;
            }

            .admin-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .admin-form {
                padding: 1rem;
                margin: 0.5rem 0;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .projects-table {
                font-size: 0.8rem;
            }

            .projects-table th,
            .projects-table td {
                padding: 0.5rem 0.3rem;
            }

            .projects-table .btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
                margin: 0.2rem;
            }

            /* Hide some columns on mobile */
            .projects-table .hide-mobile {
                display: none;
            }

            /* Stack action buttons vertically on mobile */
            .projects-table .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.3rem;
            }

            /* Modal responsive */
            .project-modal-content {
                width: 95%;
                margin: 1rem auto;
                max-height: 90vh;
                padding: 1rem;
            }

            .project-modal-content h3 {
                font-size: 1.2rem;
            }

            .modal-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .modal-buttons button {
                width: 100%;
            }

            /* Form elements mobile */
            .form-group input,
            .form-group textarea,
            .form-group select {
                font-size: 16px;
                /* Prevents zoom on iOS */
            }

            /* Image preview mobile */
            .image-preview img {
                max-width: 150px;
                height: auto;
            }
        }

        @media (max-width: 480px) {
            .admin-header h1 {
                font-size: 1.5rem;
            }

            .admin-form {
                padding: 0.8rem;
            }

            .section-title {
                font-size: 1.3rem;
            }

            .projects-table {
                font-size: 0.75rem;
            }

            .projects-table .btn {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }

            .project-modal-content {
                width: 98%;
                margin: 0.5rem auto;
                padding: 0.8rem;
            }
        }

        /* Horizontal scroll for table on small screens */
        @media (max-width: 600px) {
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .projects-table {
                min-width: 600px;
            }
        }

        /* Mobile Responsive for Projects section */
        @media (max-width: 768px) {
            .projects-table .actions {
                display: flex;
                flex-direction: column;
                gap: 0.3rem;
            }

            .projects-table .actions button,
            .projects-table .actions form {
                width: 100%;
                margin: 0;
            }

            .projects-table .actions button {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }
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

        // Project Functions
        function showAddProjectForm() {
            document.getElementById('add-project-form').style.display = 'block';
        }

        function hideAddProjectForm() {
            document.getElementById('add-project-form').style.display = 'none';
        }

        function viewProject(id) {
            fetch('admin.php?action=get_project&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    const imageDisplay = data.image ?
                        `<img src="${data.image}" style="width:100%; max-width:300px; height:auto; border-radius:8px; margin:0 auto; display:block;" alt="Project Image">` :
                        '<div style="background:#f8f9fa; padding:1rem; text-align:center; border-radius:4px; color:#666;">No image uploaded</div>';

                    document.getElementById('view-project-content').innerHTML = `
                        <div style="display:grid; gap:1rem;">
                            
                            <!-- Project Image -->
                            <div>
                                <strong style="display:block; margin-bottom:0.5rem; color:#008080;">Project Image:</strong>
                                ${imageDisplay}
                            </div>
                            
                            <!-- Basic Info Grid -->
                            <div class="form-grid">
                               
                                <div>
                                    <strong style="color:#008080;">Category:</strong>
                                    <p style="margin:0.25rem 0;">${data.category || 'N/A'}</p>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <div>
                                <strong style="color:#008080;">Project Title:</strong>
                                <p style="margin:0.25rem 0; font-size:1.1rem; font-weight:600;">${data.title}</p>
                            </div>
                            
                            <!-- Technologies -->
                            <div>
                                <strong style="color:#008080;">Technologies Used:</strong>
                                <p style="margin:0.25rem 0;">${data.technologies || 'N/A'}</p>
                            </div>
                            
                            <!-- Links Grid -->
                            <div class="form-grid">
                                <div>
                                    <strong style="color:#008080;">GitHub Repository:</strong>
                                    <p style="margin:0.25rem 0;">${data.github_link ? `<a href="${data.github_link}" target="_blank" style="color:#00D4AA;">View Repository</a>` : 'N/A'}</p>
                                </div>
                                <div>
                                    <strong style="color:#008080;">Demo Link:</strong>
                                    <p style="margin:0.25rem 0;">${data.demo_link ? `<a href="${data.demo_link}" target="_blank" style="color:#00D4AA;">View Demo</a>` : 'N/A'}</p>
                                </div>
                            </div>
                            
                            <!-- Descriptions -->
                            <div>
                                <strong style="color:#008080;">Short Description:</strong>
                                <p style="margin:0.25rem 0; background:#f8f9fa; padding:0.75rem; border-radius:4px; border-left:3px solid #00D4AA;">${data.short_description}</p>
                            </div>
                            
                            <div>
                                <strong style="color:#008080;">Detailed Description:</strong>
                                <p style="margin:0.25rem 0; background:#f8f9fa; padding:0.75rem; border-radius:4px; border-left:3px solid #00D4AA; line-height:1.5;">${data.detailed_description}</p>
                            </div>
                            
                        </div>
                    `;

                    document.getElementById('view-project-modal').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading project details');
                });
        }

        function editProject(id) {
            fetch('admin.php?action=get_project&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('edit_project_id').value = data.id;
                    document.getElementById('edit_project_title').value = data.title;
                    document.getElementById('edit_project_category').value = data.category || '';
                    document.getElementById('edit_project_short_desc').value = data.short_description;
                    document.getElementById('edit_project_detailed_desc').value = data.detailed_description;
                    document.getElementById('edit_project_technologies').value = data.technologies || '';
                    document.getElementById('edit_project_github').value = data.github_link || '';
                    document.getElementById('edit_project_demo').value = data.demo_link || '';

                    // Show current image
                    const currentImageDisplay = data.image ? `<img src="${data.image}" style="width:100px; height:80px; border-radius:4px; object-fit:cover;" alt="Current Image">` : 'No image uploaded';
                    document.getElementById('current_project_image').innerHTML = currentImageDisplay;

                    document.getElementById('edit-project-modal').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                })
                .catch(error => alert('Error loading project data'));
        }

        function closeViewProjectModal() {
            document.getElementById('view-project-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeEditProjectModal() {
            document.getElementById('edit-project-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Skills Functions
        function showAddSkillForm() {
            document.getElementById('add-skill-form').style.display = 'block';
        }

        function hideAddSkillForm() {
            document.getElementById('add-skill-form').style.display = 'none';
        }

        function viewSkill(id) {
            fetch('admin.php?action=get_skill&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    let iconDisplay = '';
                    if (data.icon) {
                        iconDisplay = `<img src="${data.icon}" style="width:50px; height:50px; border-radius:4px;" alt="Skill Icon">`;
                    } else {
                        iconDisplay = '<i class="fa fa-code" style="font-size:30px; color:#008080;"></i>';
                    }

                    document.getElementById('view-skill-content').innerHTML = `
                        <table style="width:100%; border-collapse:collapse;">
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">ID:</td><td style="border:1px solid #ddd; padding:8px;">${data.id}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Icon:</td><td style="border:1px solid #ddd; padding:8px;">${iconDisplay}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Skill Name:</td><td style="border:1px solid #ddd; padding:8px;">${data.name}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Level:</td><td style="border:1px solid #ddd; padding:8px;">${data.level || 'N/A'}%</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Category:</td><td style="border:1px solid #ddd; padding:8px;">${data.category || 'N/A'}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Description:</td><td style="border:1px solid #ddd; padding:8px;">${data.description || 'N/A'}</td></tr>
                        </table>
                    `;
                    document.getElementById('view-skill-modal').style.display = 'block';
                })
                .catch(error => alert('Error loading skill details'));
        }

        function editSkill(id) {
            fetch('admin.php?action=get_skill&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('edit_skill_id').value = data.id;
                    document.getElementById('edit_skill_name').value = data.name;
                    document.getElementById('edit_skill_level').value = data.level || '';
                    document.getElementById('edit_skill_category').value = data.category || '';
                    document.getElementById('edit_skill_description').value = data.description || '';

                    // Populate icon URL field if it's a URL
                    if (data.icon && (data.icon.startsWith('http') || data.icon.startsWith('https'))) {
                        document.getElementById('edit_skill_icon_url').value = data.icon;
                    }

                    // Show current icon
                    let currentIconDisplay = '';
                    if (data.icon) {
                        currentIconDisplay = `<img src="${data.icon}" style="width:50px; height:50px; border-radius:4px;" alt="Current Icon">`;
                    } else {
                        currentIconDisplay = '<i class="fa fa-code" style="font-size:30px; color:#008080;"></i> <span>No icon uploaded</span>';
                    }
                    document.getElementById('current_skill_icon').innerHTML = currentIconDisplay;

                    document.getElementById('edit-skill-modal').style.display = 'block';
                })
                .catch(error => alert('Error loading skill data'));
        }

        function closeViewSkillModal() {
            document.getElementById('view-skill-modal').style.display = 'none';
        }

        function closeEditSkillModal() {
            document.getElementById('edit-skill-modal').style.display = 'none';
        }

        // Education Functions
        function showAddEducationForm() {
            document.getElementById('add-education-form').style.display = 'block';
        }

        function hideAddEducationForm() {
            document.getElementById('add-education-form').style.display = 'none';
        }

        function viewEducation(id) {
            fetch('admin.php?action=get_education&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('view-education-content').innerHTML = `
                        <table style="width:100%; border-collapse:collapse;">
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">ID:</td><td style="border:1px solid #ddd; padding:8px;">${data.id}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Institution:</td><td style="border:1px solid #ddd; padding:8px;">${data.institute}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Degree:</td><td style="border:1px solid #ddd; padding:8px;">${data.degree}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Year:</td><td style="border:1px solid #ddd; padding:8px;">${data.year}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Description:</td><td style="border:1px solid #ddd; padding:8px;">${data.description || 'N/A'}</td></tr>
                        </table>
                    `;
                    document.getElementById('view-education-modal').style.display = 'block';
                })
                .catch(error => alert('Error loading education details'));
        }

        function editEducation(id) {
            fetch('admin.php?action=get_education&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('edit_edu_id').value = data.id;
                    document.getElementById('edit_edu_institute').value = data.institute;
                    document.getElementById('edit_edu_degree').value = data.degree;
                    document.getElementById('edit_edu_year').value = data.year;
                    document.getElementById('edit_edu_description').value = data.description || '';
                    document.getElementById('edit-education-modal').style.display = 'block';
                })
                .catch(error => alert('Error loading education data'));
        }

        function closeViewModal() {
            document.getElementById('view-education-modal').style.display = 'none';
        }

        function closeEditModal() {
            document.getElementById('edit-education-modal').style.display = 'none';
        }

        // Experience Functions
        function showAddExperienceForm() {
            document.getElementById('add-experience-form').style.display = 'block';
        }

        function hideAddExperienceForm() {
            document.getElementById('add-experience-form').style.display = 'none';
        }

        function viewExperience(id) {
            fetch('admin.php?action=get_experience&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('view-experience-content').innerHTML = `
                        <table style="width:100%; border-collapse:collapse;">
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">ID:</td><td style="border:1px solid #ddd; padding:8px;">${data.id}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Company:</td><td style="border:1px solid #ddd; padding:8px;">${data.company}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Role:</td><td style="border:1px solid #ddd; padding:8px;">${data.role}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Duration:</td><td style="border:1px solid #ddd; padding:8px;">${data.year}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Location:</td><td style="border:1px solid #ddd; padding:8px;">${data.location || 'N/A'}</td></tr>
                            <tr><td style="border:1px solid #ddd; padding:8px; font-weight:bold;">Description:</td><td style="border:1px solid #ddd; padding:8px;">${data.description || 'N/A'}</td></tr>
                        </table>
                    `;
                    document.getElementById('view-experience-modal').style.display = 'block';
                })
                .catch(error => alert('Error loading experience details'));
        }

        function editExperience(id) {
            fetch('admin.php?action=get_experience&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    document.getElementById('edit_exp_id').value = data.id;
                    document.getElementById('edit_exp_company').value = data.company;
                    document.getElementById('edit_exp_role').value = data.role;
                    document.getElementById('edit_exp_year').value = data.year;
                    document.getElementById('edit_exp_location').value = data.location || '';
                    document.getElementById('edit_exp_description').value = data.description || '';
                    document.getElementById('edit-experience-modal').style.display = 'block';
                })
                .catch(error => alert('Error loading experience data'));
        }

        function closeViewExperienceModal() {
            document.getElementById('view-experience-modal').style.display = 'none';
        }

        function closeEditExperienceModal() {
            document.getElementById('edit-experience-modal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const viewModal = document.getElementById('view-project-modal');
            const editModal = document.getElementById('edit-project-modal');

            if (event.target === viewModal) {
                closeViewProjectModal();
            }
            if (event.target === editModal) {
                closeEditProjectModal();
            }
        });

        // Simple Image Previews
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Project Image Preview
            const editProjectImageInput = document.querySelector('input[name="edit_project_image"]');
            if (editProjectImageInput) {
                editProjectImageInput.addEventListener('change', function(e) {
                    const currentImageDiv = document.getElementById('current_project_image');

                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            if (!editProjectImageInput.originalContent) {
                                editProjectImageInput.originalContent = currentImageDiv.innerHTML;
                            }

                            currentImageDiv.innerHTML = `
                                <div>
                                    <strong>Current:</strong><br>
                                    ${editProjectImageInput.originalContent || 'No current image'}
                                </div>
                                <div style="margin-top: 1rem;">
                                    <strong>New:</strong><br>
                                    <img src="${ev.target.result}" style="width:150px; height:auto; margin-top:5px;" alt="New Image">
                                </div>
                            `;
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }

            // Add Project Image Preview
            const addProjectImageInput = document.querySelector('input[name="project_image"]');
            if (addProjectImageInput) {
                addProjectImageInput.addEventListener('change', function(e) {
                    const existingPreview = document.getElementById('simple-preview');
                    if (existingPreview) existingPreview.remove();

                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            const previewDiv = document.createElement('div');
                            previewDiv.id = 'simple-preview';
                            previewDiv.style.marginTop = '10px';
                            previewDiv.innerHTML = `<strong>Preview:</strong><br><img src="${ev.target.result}" style="width:150px; height:auto; margin-top:5px;" alt="Preview">`;
                            addProjectImageInput.parentNode.insertBefore(previewDiv, addProjectImageInput.nextSibling);
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }
        });

        // Add this function to your existing JavaScript
        function deleteProject(id) {
            if (confirm('Are you sure you want to delete this project?')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="project_id" value="${id}">`;

                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_project';
                deleteInput.value = '1';
                form.appendChild(deleteInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <!-- Admin Profile Section -->
        <div style="text-align:center; margin-bottom:2rem;">
            <i class="fa fa-user admin-icon"></i>
            <div style="font-weight:bold; font-size:1.2rem;">Rysul Aman Nirob</div>
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
                                    <button onclick="viewSkill(<?php echo $row['id']; ?>)" style="background:#17a2b8; color:white; border:none; padding:0.4rem 0.8rem; margin-right:0.3rem; border-radius:4px; cursor:pointer;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button onclick="editSkill(<?php echo $row['id']; ?>)" style="background:#ffc107; color:black; border:none; padding:0.4rem 0.8rem; margin-right:0.3rem; border-radius:4px; cursor:pointer;">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this skill?')">
                                        <input type="hidden" name="skill_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_skill" style="background:#dc3545; color:white; border:none; padding:0.4rem 0.8rem; border-radius:4px; cursor:pointer;">
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
            <h2>Projects Management</h2>

            <!-- Add Project Button -->
            <button onclick="showAddProjectForm()" style="background:#008080; margin-bottom:1rem;">
                <i class="fa fa-plus"></i> Add Project
            </button>

            <!-- Add Project Form (Hidden by default) -->
            <div id="add-project-form" style="display:none; background:#f9f9fa; padding:1.5rem; border-radius:8px; margin-bottom:2rem;">
                <h3>Add New Project</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <label>Project Title:</label>
                            <input type="text" name="project_title" placeholder="Project Name" required>
                        </div>
                        <div>
                            <label>Category:</label>
                            <input type="text" name="project_category" placeholder="e.g., Web Development, Mobile App">
                        </div>
                    </div>

                    <label>Short Description:</label>
                    <textarea name="project_short_desc" rows="2" placeholder="Brief description for project card" required></textarea>

                    <label>Detailed Description:</label>
                    <textarea name="project_detailed_desc" rows="4" placeholder="Detailed project description" required></textarea>

                    <label>Technologies Used:</label>
                    <input type="text" name="project_technologies" placeholder="e.g., HTML5, CSS3, JavaScript, PHP">

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <label>GitHub Repository:</label>
                            <input type="url" name="project_github" placeholder="https://github.com/username/repo">
                        </div>
                        <div>
                            <label>Demo Link:</label>
                            <input type="url" name="project_demo" placeholder="https://demo-site.com">
                        </div>
                    </div>

                    <label>Project Image:</label>
                    <input type="file" name="project_image" accept="image/*">

                    <div style="margin-top:1rem;">
                        <button type="submit" name="add_project">Save Project</button>
                        <button type="button" onclick="hideAddProjectForm()" style="background:#6c757d;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Projects Table -->
            <div style="background:#fff; border-radius:8px; overflow:hidden;">
                <table class="projects-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th class="hide-mobile">Category</th>
                            <th class="hide-mobile">Technologies</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo $project['id']; ?></td>
                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                <td class="hide-mobile"><?php echo htmlspecialchars($project['category']); ?></td>
                                <td class="hide-mobile"><?php echo htmlspecialchars($project['technologies']); ?></td>
                                <td>
                                    <?php if ($project['image']): ?>
                                        <img src="<?php echo $project['image']; ?>" style="width:40px;height:30px;object-fit:cover;border-radius:4px;" alt="Project">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <button onclick="viewProject(<?php echo $project['id']; ?>)" style="background:#17a2b8; color:white; border:none; padding:0.4rem 0.8rem; margin-right:0.3rem; border-radius:4px; cursor:pointer;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button onclick="editProject(<?php echo $project['id']; ?>)" style="background:#ffc107; color:black; border:none; padding:0.4rem 0.8rem; margin-right:0.3rem; border-radius:4px; cursor:pointer;">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this project?')">
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <button type="submit" name="delete_project" style="background:#dc3545; color:white; border:none; padding:0.4rem 0.8rem; border-radius:4px; cursor:pointer;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Education Section -->
        <div class="section" id="education" style="display:none;">
            <h2>Education Management</h2>

            <!-- Add Education Button -->
            <button onclick="showAddEducationForm()" style="background:#008080; margin-bottom:1rem;">
                <i class="fa fa-plus"></i> Add Education
            </button>

            <!-- Add Education Form (Hidden by default) -->
            <div id="add-education-form" style="display:none; background:#f9f9fa; padding:1.5rem; border-radius:8px; margin-bottom:2rem;">
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
            <div id="add-experience-form" style="display:none; background:#f9f9fa; padding:1.5rem; border-radius:8px; margin-bottom:2rem;">
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

    <!-- View Project Modal -->
    <div id="view-project-modal" class="project-modal-overlay">
        <div class="project-modal-content">
            <h3>Project Details</h3>
            <div id="view-project-content"></div>
            <div style="margin-top:1rem; text-align: right;">
                <button onclick="closeViewProjectModal()" style="background:#6c757d;">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div id="edit-project-modal" class="project-modal-overlay">
        <div class="project-modal-content">
            <h3>Edit Project</h3>
            <form method="POST" id="edit-project-form" enctype="multipart/form-data">
                <input type="hidden" name="edit_project_id" id="edit_project_id">

                <div class="form-grid">
                    <div>
                        <label>Project Title:</label>
                        <input type="text" name="edit_project_title" id="edit_project_title" required>
                    </div>
                    <div>
                        <label>Category:</label>
                        <input type="text" name="edit_project_category" id="edit_project_category">
                    </div>
                </div>

                <div class="form-full-width">
                    <label>Short Description:</label>
                    <textarea name="edit_project_short_desc" id="edit_project_short_desc" rows="2" required></textarea>
                </div>

                <div class="form-full-width">
                    <label>Detailed Description:</label>
                    <textarea name="edit_project_detailed_desc" id="edit_project_detailed_desc" rows="4" required></textarea>
                </div>

                <div class="form-full-width">
                    <label>Technologies Used:</label>
                    <input type="text" name="edit_project_technologies" id="edit_project_technologies">
                </div>

                <div class="form-grid">
                    <div>
                        <label>GitHub Repository:</label>
                        <input type="url" name="edit_project_github" id="edit_project_github">
                    </div>
                    <div>
                        <label>Demo Link:</label>
                        <input type="url" name="edit_project_demo" id="edit_project_demo">
                    </div>
                </div>

                <div class="form-full-width">

                    <div id="current_project_image" style="margin-bottom:1rem;"></div>
                </div>

                <div class="form-full-width">
                    <label>New Project Image:</label>
                    <input type="file" name="edit_project_image" accept="image/*">
                </div>

                <div style="margin-top:1rem; text-align: right;">
                    <button type="button" onclick="closeEditProjectModal()" style="background:#6c757d; margin-right: 1rem;">Cancel</button>
                    <button type="submit" name="update_project">Update Project</button>
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
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'project_added'): ?>
                showAlert('Project added successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'project_updated'): ?>
                showAlert('Project updated successfully!');
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'project_deleted'): ?>
                showAlert('Project deleted successfully!');
            <?php endif; ?>
        });
    </script>
</body>

</html>
<?php $conn->close();

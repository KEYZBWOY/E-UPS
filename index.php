<?php
session_start();
include('db_connection.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Handle file and cover upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['ebookTitle'];
    $target_dir = "uploads/";
    
    // Ebook file upload
    $ebook_file_name = basename($_FILES["ebookFile"]["name"]);
    $ebook_target_file = $target_dir . $ebook_file_name;
    $uploadOk = 1;
    $ebookFileType = strtolower(pathinfo($ebook_target_file, PATHINFO_EXTENSION));
    
    // Cover image upload
    $cover_file_name = basename($_FILES["coverImage"]["name"]);
    $cover_target_file = $target_dir . $cover_file_name;
    $coverFileType = strtolower(pathinfo($cover_target_file, PATHINFO_EXTENSION));

    // Allow only PDF or EPUB files for ebooks
    if ($ebookFileType != "pdf" && $ebookFileType != "epub") {
        $error = "Sorry, only PDF & EPUB files are allowed for ebooks.";
        $uploadOk = 0;
    }

    // Allow only JPG, PNG, JPEG for covers
    if($coverFileType != "jpg" && $coverFileType != "png" && $coverFileType != "jpeg") {
        $error = "Sorry, only JPG, JPEG, & PNG files are allowed for covers.";
        $uploadOk = 0;
    }

    // Move the uploaded files and insert into database
    if ($uploadOk && move_uploaded_file($_FILES["ebookFile"]["tmp_name"], $ebook_target_file) && move_uploaded_file($_FILES["coverImage"]["tmp_name"], $cover_target_file)) {
        $sql = "INSERT INTO ebooks (title, file_path, cover_path) VALUES ('$title', '$ebook_target_file', '$cover_target_file')";
        if ($conn->query($sql) === TRUE) {
            $success = "The file ". $ebook_file_name ." and cover ". $cover_file_name ." have been uploaded.";
        } else {
            $error = "Error uploading file to the database.";
        }
    } else {
        $error = "Sorry, there was an error uploading your files.";
    }
}

// Retrieve ebooks from the database
$result = $conn->query("SELECT * FROM ebooks");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-UPS</title>

    <!-- Add the logo to the browser tab (favicon) -->
    <link rel="icon" href="images/logo.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <!-- Website Logo -->
                <img src="images/logo.png" alt="E-UPS Logo" width="40" height="40" class="d-inline-block align-text-top">
                E-UPS
            </a>
            <div class="navbar-nav">
                <span class="nav-item nav-link">Logged in as: <?php echo $_SESSION['username']; ?></span>
                <a class="nav-item nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Display any success or error messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <h2>Upload a New Ebook</h2>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="ebookTitle" class="form-label">Ebook Title</label>
                <input type="text" class="form-control" id="ebookTitle" name="ebookTitle" required>
            </div>
            <div class="mb-3">
                <label for="ebookFile" class="form-label">Select Ebook (PDF/EPUB)</label>
                <input type="file" class="form-control" id="ebookFile" name="ebookFile" required>
            </div>
            <div class="mb-3">
                <label for="coverImage" class="form-label">Upload Cover Image (JPG/PNG)</label>
                <input type="file" class="form-control" id="coverImage" name="coverImage" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload Ebook</button>
        </form>

        <!-- Ebook Library -->
        <h2 class="mt-5">Available Ebooks</h2>
        <div class="row">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo $row['cover_path']; ?>" class="card-img-top" alt="Ebook Cover">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['title']; ?></h5>
                            <a href="<?php echo $row['file_path']; ?>" class="btn btn-primary" download>Download</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

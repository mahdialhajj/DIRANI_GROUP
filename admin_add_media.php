<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$company_id = $_GET['company_id'] ?? $_POST['company_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caption = $_POST['caption'];
    $description = $_POST['description'];
    $media_type = $_POST['media_type'];
    $video_url = $_POST['video_url'] ?? null;
    
    // Handle image upload
    $photo_path = null;
    if ($media_type == 'image' && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/company_photos/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $photo_path = $target_dir . time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }
    
    // Insert into database
    $sql = "INSERT INTO company_photos (company_id, photo_path, caption, description, media_type, video_embed_url) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$company_id, $photo_path, $caption, $description, $media_type, $video_url]);
    
    header("Location: company_profile.php?id=$company_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Media to Slideshow</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 600px; margin: auto; }
        form { background: #f5f5f5; padding: 20px; border-radius: 10px; }
        input, select, textarea { width: 100%; margin: 10px 0; padding: 8px; }
        button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .media-type-selector { margin: 20px 0; }
        .video-fields, .image-fields { display: none; }
    </style>
</head>
<body>
    <h2>Add Media to Slideshow</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
        
        <label>Media Type:</label>
        <select name="media_type" id="media_type" required>
            <option value="image">Image</option>
            <option value="video">Video</option>
        </select>
        
        <div id="image_fields" class="image-fields">
            <label>Upload Image:</label>
            <input type="file" name="photo" accept="image/*">
        </div>
        
        <div id="video_fields" class="video-fields">
            <label>Video URL (YouTube, Vimeo, or direct MP4):</label>
            <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
            <small>Supported: YouTube, Vimeo, or direct .mp4 file URL</small>
        </div>
        
        <label>Caption:</label>
        <input type="text" name="caption" required>
        
        <label>Description:</label>
        <textarea name="description" rows="3"></textarea>
        
        <button type="submit">Add to Slideshow</button>
    </form>
    
    <script>
        const mediaType = document.getElementById('media_type');
        const imageFields = document.getElementById('image_fields');
        const videoFields = document.getElementById('video_fields');
        
        function toggleFields() {
            if (mediaType.value === 'image') {
                imageFields.style.display = 'block';
                videoFields.style.display = 'none';
            } else {
                imageFields.style.display = 'none';
                videoFields.style.display = 'block';
            }
        }
        
        mediaType.addEventListener('change', toggleFields);
        toggleFields();
    </script>
</body>
</html>
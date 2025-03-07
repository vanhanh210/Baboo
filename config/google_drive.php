<?php
require '../vendor/autoload.php'; // Load Google API Client Library
require '../config/database.php'; // Include database connection

class GoogleDriveService {
    private $client;
    private $service;
    private $main_folder_id = "1yYhDtG3BxelEnr7wPDQDc2pRvPjtTkNH"; // Root Folder ID
    private $conn; // Database connection

    public function __construct($conn) {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=../config/credentials.json');

        try {
            $this->client = new Google_Client();
            $this->client->useApplicationDefaultCredentials();
            $this->client->setScopes([Google_Service_Drive::DRIVE_FILE]);

            $this->service = new Google_Service_Drive($this->client);
            $this->conn = $conn;
        } catch (Exception $e) {
            die("❌ Google Drive API Initialization Failed: " . $e->getMessage());
        }
    }

    /**
     * Upload single or multiple files to Google Drive with structured folders
     * @param array $files - The uploaded files from $_FILES
     * @param int $building_id - ID of the building
     * @param string|null $room_name - Optional room name for room images
     * @return array - Returns an array of Google Drive file URLs
     */
    public function uploadFilesAndSave($files, $building_id, $room_name = null) {
        $uploaded_file_urls = [];

        // Fetch building details
        $sql = "SELECT name, city, district FROM buildings WHERE building_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $building_id);
        $stmt->execute();
        $stmt->bind_result($building_name, $city, $district);
        $stmt->fetch();
        $stmt->close();

        if (!$building_name || !$city || !$district) {
            die("❌ Building data not found.");
        }

        // Create Google Drive Folder Structure: City → District → Building → Room
        $city_folder_id = $this->getOrCreateFolder($city, $this->main_folder_id);
        $district_folder_id = $this->getOrCreateFolder($district, $city_folder_id);
        $building_folder_id = $this->getOrCreateFolder($building_name, $district_folder_id);
        $parent_folder_id = $building_folder_id;

        if ($room_name) {
            $parent_folder_id = $this->getOrCreateFolder($room_name, $building_folder_id);
        }

        // ✅ Handle both single and multiple file uploads
        if (isset($files['tmp_name']) && is_array($files['tmp_name'])) {
            foreach ($files['tmp_name'] as $index => $tmp_name) {
                if (!empty($tmp_name) && file_exists($tmp_name)) {
                    $uploaded_url = $this->uploadSingleFile($tmp_name, $files['name'][$index], $files['type'][$index], $parent_folder_id, $room_name ?? $building_name);
                    if ($uploaded_url) {
                        $uploaded_file_urls[] = $uploaded_url;
                    }
                }
            }
        } elseif (!empty($files['tmp_name']) && file_exists($files['tmp_name'])) {
            $uploaded_url = $this->uploadSingleFile($files['tmp_name'], $files['name'], $files['type'], $parent_folder_id, $room_name ?? $building_name);
            if ($uploaded_url) {
                $uploaded_file_urls[] = $uploaded_url;
            }
        }

        // ✅ Store file URLs in the database
        if (!empty($uploaded_file_urls)) {
            $file_urls_json = json_encode($uploaded_file_urls); // Store as JSON

            if ($room_name) {
                $update_sql = "UPDATE rooms SET photo_urls = ? WHERE building_id = ? AND room_name = ?";
                $stmt = $this->conn->prepare($update_sql);
                $stmt->bind_param("sis", $file_urls_json, $building_id, $room_name);
            } else {
                $update_sql = "UPDATE buildings SET photo_urls = ? WHERE building_id = ?";
                $stmt = $this->conn->prepare($update_sql);
                $stmt->bind_param("si", $file_urls_json, $building_id);
            }

            $stmt->execute();
            $stmt->close();
        }

        return $uploaded_file_urls;
    }

    /**
     * Uploads a single file to Google Drive
     * @param string $file_tmp - Temporary file path
     * @param string $file_name - Original file name
     * @param string $file_mime - MIME type of the file
     * @param string $parent_folder_id - Google Drive parent folder ID
     * @param string $prefix - Prefix for the file name (Building/Room name)
     * @return string|null - Returns Google Drive file URL or null if failed
     */
    private function uploadSingleFile($file_tmp, $file_name, $file_mime, $parent_folder_id, $prefix) {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file_mime, $allowed_types)) {
            return null;
        }

        // Generate unique file name
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $unique_id = uniqid();
        $new_file_name = "{$prefix}_{$unique_id}.{$file_ext}";

        try {
            // Upload file to Google Drive
            $file_metadata = new Google_Service_Drive_DriveFile([
                'name' => $new_file_name,
                'parents' => [$parent_folder_id]
            ]);

            $content = file_get_contents($file_tmp);
            $uploadedFile = $this->service->files->create($file_metadata, [
                'data' => $content,
                'mimeType' => $file_mime,
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            if (!$uploadedFile) {
                return null;
            }

            $file_id = $uploadedFile->id;

            // Set file permissions to public
            $this->service->permissions->create($file_id, new Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]));

            return "https://drive.google.com/uc?id=" . $file_id;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Creates a folder in Google Drive or retrieves an existing one
     */
    private function getOrCreateFolder($folder_name, $parent_folder_id) {
        $query = sprintf(
            "name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed=false",
            addslashes($folder_name),
            addslashes($parent_folder_id)
        );

        $response = $this->service->files->listFiles(['q' => $query, 'fields' => 'files(id)']);

        if (count($response->files) > 0) {
            return $response->files[0]->id;
        }

        $folder_metadata = new Google_Service_Drive_DriveFile([
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parent_folder_id]
        ]);

        $folder = $this->service->files->create($folder_metadata, ['fields' => 'id']);
        return $folder->id;
    }


    /**
     * Converts Google Drive file URLs to direct image links
     */
    public function getDirectGoogleDriveImage($google_drive_link) {
        if (preg_match('/id=([a-zA-Z0-9_-]+)/', $google_drive_link, $matches) ||
            preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $google_drive_link, $matches)) {
            return "https://lh3.googleusercontent.com/d/" . $matches[1];
        }
        return false;
    }
}
?>

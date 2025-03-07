<?php
require '../vendor/autoload.php'; // Load Google API Client Library

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

class GoogleDriveTest {
    private $client;
    private $service;
    private $folder_id = "1yYhDtG3BxelEnr7wPDQDc2pRvPjtTkNH"; // Your folder ID
    private $service_account_email;

    public function __construct() {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=../config/credentials.json');

        try {
            $this->client = new Google_Client();
            $this->client->useApplicationDefaultCredentials();
            $this->client->setScopes([Google_Service_Drive::DRIVE]);

            $this->service = new Google_Service_Drive($this->client);
            echo "<p>✅ Google Drive API Connected Successfully!</p>";

            // Get Service Account Email from credentials.json
            $credentials = json_decode(file_get_contents('../config/credentials.json'), true);
            if (isset($credentials['client_email'])) {
                $this->service_account_email = $credentials['client_email'];
            } else {
                $this->service_account_email = "❌ Service account email not found!";
            }
        } catch (Exception $e) {
            die("<p>❌ Google Drive API Connection Failed: " . $e->getMessage() . "</p>");
        }
    }

    public function checkFolderAccess() {
        echo "<p>🔍 Checking Folder ID: <strong>" . htmlspecialchars($this->folder_id) . "</strong></p>";
        echo "<p>🔍 Service Account Email: <strong>" . htmlspecialchars($this->service_account_email) . "</strong></p>";

        try {
            $query = "'{$this->folder_id}' in parents and trashed=false";
            $results = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'spaces' => 'drive'
            ]);

            if (count($results->getFiles()) === 0) {
                echo "<p>⚠️ No files found. Make sure:</p>";
                echo "<ul>";
                echo "<li>✅ The folder ID <strong>{$this->folder_id}</strong> is correct.</li>";
                echo "<li>✅ The service account email <strong>{$this->service_account_email}</strong> has <strong>Editor</strong> access.</li>";
                echo "<li>✅ You uploaded a test file manually to the folder.</li>";
                echo "</ul>";
            } else {
                echo "<p>✅ Folder Access Verified! Listing files:</p>";
                echo "<ul>";
                foreach ($results->getFiles() as $file) {
                    echo "<li>📂 <a href='https://drive.google.com/file/d/{$file->getId()}/view' target='_blank'>" . htmlspecialchars($file->getName()) . "</a></li>";
                }
                echo "</ul>";
            }
        } catch (Exception $e) {
            die("<p>❌ Failed to access folder: " . $e->getMessage() . "</p>");
        }
    }
}

// Run the test
$test = new GoogleDriveTest();
$test->checkFolderAccess();
?>

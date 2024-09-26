<?php
include 'db.php';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Fetch request body
// Initialize $data
$data = null;

// Fetch request body based on request method
if ($method === 'POST' || $method === 'PUT') {
    // For POST and PUT requests, get the raw input
    $input = file_get_contents('php://input');
    
    if (strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false) {
        parse_str($input, $data); // Parse the raw input into an associative array
    } else {
        // Otherwise, assume the input is in JSON format
        $data = json_decode($input, true);
    }
}

// Route handling
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            fetchJobById($mysqli, $_GET['id']);
        } else {
            fetchAllJobs($mysqli);
        }
        break;
    case 'POST':
            createJob($mysqli, $data);
        break;
    case 'PUT':
        if (isset($_GET['id'])) {
            editJob($mysqli, $_GET['id'], $data);
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteUserAndCompany($mysqli, $_GET['id']);
        }
        break;
    default:
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

// Fetch all jobs
function fetchAllJobs($mysqli) {
    // SQL query to fetch job details along with the associated company
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $query = "
        SELECT 
            jobs.id, 
            jobs.title, 
            jobs.type, 
            jobs.location, 
            jobs.description, 
            jobs.salary, 
            companies.name AS company_name, 
            companies.description AS company_description, 
            companies.contact_email, 
            companies.contact_phone
        FROM jobs
        JOIN companies ON jobs.company_id = companies.id
        ORDER BY jobs.id DESC
    ";

      // If a limit is provided, append the LIMIT clause to the query
      if ($limit) {
        $query .= " LIMIT ?";
    }

    $stmt = $mysqli->prepare($query);

    // If a limit is set, bind the parameter to the statement
    if ($limit) {
        $stmt->bind_param('i', $limit);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $jobs = [];

    while ($row = $result->fetch_assoc()) {
        $jobs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'type' => $row['type'],
            'location' => $row['location'],
            'description' => $row['description'],
            'salary' => $row['salary'],
            'company' => [
                'name' => $row['company_name'],
                'description' => $row['company_description'],
                'contactEmail' => $row['contact_email'],
                'contactPhone' => $row['contact_phone']
            ]
        ];
    }

   
    echo json_encode($jobs);
}

function fetchJobById($mysqli, $jobId) {
    $query = "
        SELECT 
            jobs.id,
            jobs.title, 
            jobs.type, 
            jobs.location, 
            jobs.description, 
            jobs.salary, 
            companies.id AS company_id, 
            companies.name AS company_name, 
            companies.description AS company_description, 
            companies.contact_email, 
            companies.contact_phone
        FROM jobs
        JOIN companies ON jobs.company_id = companies.id
        WHERE jobs.id = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $jobId); 
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $job = [
            'id' => $row['id'],
            'title' => $row['title'],
            'type' => $row['type'],
            'location' => $row['location'],
            'description' => $row['description'],
            'salary' => $row['salary'],
            'company' => [
                'id' => $row['company_id'],
                'name' => $row['company_name'],
                'description' => $row['company_description'],
                'contactEmail' => $row['contact_email'],
                'contactPhone' => $row['contact_phone']
            ]
        ];

        echo json_encode($job);
    } else {
        echo json_encode([
            'error' => 'Job not found'
        ]);
    }
}


// Create a new job
function createJob($mysqli, $data) {
    // Get company data from $data array
    $companyName = $data['company']['name'];
    $companyDescription = $data['company']['description'];
    $companyEmail = $data['company']['contactEmail'];
    $companyPhone = $data['company']['contactPhone'];

    // Check if the company already exists based on name
    $companyCheckQuery = "SELECT id FROM companies WHERE name = ?";
    $stmt = $mysqli->prepare($companyCheckQuery);
    $stmt->bind_param('s', $companyName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'error' => 'Company already exists!',
           
        ]);
        return;
    }
        $companyInsertQuery = "INSERT INTO companies (name, description, contact_email, contact_phone) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($companyInsertQuery);
        $stmt->bind_param('ssss', $companyName, $companyDescription, $companyEmail, $companyPhone);
        $stmt->execute();
        $companyId = $mysqli->insert_id; // Get the last inserted company ID
    

    $jobTitle = $data['title'];
    $jobType = $data['type'];
    $jobLocation = $data['location'];
    $jobDescription = $data['description'];
    $jobSalary = $data['salary'];

    $jobInsertQuery = "INSERT INTO jobs (title, type, description, location, salary, company_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($jobInsertQuery);
    $stmt->bind_param('sssssi', $jobTitle, $jobType, $jobDescription, $jobLocation, $jobSalary, $companyId);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => 'Job created successfully',
            'jobId' => $mysqli->insert_id
        ]);
    } else {
        echo json_encode([
            'error' => 'Failed to create job'
        ]);
    }
}



// edit job
function editJob($mysqli, $jobId, $data) {
    // Get job data from $data array
    $jobTitle = $data['title'];
    $jobType = $data['type'];
    $jobLocation = $data['location'];
    $jobDescription = $data['description'];
    $jobSalary = $data['salary'];
    
    // Get company data
    $companyId = $data['company']['id']; 
    $companyName = $data['company']['name'];
    $companyDescription = $data['company']['description'];
    $companyEmail = $data['company']['contactEmail'];
    $companyPhone = $data['company']['contactPhone'];

    // Update company details using the company ID
    $companyUpdateQuery = "UPDATE companies SET name = ?, description = ?, contact_email = ?, contact_phone = ? WHERE id = ?";
    $stmt = $mysqli->prepare($companyUpdateQuery);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare company update statement: ' . $mysqli->error]);
        return;
    }
    
    $stmt->bind_param('ssssi', $companyName, $companyDescription, $companyEmail, $companyPhone, $companyId);
    
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to update company: ' . $stmt->error]);
        return; 
    }

    // Update job details using the job ID
    $jobUpdateQuery = "UPDATE jobs SET title = ?, type = ?, description = ?, location = ?, salary = ? WHERE id = ?";
    $stmt = $mysqli->prepare($jobUpdateQuery);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare job update statement: ' . $mysqli->error]);
        return;
    }
    
    $stmt->bind_param('sssssi', $jobTitle, $jobType, $jobDescription, $jobLocation, $jobSalary, $jobId);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Job and company updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update job: ' . $stmt->error]);
    }
}




// Delete job and company
function deleteUserAndCompany($mysqli, $userId) {
    try {
        // Step 1: Get the company ID associated with the user
        $companyQuery = "SELECT company_id FROM jobs WHERE id = ?";
        $stmt = $mysqli->prepare($companyQuery);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'User not found']);
            return;
        }

        $row = $result->fetch_assoc();
        $companyId = $row['company_id'];
   var_dump($companyId);
  
       $deleteUserQuery = "DELETE FROM jobs WHERE id = ?";
       $stmt = $mysqli->prepare($deleteUserQuery);
       $stmt->bind_param('i', $userId);

       if (!$stmt->execute()) {
           throw new Exception('Failed to delete user: ' . $stmt->error);
       }
   $deleteCompanyQuery = "DELETE FROM companies WHERE id = ?";
   $stmt = $mysqli->prepare($deleteCompanyQuery);
   $stmt->bind_param('i', $companyId);

   if (!$stmt->execute()) {
       throw new Exception('Failed to delete company: ' . $stmt->error);
   }


        echo json_encode(['success' => 'User and company deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}


?>

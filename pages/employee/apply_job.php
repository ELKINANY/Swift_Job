<?php
require '../db_connection.php';
include "../navBar.php";

// Define all possible questions
$allQuestions = [
    'q1' => 'ما هي أهم مهاراتك التي تتناسب مع هذه الوظيفة؟',
    'q2' => 'ما هي أهدافك المهنية على المدى الطويل؟',
    'q3' => 'كيف علمت عن هذه الوظيفة؟',
    'q4' => 'ما هي أكبر إنجازاتك المهنية حتى الآن؟',
    'q5' => 'كيف تتعامل مع ضغط العمل؟',
    'q6' => 'ما هي نقاط القوة والضعف لديك؟',
    'q7' => 'ما هو أكبر تحدٍ واجهته في عملك السابق وكيف تغلبت عليه؟',
    'q8' => 'لماذا تعتقد أنك مؤهل لهذه الوظيفة؟',
    'q9' => 'ما هي توقعاتك من بيئة العمل؟',
    'q10' => 'كيف تتعامل مع النزاعات في مكان العمل؟'
];

// Select 5 random questions
$randomKeys = array_rand($allQuestions, 5);
$selectedQuestions = array_intersect_key($allQuestions, array_flip($randomKeys));

// Check if user is logged in (assuming user_id is in $_SESSION)
// Redirect or handle if not logged in - added a basic check
if (!isset($_SESSION['user_id'])) {
    // Handle unauthorized access, maybe redirect to login
    header("Location: login.php"); // Replace login.php with your actual login page
    exit();
}
$userId = $_SESSION['user_id'];

// استقبال معرف الوظيفة
// Basic check for job_id
if (!isset($_GET['job_id'])) {
    // Handle missing job ID, maybe redirect to jobs list
    header("Location: findJobs.php"); // Replace jobs.php with your actual jobs list page
    exit();
}
$jobId = $_GET['job_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // معالجة تحميل السيرة الذاتية
    $resume = $_FILES['resume'];
    $uploadDir = 'uploads/';
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($resume['name'], PATHINFO_EXTENSION));

    // Check if directory exists, if not create it
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Use 0777 for testing, consider stricter permissions in production
    }

    $newFileName = uniqid() . '.' . $fileType; // Generate unique file name
    $targetFile = $uploadDir . $newFileName;

    // Check file size (e.g., 5MB limit)
    if ($resume['size'] > 5000000) {
        echo '<script>alert("عذراً، ملف السيرة الذاتية كبير جداً.");</script>';
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
        echo '<script>alert("عذراً، يُسمح فقط بملفات PDF و DOC و DOCX.");</script>';
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo '<script>alert("عذراً، ملف السيرة الذاتية لم يتم تحميله.");</script>';
    } else {
        // Try to upload file
        if (move_uploaded_file($resume['tmp_name'], $targetFile)) {
            $questionsAnswers = serialize($_POST['questions']);

            // Sanitize input data
            $coverLetter = htmlspecialchars($_POST['cover_letter']);
            $expectedSalary = filter_var($_POST['expected_salary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $whyJob = htmlspecialchars($_POST['why_job']);
            $whyCompany = htmlspecialchars($_POST['why_company']);
            $phone = htmlspecialchars($_POST['phone']);

            // Check if salary is a valid number
            if ($expectedSalary === false) {
                 echo '<script>alert("الراتب المتوقع يجب أن يكون رقماً صالحاً.");</script>';
                 // Handle invalid salary input, maybe don't insert into DB
            } else {
                 $query = "INSERT INTO job_applications (job_id, user_id, cover_letter, expected_salary, resume, applied_at, why_job, why_company, phone, additional_questions)
                          VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
                 $stmt = $conn->prepare($query);

                 // Use the new unique file name for the database
                 $dbResumeName = $newFileName;

                 $stmt->bind_param("iisssssss",
                     $jobId,
                     $userId,
                     $coverLetter,
                     $expectedSalary,
                     $dbResumeName, // Use the new file name here
                     $whyJob,
                     $whyCompany,
                     $phone,
                     $questionsAnswers
                 );

                 if ($stmt->execute()) {
                     echo '<script>alert("تم تقديم طلبك بنجاح!"); window.location.href = "findJobs.php";</script>'; // Redirect after success
                 } else {
                     echo '<script>alert("حدث خطأ أثناء حفظ بيانات الطلب: ' . $stmt->error . '");</script>';
                 }
                 $stmt->close();
            }
        } else {
            echo '<script>alert("عذراً، حدث خطأ أثناء تحميل ملف السيرة الذاتية.");</script>';
        }
    }
    // Close DB connection after use
    $conn->close();
    exit(); // Stop execution after POST
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تقديم على وظيفة</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
  /* Basic Reset and Body Styling */
  body {
    font-family: 'Cairo', sans-serif;
    /* Use the modern font */
    margin: 0;
    padding: 20px;
    /* Add some padding around the body */
    background-color: #f0f2f5;
    /* Light background color */
    color: #333;
    /* Default text color */
    direction: rtl;
    /* Right-to-left for Arabic */
    text-align: right;
    /* Align text to the right */
  }

  .form-container {
    max-width: 700px;
    /* Adjust max-width for better readability */
    margin: 40px auto;
    /* Center the form with more vertical margin */
    background-color: #fff;
    /* White background for the form */
    padding: 30px;
    /* More padding inside the form */
    border-radius: 12px;
    /* Softer rounded corners */
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    /* More pronounced shadow */
    border: 1px solid #ddd;
    /* Subtle border */
  }

  h2,
  h3 {
    color: #0056b3;
    /* A pleasant blue for headings */
    margin-bottom: 25px;
    /* Space after headings */
    text-align: center;
    /* Center headings */
    position: relative;
    /* For potential pseudo-element underline */
  }

  /* Optional: Add an underline effect to headings */
  h2::after,
  h3::after {
    content: '';
    position: absolute;
    bottom: -10px;
    right: 50%;
    /* Start from center */
    transform: translateX(50%);
    /* Move back by half its width to truly center */
    width: 60px;
    /* Width of the underline */
    height: 3px;
    background-color: #007bff;
    /* Blue underline color */
    border-radius: 2px;
  }


  label {
    display: block;
    /* Label on its own line */
    margin-bottom: 8px;
    /* Space between label and input */
    font-weight: 600;
    /* Slightly bolder labels */
    color: #555;
    /* Darker grey for labels */
    font-size: 1rem;
    /* Standard font size */
  }

  input[type="file"],
  input[type="number"],
  textarea {
    width: calc(100% - 24px);
    /* Full width minus padding */
    padding: 12px;
    /* Padding inside fields */
    margin-bottom: 20px;
    /* Space below fields */
    border: 1px solid #ccc;
    /* Light grey border */
    border-radius: 6px;
    /* Rounded corners for fields */
    font-size: 1rem;
    /* Consistent font size */
    box-sizing: border-box;
    /* Include padding and border in element's total width */
    transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    /* Smooth transition for focus */
    font-family: 'Cairo', sans-serif;
    /* Apply font to inputs */
    text-align: right;
    /* Ensure text input is right-aligned */
  }

  input[type="file"] {
    padding-top: 10px;
    /* Adjust padding for file input */
    padding-bottom: 10px;
  }

  input[type="number"]::-webkit-outer-spin-button,
  input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  input[type="number"] {
    -moz-appearance: textfield;
    /* Firefox */
  }


  textarea {
    min-height: 120px;
    /* Taller text areas */
    resize: vertical;
    /* Allow vertical resizing only */
  }

  /* Focus state for inputs and textareas */
  input[type="file"]:focus,
  input[type="number"]:focus,
  textarea:focus {
    border-color: #007bff;
    /* Highlight border on focus */
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
    /* Add a subtle shadow on focus */
    outline: none;
    /* Remove default outline */
  }

  input[type="submit"] {
    display: block;
    /* Button on its own line */
    width: 100%;
    /* Full width button */
    background-color: #28a745;
    /* Green background */
    color: white;
    /* White text */
    padding: 15px 20px;
    /* More padding for a larger button */
    border: none;
    /* No border */
    border-radius: 6px;
    /* Rounded corners */
    cursor: pointer;
    /* Indicate it's clickable */
    font-size: 1.1rem;
    /* Larger font size */
    font-weight: 700;
    /* Bold text */
    transition: background-color 0.3s ease, transform 0.1s ease;
    /* Smooth transition for hover/active */
    margin-top: 30px;
    /* Space above the button */
  }

  input[type="submit"]:hover {
    background-color: #218838;
    /* Darker green on hover */
  }

  input[type="submit"]:active {
    background-color: #1e7e34;
    /* Even darker green when clicked */
    transform: translateY(1px);
    /* Add a slight press effect */
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .form-container {
      max-width: 95%;
      /* Wider on smaller screens */
      padding: 20px;
      /* Less padding */
      margin: 20px auto;
      /* Less vertical margin */
    }

    input[type="file"],
    input[type="number"],
    textarea {
      width: calc(100% - 20px);
      /* Adjust width for smaller padding */
      padding: 10px;
      /* Less padding */
    }

    input[type="submit"] {
      padding: 12px 15px;
      /* Smaller padding for the button */
      font-size: 1rem;
      /* Smaller font for the button */
    }
  }

  @media (max-width: 480px) {
    .form-container {
      padding: 15px;
      /* Even less padding on very small screens */
    }

    input[type="file"],
    input[type="number"],
    textarea {
      width: calc(100% - 16px);
      /* Adjust width */
      padding: 8px;
      /* Even less padding */
      font-size: 0.9rem;
      /* Smaller font size */
    }

    label {
      font-size: 0.95rem;
      /* Slightly smaller label font */
    }

    h2,
    h3 {
      font-size: 1.3rem;
      /* Smaller headings */
      margin-bottom: 15px;
    }

    h2::after,
    h3::after {
      bottom: -5px;
    }

    input[type="submit"] {
      padding: 10px 15px;
      font-size: 0.95rem;
    }
  }
  </style>
</head>

<body>

  <div class="form-container">
    <h2>تقديم طلب وظيفة</h2>
    <form method="post" enctype="multipart/form-data">
      <label for="resume">تحميل السيرة الذاتية (PDF, DOC, DOCX):</label>
      <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx" required>

      <label for="cover_letter">خطاب التقديم:</label>
      <textarea name="cover_letter" id="cover_letter" required
        placeholder="اكتب هنا عن اهتمامك بالوظيفة وكيف تتناسب مؤهلاتك..."></textarea>

      <label for="expected_salary">الراتب المتوقع (بالعملة المحلية):</label>
      <input type="number" name="expected_salary" id="expected_salary" step="0.01" required placeholder="مثال: 5000">

      <h3>الاسئلة الخاصة بالتقديم:</h3>
      <label for="why_job">لماذا تريد العمل في هذه الوظيفة؟</label>
      <textarea name="why_job" id="why_job" required placeholder="اشرح دوافعك للتقدم لهذه الوظيفة بالذات..."></textarea>

      <label for="why_company">لماذا تريد العمل في هذه الشركة؟</label>
      <textarea name="why_company" id="why_company" required
        placeholder="وضح لماذا هذه الشركة تحديداً تثير اهتمامك..."></textarea>

      <p>يرجى الاجابة على الاسئلة التالية:</p>
      <?php foreach ($selectedQuestions as $key => $question): ?>
      <label for="<?php echo $key; ?>"><?php echo $question; ?></label>
      <textarea name="questions[<?php echo $key; ?>]" id="<?php echo $key; ?>" required
        placeholder="اجابتك على السؤال..."></textarea>
      <?php endforeach; ?>

      <label for="phone">رقم الهاتف (واتساب):</label>
      <input type="text" name="phone" id="phone" required placeholder="مثال: 009665... أو +9665...">
      <input type="submit" value="تقديم الطلب">
    </form>
  </div>

</body>

</html>
<?php
ob_start();
session_start();
require '../db_connection.php';
include "../navBar.php";

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) die("يرجى تسجيل الدخول.");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['next'])) {
  $answer = $_POST['answer'];
  $questionId = $_POST['question_id'];

  if (!empty($answer)) {
    $_SESSION['answers'][$questionId] = $answer;

    $answerText = is_array($answer) ? json_encode($answer, JSON_UNESCAPED_UNICODE) : $answer;

    $stmt = $conn->prepare("INSERT INTO interview_answers (user_id, question_id, answer) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $questionId, $answerText);
    $stmt->execute();
    $stmt->close();
  }

  $_SESSION['question_index']++;
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retry'])) {
  unset($_SESSION['interview_type'], $_SESSION['question_index'], $_SESSION['answers']);
  header("Location: interview_sim.php");
  exit();
}

$questions = [];
if (isset($_SESSION['interview_type'])) {
  $type = $_SESSION['interview_type'];
  $query = "SELECT id, question, options, correct_answers, is_multiple, score FROM interview_questions WHERE type = ? LIMIT 5";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $type);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $questions[$row['id']] = $row;
  }
  $stmt->close();
}

$questionIndex = $_SESSION['question_index'] ?? 0;
$totalQuestions = count($questions);
$isFinished = $questionIndex >= $totalQuestions;
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>أسئلة المقابلة</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f4f7fa;
    color: #333;
    padding-top: 2rem;
  }

  .container {
    max-width: 800px;
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  h1 {
    font-size: 2rem;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .question-box {
    margin-bottom: 1.5rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
  }

  .option {
    display: flex;
    align-items: center;
    margin: 0.5rem 0;
  }

  .option input {
    margin-left: 0.5rem;
  }

  .btn-custom {
    background: #3b82f6;
    color: #fff;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 5px;
    transition: all 0.3s ease;
  }

  .btn-custom:hover {
    background: #2563eb;
    transform: translateY(-2px);
  }

  .feedback {
    margin-top: 1.5rem;
    padding: 1rem;
    border-radius: 8px;
  }

  .feedback-success {
    background: #d1fae5;
    color: #065f46;
  }

  .feedback-tip {
    background: #fee2e2;
    color: #991b1b;
  }

  @media (max-width: 768px) {
    .container {
      padding: 1rem;
    }

    h1 {
      font-size: 1.5rem;
    }
  }
  </style>
</head>

<body>
  <div class="container">
    <h1>أسئلة المقابلة <i class="fas fa-user-tie"></i></h1>

    <?php if (!$isFinished): ?>
    <?php
    $currentQuestion = $questions[array_keys($questions)[$questionIndex]];
    $options = json_decode($currentQuestion['options'], true);
    $isMultiple = $currentQuestion['is_multiple'] ? true : false;
    ?>
    <div class="question-box">
      <p><strong>السؤال <?= $questionIndex + 1 ?> من <?= $totalQuestions ?>:</strong>
        <?= htmlspecialchars($currentQuestion['question']) ?></p>
      <form method="post">
        <?php foreach ($options as $option): ?>
        <div class="option">
          <input type="<?= $isMultiple ? 'checkbox' : 'radio' ?>" name="answer<?= $isMultiple ? '[]' : '' ?>"
            value="<?= htmlspecialchars($option) ?>" <?= !$isMultiple ? 'required' : '' ?>>
          <?= htmlspecialchars($option) ?>
        </div>
        <?php endforeach; ?>
        <input type="hidden" name="question_id" value="<?= $currentQuestion['id'] ?>">
        <button type="submit" name="next" class="btn btn-custom mt-2">التالي</button>
      </form>
    </div>

    <?php else: ?>
    <?php
    $score = 0;

    foreach ($_SESSION['answers'] as $questionId => $answer) {
      $correctAnswers = json_decode($questions[$questionId]['correct_answers'], true);
      if (!is_array($answer)) {
        $answer = [$answer];
      }
      sort($correctAnswers);
      sort($answer);
      if ($answer === $correctAnswers) {
        $score += $questions[$questionId]['score'];
      }
    }

    $finalScore = $score;
    $feedback = "أجبت عن $totalQuestions أسئلة. درجتك النهائية: $finalScore من " . array_sum(array_column($questions, 'score')) . ".";
    $tips = [];
    if ($finalScore >= 4) $tips[] = "✅ ممتاز، أدائك رائع!";
    elseif ($finalScore >= 2) $tips[] = "❗ جيد، لكن حاول مراجعة الإجابات.";
    else $tips[] = "💡 تحتاج لمزيد من التحضير.";
    ?>
    <div class="feedback feedback-success"><?= $feedback ?></div>
    <?php foreach ($tips as $tip): ?>
    <div class="feedback feedback-tip"><?= $tip ?></div>
    <?php endforeach; ?>
    <form method="post">
      <button type="submit" name="retry" class="btn btn-custom mt-3">أعد المحاولة</button>
    </form>
    <?php endif; ?>
  </div>
</body>

</html>

<?php
ob_end_flush();
$conn->close();
?>
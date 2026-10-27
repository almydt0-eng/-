<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND status = 'completed' ORDER BY id DESC");
$stmt->execute([$user_id]);
$archived_tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الأرشيف الأمني المشفر - جهاز الأمن والمخابرات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #030712; color: #00f0ff; min-height: 100vh; display: flex; flex-direction: column; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: rgba(5, 13, 26, 0.95); border-bottom: 2px solid #00f0ff; }
        .card-cyber { background: rgba(8, 18, 35, 0.9); border: 1px solid #1e3a8a; border-radius: 14px; color: #fff; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); }
        .table-dark { background-color: rgba(4, 11, 24, 0.95); }
        footer { background: rgba(2, 6, 23, 0.98); border-top: 2px solid #1e3a8a; color: #93c5fd; padding: 20px 0; margin-top: auto; text-align: center; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg px-4">
        <a class="navbar-brand fw-bold text-info" href="index.php"><i class="fas fa-shield-alt text-danger me-2"></i> جهاز الأمن والمخابرات | الأرشيف المركزي</a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-outline-info btn-sm fw-bold"><i class="fas fa-arrow-right me-1"></i> العودة لغرفة العمليات</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card-cyber p-4">
            <h3 class="text-info fw-bold mb-4"><i class="fas fa-archive me-2"></i> سجل الأرشيف والملفات الأمنية المؤمنة</h3>
            <?php if (count($archived_tasks) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle border-secondary text-white">
                        <thead>
                            <tr class="text-warning">
                                <th>كود العملية المؤرشفة</th>
                                <th>مستوى السرية</th>
                                <th>التقرير الاستخباراتي</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archived_tasks as $task): ?>
                                <tr>
                                    <td class="fw-bold text-success"><?= htmlspecialchars($task['title']) ?></td>
                                    <td><span class="badge bg-success">مؤمنة بالكامل</span></td>
                                    <td class="text-light small"><?= htmlspecialchars($task['description']) ?></td>
                                    <td><span class="badge bg-secondary">مؤرشف رسمياً</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-lock fa-3x mb-3 text-warning"></i>
                    <p class="fs-5">لا توجد ملفات مؤرشفة حالياً في السجلات السرية.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p class="mb-1 fw-bold">تصميم وبرمجة: <span class="text-warning">طارق أحمد محمد الشويع</span> | هاتف: <span class="text-info">771251114</span></p>
        </div>
    </footer>
</body>
</html>

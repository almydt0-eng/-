<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$military_id = $_SESSION['military_id'] ?? 'SEC-9042';
$message = "";

// إضافة عملية جديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'] ?? 'normal';

    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $priority]);
        header("Location: index.php");
        exit();
    } else {
        $message = "<div class='alert alert-warning text-center fw-bold'>تنبيه أمني: كود العملية مطلوب!</div>";
    }
}

// حذف سجل
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    header("Location: index.php");
    exit();
}

// تغيير حالة التنفيذ
if (isset($_GET['toggle'])) {
    $task_id = $_GET['toggle'];
    $stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();

    if ($task) {
        $new_status = ($task['status'] == 'pending') ? 'completed' : 'pending';
        $update = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $update->execute([$new_status, $task_id]);
        header("Location: index.php");
        exit();
    }
}

// جلب العمليات والإحصائيات
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

$total_tasks = count($tasks);
$pending_tasks = 0;
$completed_tasks = 0;
foreach($tasks as $t) {
    if($t['status'] == 'completed') $completed_tasks++;
    else $pending_tasks++;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>غرفة العمليات المركزية - جهاز الأمن والمخابرات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #030712; 
            color: #00f0ff; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        #cyber-canvas {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; opacity: 0.2;
        }
        .navbar { 
            background: rgba(5, 13, 26, 0.95); border-bottom: 2px solid #00f0ff; box-shadow: 0 0 30px rgba(0,240,255,0.4); padding: 15px 20px;
        }
        .card-cyber { 
            background: rgba(8, 18, 35, 0.9); border: 1px solid #1e3a8a; border-radius: 14px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); color: #fff;
        }
        .form-control, .form-select { 
            background: #020617; border: 1px solid #1e3a8a; color: #00f0ff; padding: 12px; border-radius: 8px;
        }
        .form-control:focus, .form-select:focus { 
            background: #020617; color: #00f0ff; border-color: #ffaa00; box-shadow: 0 0 15px rgba(255,170,0,0.4); 
        }
        .radar-box {
            width: 80px; height: 80px; border: 2px solid #00f0ff; border-radius: 50%; position: relative;
            background: radial-gradient(circle, rgba(0,240,255,0.15) 0%, rgba(0,0,0,0.9) 75%); margin: 0 auto;
        }
        .radar-sweep {
            width: 50%; height: 50%; background: linear-gradient(45deg, rgba(0,240,255,0.9), transparent);
            position: absolute; top: 0; left: 0; transform-origin: bottom right; animation: radarSpin 2s linear infinite; border-radius: 100% 0 0 0;
        }
        @keyframes radarSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .badge-top-secret { background: #dc2626; color: #fff; font-weight: bold; }
        .badge-urgent { background: #d97706; color: #000; font-weight: bold; }
        .badge-normal { background: #0284c7; color: #fff; font-weight: bold; }
        .table-dark { background-color: rgba(4, 11, 24, 0.95); }
        footer {
            background: rgba(2, 6, 23, 0.98); border-top: 2px solid #1e3a8a; color: #93c5fd; padding: 20px 0; margin-top: auto; text-align: center;
        }
    </style>
</head>
<body>

    <canvas id="cyber-canvas"></canvas>

    <nav class="navbar navbar-dark navbar-expand-lg">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-info fs-5" href="#">
                <i class="fas fa-shield-alt text-danger me-2"></i> جهاز الأمن والمخابرات <span class="text-warning fs-6">| غرفة العمليات المركزية</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3 small"><i class="fas fa-id-badge text-info me-1"></i> الضابط: <strong class="text-white"><?= htmlspecialchars($username) ?></strong> [رقم عسكري: <span class="text-warning"><?= htmlspecialchars($military_id) ?></span>]</span>
                <a href="security_archive.php" class="btn btn-outline-warning btn-sm me-2 fw-bold"><i class="fas fa-archive me-1"></i> الأرشيف الأمني</a>
                <a href="logout.php" class="btn btn-danger btn-sm px-3 fw-bold"><i class="fas fa-power-off me-1"></i> خروج</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 mt-4 mb-5">
        <?= $message ?>

        <!-- شريط الخدمات والإحصائيات -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card-cyber p-3 text-center border-start border-4 border-info">
                    <small class="text-muted fw-bold">إجمالي العمليات المرصودة</small>
                    <h2 class="text-info fw-bold mb-0"><?= $total_tasks ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-cyber p-3 text-center border-start border-4 border-warning">
                    <small class="text-muted fw-bold">عمليات نشطة (قيد المتابعة)</small>
                    <h2 class="text-warning fw-bold mb-0"><?= $pending_tasks ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-cyber p-3 text-center border-start border-4 border-success">
                    <small class="text-muted fw-bold">عمليات مؤمنة ومنجزة</small>
                    <h2 class="text-success fw-bold mb-0"><?= $completed_tasks ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-cyber p-2 text-center d-flex align-items-center justify-content-around border-start border-4 border-danger">
                    <div>
                        <small class="text-muted d-block fw-bold">الرادار الميداني</small>
                        <span class="badge bg-danger">متصل وبث مباشر</span>
                    </div>
                    <div class="radar-box"><div class="radar-sweep"></div></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-cyber p-4">
                    <h5 class="card-title mb-3 text-info fw-bold"><i class="fas fa-satellite-dish me-2"></i> إصدار أمر وعملية أمنية</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-warning">كود وعنوان العملية الميدانية</label>
                            <input type="text" name="title" class="form-control" required placeholder="مثال: رصد خلية #502">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-warning">مستوى السرية الاستراتيجية</label>
                            <select name="priority" class="form-select">
                                <option value="normal">عادي (Standard)</option>
                                <option value="urgent">عاجل (Urgent)</option>
                                <option value="top_secret">سري للغاية (Top Secret)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-warning">التقرير والملاحظات الاستخباراتية</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="تفاصيل الهدف الأمني..."></textarea>
                        </div>
                        <button type="submit" name="add_task" class="btn btn-info w-100 py-2 fw-bold text-dark shadow"><i class="fas fa-shield-virus me-1"></i> اعتماد وتشغيل العملية</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-cyber p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-info fw-bold mb-0"><i class="fas fa-database me-2"></i> سجل العمليات الجارية في المنظومة</h5>
                        <button onclick="window.print()" class="btn btn-outline-info btn-sm fw-bold"><i class="fas fa-print me-1"></i> طباعة السجل الرسمي</button>
                    </div>
                    
                    <?php if (count($tasks) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle border-secondary text-white">
                                <thead>
                                    <tr class="text-warning border-warning">
                                        <th>العملية / الهدف</th>
                                        <th>السرية</th>
                                        <th>التقرير الاستخباراتي</th>
                                        <th>الحالة</th>
                                        <th>التحكم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                        <tr>
                                            <td class="fw-bold <?= $task['status'] == 'completed' ? 'text-decoration-line-through text-muted' : 'text-info' ?>">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $p = $task['priority'] ?? 'normal';
                                                    if($p == 'top_secret') echo '<span class="badge badge-top-secret px-2 py-1">سري للغاية</span>';
                                                    elseif($p == 'urgent') echo '<span class="badge badge-urgent px-2 py-1">عاجل</span>';
                                                    else echo '<span class="badge badge-normal px-2 py-1">عادي</span>';
                                                ?>
                                            </td>
                                            <td class="text-light small"><?= htmlspecialchars($task['description']) ?></td>
                                            <td>
                                                <?php if ($task['status'] == 'completed'): ?>
                                                    <span class="badge bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> مؤمنة</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-spinner fa-spin me-1"></i> نشطة</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="index.php?toggle=<?= $task['id'] ?>" class="btn btn-sm <?= $task['status'] == 'completed' ? 'btn-secondary' : 'btn-success' ?>" title="تغيير الحالة">
                                                    <i class="fas fa-sync-alt"></i>
                                                </a>
                                                <a href="index.php?delete=<?= $task['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من إتلاف هذا الملف الأمني؟')" title="حذف">
                                                    <i class="fas fa-skull-crossbones"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-5">لا توجد عمليات نشطة حالياً في السجل الأمني.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- حقوق التصنيع للمطور المعتمد -->
    <footer>
        <div class="container">
            <p class="mb-1 fw-bold"><i class="fas fa-code text-info me-1"></i> تصميم وبرمجة وتطوير المنظومة الأمنية بواسطة المطور: <strong class="text-warning">طارق أحمد محمد الشويع</strong></p>
            <p class="mb-0 small text-light"><i class="fas fa-phone-alt me-1 text-success"></i> الدعم الفني: <strong class="text-info">771251114</strong> | جميع الحقوق محفوظة لغرفة العمليات المركزية © 2026</p>
        </div>
    </footer>

    <script>
        setInterval(() => {
            document.getElementById('security-clock').innerText = new Date().toLocaleTimeString('ar-SA');
        }, 1000);

        const canvas = document.getElementById('cyber-canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        const chars = '0123456789ABCDEFSECURITY';
        const fontSize = 14;
        const columns = canvas.width / fontSize;
        const drops = Array(Math.floor(columns)).fill(1);
        setInterval(() => {
            ctx.fillStyle = 'rgba(3, 7, 18, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#00f0ff';
            ctx.font = fontSize + 'px monospace';
            for (let i = 0; i < drops.length; i++) {
                ctx.fillText(chars.charAt(Math.floor(Math.random() * chars.length)), i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
        }, 33);
    </script>
</body>
</html>

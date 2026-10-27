<?php
require_once 'db.php';
session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['military_id'] = $user['military_id'];
            header("Location: index.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger text-center fw-bold py-2'>خطأ: بيانات الاعتماد أو البريد غير مطابقة للسجلات!</div>";
        }
    } else {
        $message = "<div class='alert alert-warning text-center fw-bold py-2'>الرجاء إدخال بيانات الدخول كاملة!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - جهاز الأمن والمخابرات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: radial-gradient(circle at center, #14050b 0%, #030204 100%); 
            color: #00f0ff; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
        }
        .card-cyber { 
            background: rgba(25, 10, 18, 0.95); 
            border: 2px solid #ff416c; 
            border-radius: 16px; 
            box-shadow: 0 0 35px rgba(255, 65, 108, 0.3); 
            color: #fff;
            backdrop-filter: blur(10px);
        }
        .form-label {
            color: #ffaa00 !important;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .form-control { 
            background: #0d0408; 
            border: 1px solid #52192b; 
            color: #ff416c; 
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-control:focus { 
            background: #0d0408; 
            color: #fff; 
            border-color: #ffaa00; 
            box-shadow: 0 0 15px rgba(255,170,0,0.5); 
        }
        .btn-custom-danger {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: #fff;
            font-weight: bold;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-custom-danger:hover {
            background: linear-gradient(135deg, #ffaa00, #ff5500);
            color: #fff;
            box-shadow: 0 0 20px rgba(255,170,0,0.6);
        }
        .link-register {
            color: #00ffcc !important;
            font-weight: 600;
            text-shadow: 0 0 5px rgba(0,255,204,0.3);
        }
        .link-register:hover {
            color: #ffaa00 !important;
            text-decoration: underline !important;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card-cyber p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-secret fa-3x text-danger mb-3"></i>
                        <h3 class="fw-bold text-white">جهاز الأمن والمخابرات</h3>
                        <p class="text-warning small mb-0">نظام الدخول المشفر لغرفة العمليات المركزية</p>
                    </div>
                    <?= $message ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2"><i class="fas fa-envelope me-1"></i> البريد الإلكتروني الأمني</label>
                            <input type="email" name="email" class="form-control" required placeholder="name@security.gov">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2"><i class="fas fa-lock me-1"></i> الرمز وسرية المرور</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-custom-danger w-100 py-3 mb-3 shadow">مصادقة ودخول آمن للنظام</button>
                    </form>
                    <div class="text-center mt-3 pt-2 border-top border-secondary">
                        <a href="register.php" class="text-decoration-none link-register"><i class="fas fa-user-plus me-1"></i> تسجيل اعتماد عنصر جديد (إنشاء حساب)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

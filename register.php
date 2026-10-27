<?php
require_once 'db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $military_id = trim($_POST['military_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($military_id) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, military_id, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $military_id, $email, $hashed_password]);
            $message = "<div class='alert alert-success text-center fw-bold py-2'>تم اعتماد الضابط والرقم العسكري بنجاح في السجلات الأمنية.</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-danger text-center fw-bold py-2'>خطأ أمني: الرقم العسكري أو البريد الإلكتروني مسجل مسبقاً!</div>";
            } else {
                $message = "<div class='alert alert-danger text-center fw-bold py-2'>حدث خطأ في النظام.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-warning text-center fw-bold py-2'>الرجاء ملء جميع الحقول واعتماد البيانات بدقة!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بوابة الاعتماد الأمني - جهاز الأمن والمخابرات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: radial-gradient(circle at center, #0a192f 0%, #02060b 100%); 
            color: #00f0ff; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
        }
        .card-cyber { 
            background: rgba(10, 25, 47, 0.95); 
            border: 2px solid #00f0ff; 
            border-radius: 16px; 
            box-shadow: 0 0 35px rgba(0, 240, 255, 0.3); 
            color: #fff;
            backdrop-filter: blur(10px);
        }
        .form-label {
            color: #ffaa00 !important;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .form-control { 
            background: #040d1a; 
            border: 1px solid #1f4068; 
            color: #00f0ff; 
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-control:focus { 
            background: #040d1a; 
            color: #00f0ff; 
            border-color: #ffaa00; 
            box-shadow: 0 0 15px rgba(255,170,0,0.5); 
        }
        .btn-custom-cyber {
            background: linear-gradient(135deg, #00f0ff, #0077ff);
            color: #000;
            font-weight: bold;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-custom-cyber:hover {
            background: linear-gradient(135deg, #ffaa00, #ff5500);
            color: #fff;
            box-shadow: 0 0 20px rgba(255,170,0,0.6);
        }
        .link-login {
            color: #00ffcc !important;
            font-weight: 600;
            text-shadow: 0 0 5px rgba(0,255,204,0.3);
        }
        .link-login:hover {
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
                        <i class="fas fa-shield-alt fa-3x text-info mb-3 animate-pulse"></i>
                        <h3 class="fw-bold text-white">جهاز الأمن والمخابرات</h3>
                        <p class="text-warning small mb-0">بوابة الاعتماد الأمني للضباط والعناصر</p>
                    </div>
                    <?= $message ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2"><i class="fas fa-user-shield me-1"></i> اسم الضابط / العنصر</label>
                            <input type="text" name="username" class="form-control" required placeholder="أدخل الاسم الكامل...">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2"><i class="fas id-badge me-1"></i> الرقم العسكري</label>
                            <input type="text" name="military_id" class="form-control" required placeholder="مثال: SEC-9042">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2"><i class="fas fa-envelope me-1"></i> البريد الإلكتروني المعتمد</label>
                            <input type="email" name="email" class="form-control" required placeholder="name@security.gov">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2"><i class="fas fa-key me-1"></i> كلمة المرور المشفرة</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-custom-cyber w-100 py-3 mb-3 shadow">اعتماد وتثبيت البيانات الأمنية</button>
                    </form>
                    <div class="text-center mt-3 pt-2 border-top border-secondary">
                        <a href="login.php" class="text-decoration-none link-login"><i class="fas fa-sign-in-alt me-1"></i> لديك اعتماد مسبق؟ سجل الدخول للنظام</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Başlıkları JSON yanıtı için ayarla
header('Content-Type: application/json');

// Yanıt dizisini hazırla
$response = ['success' => false, 'message' => 'Bilinmeyen bir hata oluştu.'];

// --- VERİTABANI BAĞLANTI BİLGİLERİNİZİ DOLDURUN ---
$host = 'localhost'; 
$db   = 'b'; // Veritabanı adınızı girin
$user = 'root';  // MySQL kullanıcı adınızı girin
$pass = '';       // MySQL şifrenizi girin
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
// ----------------------------------------------------

// Bağış miktarını al ve temizle
if (isset($_POST['donation_amount'])) {
    $amount = filter_var($_POST['donation_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    // Geçerli bir sayı olup olmadığını tekrar kontrol et
    if (!is_numeric($amount) || $amount <= 0) {
        $response['message'] = "Geçersiz bağış miktarı gönderildi.";
        echo json_encode($response);
        exit;
    }

    try {
        // 1. Veritabanına bağlan
        $pdo = new PDO($dsn, $user, $pass, $options);

        // 2. SQL Sorgusunu Hazırla (GÜVENLİK İÇİN ZORUNLU ADIM)
        // Tablonuzun 'resources' olduğunu varsayıyoruz ve eklemek istediğiniz sütunun 'amount' olduğunu varsayıyoruz.
        // Başka sütunlarınız varsa, burayı ona göre düzenlemelisiniz.
        // Örnek: INSERT INTO resources (amount, date_added, type) VALUES (?, NOW(), 'donation')
        $sql = "INSERT INTO resources (amount) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        
        // 3. Sorguyu Çalıştır ve Değeri Güvenli Bir Şekilde Gönder
        $stmt->execute([$amount]);

        // İşlem başarılı
        $response['success'] = true;
        $response['message'] = "Veri başarıyla resources tablosuna eklendi.";

    } catch (\PDOException $e) {
        // Hata durumunda mesajı kaydet
        $response['message'] = "Veritabanı hatası: " . $e->getMessage();
        // Geliştirme aşamasında hatayı görebilirsiniz, canlı ortamda bu detaylı hatayı göstermeyin.
        error_log("DB Hatası: " . $e->getMessage()); 
    }

} else {
    $response['message'] = "Bağış miktarı bilgisi alınamadı.";
}

// Sonuçları JSON formatında geri döndür
echo json_encode($response);
?>
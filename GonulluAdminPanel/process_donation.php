<?php
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Bilinmeyen bir hata oluştu.', 'category_assigned' => '', 'city_assigned' => ''];

// --- VERİTABANI BAĞLANTI BİLGİLERİNİZİ DOLDURUN ---
$host = 'localhost'; 
$db   = 'b'; 
$user = 'root'; 
$pass = '';       
$charset = 'utf8mb4';
// ----------------------------------------------------

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- KATEGORİ SORGULAMA FONKSİYONU (AYNI KALDI) ---
function determineCategory($itemName) {
    // ... (Önceki belirleme fonksiyonu buraya gelecek) ... 
    // Kendi keyword'lerinizi ve kategorilerinizi buraya kopyalayın
    $item = mb_strtolower(trim($itemName), 'UTF-8');
    $keywords = [
        'yiyecek' => ['ekmek', 'su', 'yemek', 'gıda', 'konserve', 'erzak', 'makarna', 'pirinç', 'kahvaltılık'],
        'barınma' => ['battaniye', 'çadır', 'uyku tulumu', 'ısıtıcı', 'soba', 'branda'],
        'sağlık'  => ['ilaç', 'bandaj', 'sargı', 'maske', 'dezenfektan', 'tıbbi', 'ecza', 'ağrı kesici'],
        'giyim'   => ['kıyafet', 'mont', 'ayakkabı', 'çocuk giyim', 'kazak'],
        'genel'   => ['genel', 'temizlik', 'malzeme', 'oyuncak'] 
    ];

    foreach ($keywords as $category => $list) {
        foreach ($list as $key) {
            if (strpos($item, $key) !== false) {
                return $category;
            }
        }
    }
    return 'genel';
}
// ----------------------------------------

if (isset($_POST['quantity']) && isset($_POST['item_name']) && isset($_POST['city_name'])) {
    
    // 1. Verileri al ve temizle
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    $itemName = $_POST['item_name']; 
    $cityName = trim($_POST['city_name']); // Yeni: Şehir bilgisini al
    
    // 2. Kategoriyi belirle
    $category = determineCategory($itemName);

    // 3. Doğrulama
    if ($quantity === false || $quantity <= 0 || empty($cityName)) {
        $response['message'] = "Geçersiz değerler (miktar veya şehir) gönderildi.";
        echo json_encode($response);
        exit;
    }

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        // resource_id üretimi (UYARI: Test amaçlı, gerçek projede farklı bir yöntem kullanın)
        $resourceId = 'RS' . rand(100, 999); 
        $provider = "Anonim Bağışçı"; 
        $available = 1; 
        
        // SQL Sorgusu: Yeni şehir (city) sütununu ekledik.
        $sql = "INSERT INTO resources (resource_id, provider, category, quantity, city, available) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // Sorguyu Çalıştır ve Değerleri Sırayla Güvenli Bir Şekilde Gönder
        $stmt->execute([
            $resourceId,
            $provider,
            $category,
            $quantity,
            $cityName, // Şehir değişkeni buraya eklendi
            $available
        ]);

        $response['success'] = true;
        $response['message'] = "Kaynak başarıyla eklendi.";
        $response['category_assigned'] = $category;
        $response['city_assigned'] = $cityName; // Atanan şehri geri döndür
        

    } catch (\PDOException $e) {
        $response['message'] = "Veritabanı hatası: " . $e->getMessage();
        error_log("DB Hatası: " . $e->getMessage()); 
    }

} else {
    $response['message'] = "Gerekli tüm veriler (miktar, malzeme adı ve şehir) alınamadı.";
}

echo json_encode($response);
?>
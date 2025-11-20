<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$dbname = 'b';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "data" => []]);
    exit;
}

$sql = "
    SELECT 
        u.city AS sehir,
        m.category AS kategori,
        m.status AS statu,
        u.status AS kullanici_durumu
    FROM messages m
    JOIN users u ON m.user_id = u.user_id
";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cityData = [];

// Şehirlere göre grupla
foreach ($rows as $row) {

    $sehir = mb_convert_case($row['sehir'], MB_CASE_TITLE, "UTF-8");
    $kategori = strtolower($row['kategori']); // JS ile eşleşmesi için küçük harf
    $statu = $row['statu'];
    $durum = $row['kullanici_durumu'];

    if (!isset($cityData[$sehir])) {
        $cityData[$sehir] = [
            "details" => [
                "su" => 0,
                "yiyecek" => 0,
                "barınma" => 0,
                "sağlık" => 0
            ],
            "statu" => $statu,
            "durum" => $durum,
            "toplam" => 0
        ];
    }

    // ilgili kategori sayaç artır
    if (isset($cityData[$sehir]["details"][$kategori])) {
        $cityData[$sehir]["details"][$kategori]++;
    }

    $cityData[$sehir]["toplam"]++;
}

echo json_encode([
    "success" => true,
    "data" => $cityData
], JSON_UNESCAPED_UNICODE);
?>

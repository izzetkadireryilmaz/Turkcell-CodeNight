async function svgturkiyeharitasi() {
  const element = document.querySelector('#svg-turkiye-haritasi');
  const info = document.querySelector('.il-isimleri');
  
  // 1. Veriyi Çek
  let cityData = {};
  try {
    const response = await fetch('get_map_data.php'); // Yukarıda oluşturduğumuz dosya
    const result = await response.json();
    if (result.success) {
      cityData = result.data;
      renderMapColors(cityData); // Haritayı boya
    }
  } catch (error) {
    console.error('Veri çekilemedi:', error);
  }

  // Haritayı kategorilere göre renklendirme fonksiyonu
  function renderMapColors(data) {
    const paths = element.querySelectorAll('path');
    
    paths.forEach(path => {
      // SVG'deki il adını al (Büyük harfe çevir eşleşme için)
      const ilAdi = path.getAttribute('data-iladi').toUpperCase();
      
      if (data[ilAdi]) {
        // Bu ilde veri var, en çok hangi kategori istenmiş bulalım
        const details = data[ilAdi].details;
        const dominantCategory = Object.keys(details).reduce((a, b) => details[a] > details[b] ? a : b);
        
        // Kategoriye göre renk ataması
        let color = '#ccc';
        switch(dominantCategory) {
            case 'su': color = '#3498db'; break; // Mavi
            case 'yiyecek': color = '#2ecc71'; break; // Yeşil
            case 'barınma': color = '#e67e22'; break; // Turuncu
            case 'sağlık': color = '#e74c3c'; break; // Kırmızı
            default: color = '#95a5a6'; // Gri
        }
        
        // Harita parçasını boya
        path.style.fill = color;
        // Kullanıcının orada talep olduğunu anlaması için opaklığı ayarla veya class ekle
        path.setAttribute('data-has-request', 'true');
      }
    });
  }

  // 2. Mouse Over Olayı (Güncellendi)
  element.addEventListener('mouseover', function (event) {
    if (event.target.tagName === 'path') {
      const parent = event.target.parentNode;
      const ilAdiRaw = parent.getAttribute('data-iladi');
      const ilAdiKey = ilAdiRaw.toUpperCase();
      
      let content = `<div><strong>${ilAdiRaw}</strong></div>`;
      
      // Eğer bu ilde veri varsa detayları ekle
      if (cityData[ilAdiKey]) {
        const details = cityData[ilAdiKey].details;
        content += '<ul style="font-size:12px; padding-left:15px; margin:5px 0;">';
        for (const [cat, count] of Object.entries(details)) {
            // İlk harfi büyüt
            const catName = cat.charAt(0).toUpperCase() + cat.slice(1);
            content += `<li>${catName}: ${count}</li>`;
        }
        content += '</ul>';
      } else {
        content += '<div style="font-size:11px; color:#888;">Kayıtlı talep yok</div>';
      }

      info.innerHTML = content;
      info.style.display = 'block'; // Görünür yap
    }
  });

  element.addEventListener('mousemove', function (event) {
    info.style.top = event.pageY + 25 + 'px';
    info.style.left = event.pageX + 'px';
  });

  element.addEventListener('mouseout', function (event) {
    info.innerHTML = '';
    info.style.display = 'none';
  });

  element.addEventListener('click', function (event) {
    if (event.target.tagName === 'path') {
      const parent = event.target.parentNode;
      const id = parent.getAttribute('id');
      window.location.href = '#' + id + '-' + parent.getAttribute('data-plakakodu');
    }
  });
}

// Fonksiyonu başlat
svgturkiyeharitasi();
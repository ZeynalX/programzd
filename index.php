<?php
// --- 1. ADIM: SUNUCU TARAFINDA VERİLERİ BİRLEŞTİRME ---

// Gerekli JSON dosyalarını oku
$timetable_json = file_get_contents('ders-programi.json');
$curriculum_json = file_get_contents('kazanimlar.json');
$weeks_json = file_get_contents('haftalar.json');

// JSON verilerini PHP dizilerine dönüştür (true parametresi ile associative array olur)
$timetable = json_decode($timetable_json, true);
$curriculum = json_decode($curriculum_json, true);
$weeksData = json_decode($weeks_json, true);

// Hata kontrolü: Eğer dosyalardan biri okunamazsa veya JSON formatı bozuksa hata ver.
if (!$timetable || !$curriculum || !$weeksData) {
    die("Hata: Gerekli JSON dosyalarından biri (ders-programi, kazanimlar, haftalar) okunamadı veya formatı bozuk.");
}

// Ders sayaçlarını başlat
$lessonCounters = [];
foreach ($curriculum as $subject => $objectives) {
    $lessonCounters[$subject] = 0;
}

// 38 haftalık tam ders programını oluşturacak ana dizi
$finalSchedule = [];

// Haftaları ve dersleri birleştirerek 'finalSchedule' dizisini oluştur
foreach ($weeksData as $index => $week) {
    $weekKey = 'week' . ($index + 1);
    $finalSchedule[$weekKey] = [
        'name' => $week['name'],
        'dates' => $week['dates'],
        'days' => []
    ];

    foreach ($timetable as $day => $lessons) {
        $finalSchedule[$weekKey]['days'][$day] = [];
        foreach ($lessons as $lessonIndex => $lessonName) {
            $objective = "Kazanım veya etkinlik belirtilmemiş.";

            if (isset($curriculum[$lessonName]) && isset($lessonCounters[$lessonName]) && $lessonCounters[$lessonName] < count($curriculum[$lessonName])) {
                $objective = $curriculum[$lessonName][$lessonCounters[$lessonName]];
                $lessonCounters[$lessonName]++;
            }

            $finalSchedule[$weekKey]['days'][$day][] = [
                'time' => ($lessonIndex + 1) . '. Ders',
                'name' => $lessonName,
                'objective' => $objective
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haftalık Ders Programı - Papirüs Teması</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Crimson+Pro:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        :root { --papyrus-bg-color-light: #fdf5e6; --papyrus-bg-color-dark: #eee0c8; --text-primary-color: #3e2723; --text-secondary-color: #6d4c41; --border-color: #a1887f; --accent-color: #795548; --shadow-color: rgba(0, 0, 0, 0.1); --active-bg-color: #d7ccc8; } * { box-sizing: border-box; margin: 0; padding: 0; } body { font-family: 'Crimson Pro', serif; background-color: var(--papyrus-bg-color-light); color: var(--text-primary-color); background-image: url('https://www.transparenttextures.com/patterns/paper-fibers.png'); background-attachment: fixed; padding-bottom: 80px; } .sidenav { height: 100%; width: 0; position: fixed; z-index: 1001; top: 0; left: 0; overflow-x: hidden; transition: 0.4s ease-out; padding-top: 60px; background-color: var(--papyrus-bg-color-dark); border-right: 2px solid var(--border-color); box-shadow: 2px 0 10px var(--shadow-color); } .sidenav a { padding: 12px 20px; text-decoration: none; font-size: 1.1rem; color: var(--text-secondary-color); display: block; transition: 0.3s; border-bottom: 1px dashed rgba(0,0,0,0.1); } .sidenav a:last-child { border-bottom: none; } .sidenav a:hover { color: var(--text-primary-color); background-color: var(--active-bg-color); } .sidenav a.active { color: var(--accent-color); font-weight: 700; background-color: var(--active-bg-color); } .sidenav .closebtn { position: absolute; top: 10px; right: 25px; font-size: 36px; color: var(--text-primary-color); } header { padding: 2px 10px; position: sticky; top: 4px; z-index: 1000; margin: 4px; background-color: var(--papyrus-bg-color-dark); border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px var(--shadow-color); text-align: center; } .header-top { display: flex; align-items: center; justify-content: space-between; } .menu-icon, .nav-button { font-size: 24px; cursor: pointer; background: none; border: none; color: var(--text-primary-color); padding: 2px; border-radius: 50%; transition: background 0.3s; } .menu-icon:hover, .nav-button:hover:not(:disabled) { background: rgba(0,0,0,0.05); } .nav-button:disabled { color: rgba(0,0,0,0.2); cursor: not-allowed; } .week-navigation { display: flex; align-items: center; gap: 10px; flex-grow: 1; justify-content: center; } .user-name { font-family: 'Cinzel Decorative', cursive; font-size: 0.8rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; background-color: rgba(0,0,0,0.05); color: var(--accent-color); border: 1px dashed var(--border-color); } header h1 { font-family: 'Cinzel Decorative', cursive; font-size: 1.0rem; font-weight: 700; color: var(--accent-color); text-shadow: none; margin: 0; text-transform: uppercase; } header h2 { font-family: 'Crimson Pro', serif; font-size: 0.7rem; font-weight: 400; opacity: 0.8; margin-top: 2px; color: var(--text-secondary-color); } .day-nav { display: flex; justify-content: space-around; padding: 4px; position: sticky; top: 52px; z-index: 999; margin: 4px; gap: 4px; background-color: var(--papyrus-bg-color-dark); border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px var(--shadow-color); } .day-nav button { font-family: 'Crimson Pro', serif; padding: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; border: none; border-radius: 6px; background-color: transparent; color: var(--text-secondary-color); transition: all 0.3s ease; flex-grow: 1; } .day-nav button.active { background-color: var(--accent-color); color: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.15); } .main-container { padding: 4px; } .schedule-card { margin-bottom: 6px; background-color: var(--papyrus-bg-color-dark); border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px var(--shadow-color); transition: transform 0.2s ease-out, box-shadow 0.2s ease-out; border-left: 4px solid var(--accent-color); } .schedule-card:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.12); } .card-header { padding: 6px 10px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(0,0,0,0.03); } .lesson-name { font-family: 'Cinzel Decorative', cursive; font-weight: 700; font-size: 0.9rem; color: var(--text-primary-color); text-transform: capitalize; } .lesson-time { font-size: 0.75rem; padding: 3px 7px; border-radius: 6px; background-color: var(--accent-color); color: #fff; font-weight: 500; } .card-body { padding: 10px; font-size: 1rem; line-height: 1.5; color: var(--text-primary-color); } .card-body.empty { font-style: italic; color: var(--text-secondary-color); opacity: 0.7; } .action-buttons-container { position: fixed; bottom: 20px; right: 20px; z-index: 100; display: flex; gap: 15px; } .action-button { width: 60px; height: 60px; background-color: var(--accent-color); color: white; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); cursor: pointer; transition: all 0.3s ease; } .action-button:hover { transform: scale(1.1); box-shadow: 0 6px 16px rgba(0,0,0,0.3); } #week-view-container { display: flex; gap: 8px; align-items: flex-start; } .week-view-day-column { flex: 1; min-width: 0; } .week-view-day-title { font-family: 'Cinzel Decorative', cursive; text-align: center; font-size: 1.2rem; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 2px solid var(--border-color); color: var(--accent-color); } @page { size: A4 landscape; margin: 10mm; } @media print { body { padding-bottom: 0; font-size: 9pt; background-image: none; } header, .day-nav, .sidenav, .action-buttons-container, #week-view-container, #day-view-container { display: none !important; } #print-container { display: block !important; } .print-page-header { text-align: left; margin-bottom: 8px; } .print-page-header h1 { font-family: 'Cinzel Decorative', cursive; font-size: 11pt; font-weight: bold; margin: 0; color: #000; } .print-page-header p { font-family: 'Crimson Pro', serif; font-size: 9pt; margin: 0; color: #333; } .print-days-flex-container { display: flex !important; flex-direction: row; gap: 8px; align-items: flex-start; } .print-day-container { flex: 1; min-width: 0; } .print-day-title { font-family: 'Cinzel Decorative', cursive; font-size: 10pt; border-bottom: 1px solid #999; padding-bottom: 2px; margin-bottom: 6px; color: #000; text-align: center; } .print-card { border: 1px solid #ccc; margin-bottom: 5px; padding: 5px; page-break-inside: avoid; } .print-card-header { display: flex; justify-content: space-between; font-size: 8pt; font-family: 'Crimson Pro', serif; margin-bottom: 4px; } .print-lesson-name { font-weight: 700; } .print-card-body { font-size: 8pt; line-height: 1.3; } .print-card-body.empty { color: #888; } } #print-container { display: none; }
    </style>
</head>
<body>
    <div id="mySidenav" class="sidenav"><a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a><div id="week-menu"></div></div>
    <header>
        <div class="header-top">
            <span class="menu-icon material-icons" onclick="openNav()">menu</span>
            <div class="week-navigation">
                <button id="prev-week-btn" class="nav-button material-icons" onclick="showPreviousWeek()">chevron_left</button>
                <h1 id="week-title-display">Yükleniyor...</h1>
                <button id="next-week-btn" class="nav-button material-icons" onclick="showNextWeek()">chevron_right</button>
            </div>
            <span class="user-name">4-D Zeynal Öğrt</span>
        </div>
        <h2 id="date-display"></h2>
    </header>
    <nav id="day-nav" class="day-nav"></nav>
    <main id="day-view-container" class="main-container"></main>
    <main id="week-view-container" class="main-container" style="display: none;"></main>
    <div class="action-buttons-container">
        <button id="toggle-view-btn" class="action-button" onclick="toggleView()" title="Hafta Görünümü"><i class="material-icons">view_week</i></button>
        <button id="print-btn" class="action-button" onclick="printSchedule()" title="Haftalık Programı Yazdır"><i class="material-icons">print</i></button>
    </div>
    <div id="print-container"></div>

    <script>
        // PHP tarafından sunucuda oluşturulan tam ders programı verisi buraya yazılacak.
        const finalSchedule = <?php echo json_encode($finalSchedule, JSON_UNESCAPED_UNICODE); ?>;
        const weeksData = <?php echo json_encode($weeksData, JSON_UNESCAPED_UNICODE); ?>;
        
        // --- GLOBAL DEĞİŞKENLER ---
        let currentWeekIndex = 0;
        let currentDayId = 'pazartesi';
        let isWeekViewActive = false;
        const dayOrder = { pazartesi: "Pazartesi", sali: "Salı", carsamba: "Çarşamba", persembe: "Perşembe", cuma: "Cuma" };

        // --- HTML ELEMENTLERİ ---
        const weekMenu = document.getElementById('week-menu'),
            dayNav = document.getElementById('day-nav'),
            dayViewContainer = document.getElementById('day-view-container'),
            weekViewContainer = document.getElementById('week-view-container'),
            weekTitleDisplay = document.getElementById('week-title-display'),
            dateDisplay = document.getElementById('date-display'),
            prevWeekBtn = document.getElementById('prev-week-btn'),
            nextWeekBtn = document.getElementById('next-week-btn');

        // --- NAVİGASYON FONKSİYONLARI ---
        function openNav() { document.getElementById("mySidenav").style.width = "280px"; }
        function closeNav() { document.getElementById("mySidenav").style.width = "0"; }

        // --- ANA GÖRÜNTÜLEME FONKSİYONLARI ---

        // Yan menüdeki hafta listesini oluşturur
        function populateSideNavMenu() {
            weekMenu.innerHTML = '';
            weeksData.forEach((week, index) => {
                const link = document.createElement('a');
                link.href = "javascript:void(0)";
                link.textContent = week.name;
                link.dataset.weekIndex = index;
                link.onclick = () => { displayWeek(index); closeNav(); };
                weekMenu.appendChild(link);
            });
        }
        
        // Üstteki gün navigasyonunu oluşturur
        function populateDayNav() {
            dayNav.innerHTML = '';
            const dayNames = { pazartesi: "Pzt", sali: "Sal", carsamba: "Çar", persembe: "Per", cuma: "Cum" };
            for(const dayId in dayOrder) {
                 const button = document.createElement('button');
                 button.textContent = dayNames[dayId]; 
                 button.dataset.day = dayId;
                 button.onclick = () => displayDay(dayId); 
                 dayNav.appendChild(button);
            }
        }

        // Belirli bir haftayı görüntüler
        function displayWeek(weekIndex) {
            currentWeekIndex = weekIndex;
            const weekKey = `week${weekIndex + 1}`;
            const weekData = finalSchedule[weekKey];
            
            if (!weekData) return; // Hata kontrolü

            weekTitleDisplay.textContent = weekData.name;
            dateDisplay.textContent = weekData.dates;
            document.querySelectorAll('#week-menu a').forEach(a => a.classList.toggle('active', parseInt(a.dataset.weekIndex) === weekIndex));
            
            prevWeekBtn.disabled = weekIndex === 0;
            nextWeekBtn.disabled = weekIndex === (weeksData.length - 1);

            if (isWeekViewActive) {
                renderWeekView();
            } else {
                displayDay(currentDayId);
            }
        }

        // Haftalık görünümde belirli bir günü render eder
        function displayDay(dayId) {
            currentDayId = dayId;
            dayViewContainer.innerHTML = '';
            const weekKey = `week${currentWeekIndex + 1}`;
            const lessons = finalSchedule[weekKey]?.days[dayId] || [];

            lessons.forEach(lesson => {
                const card = document.createElement('div');
                card.className = 'schedule-card';
                const bodyClass = lesson.objective.includes("belirtilmemiş") ? 'card-body empty' : 'card-body';
                card.innerHTML = `<div class="card-header"><span class="lesson-name">${lesson.name}</span><span class="lesson-time">${lesson.time}</span></div><div class="${bodyClass}">${lesson.objective}</div>`;
                dayViewContainer.appendChild(card);
            });
            document.querySelectorAll('#day-nav button').forEach(btn => btn.classList.toggle('active', btn.dataset.day === dayId));
        }

        // Tüm haftayı sütunlar halinde render eder
        function renderWeekView() {
            weekViewContainer.innerHTML = '';
            const weekKey = `week${currentWeekIndex + 1}`;
            const weekData = finalSchedule[weekKey];

            for (const dayId in dayOrder) {
                const column = document.createElement('div');
                column.className = 'week-view-day-column';
                let lessonsHTML = `<h3 class="week-view-day-title">${dayOrder[dayId]}</h3>`;
                
                const lessons = weekData.days[dayId] || [];
                lessons.forEach(lesson => {
                    const bodyClass = lesson.objective.includes("belirtilmemiş") ? 'card-body empty' : 'card-body';
                    lessonsHTML += `<div class="schedule-card"><div class="card-header"><span class="lesson-name">${lesson.name}</span><span class="lesson-time">${lesson.time}</span></div><div class="${bodyClass}">${lesson.objective}</div></div>`;
                });
                column.innerHTML = lessonsHTML;
                weekViewContainer.appendChild(column);
            }
        }
        
        // --- YARDIMCI FONKSİYONLAR ---
        function showPreviousWeek() { if (currentWeekIndex > 0) displayWeek(currentWeekIndex - 1); }
        function showNextWeek() { if (currentWeekIndex < weeksData.length - 1) displayWeek(currentWeekIndex + 1); }

        function toggleView() {
            isWeekViewActive = !isWeekViewActive;
            const toggleBtn = document.getElementById('toggle-view-btn');
            const toggleBtnIcon = toggleBtn.querySelector('i');

            if (isWeekViewActive) {
                renderWeekView();
                dayNav.style.display = 'none';
                dayViewContainer.style.display = 'none';
                weekViewContainer.style.display = 'flex';
                toggleBtn.title = 'Gün Görünümü';
                toggleBtnIcon.textContent = 'view_day';
            } else {
                dayNav.style.display = 'flex';
                dayViewContainer.style.display = 'block';
                weekViewContainer.style.display = 'none';
                toggleBtn.title = 'Hafta Görünümü';
                toggleBtnIcon.textContent = 'view_week';
                displayDay(currentDayId);
            }
        }
        
        function printSchedule() {
            const printContainer = document.getElementById('print-container');
            const weekKey = `week${currentWeekIndex + 1}`;
            const week = finalSchedule[weekKey];
            let pageHeaderHTML = `<div class="print-page-header"><h1>${week.name}</h1><p>${week.dates}</p></div>`;
            let daysHTML = '<div class="print-days-flex-container">';

            for (const dayId in dayOrder) {
                daysHTML += `<div class="print-day-container"><h3 class="print-day-title">${dayOrder[dayId]}</h3>`;
                const lessons = week.days[dayId] || [];
                lessons.forEach(lesson => {
                    const bodyClass = lesson.objective.includes("belirtilmemiş") ? 'print-card-body empty' : 'print-card-body';
                    daysHTML += `<div class="print-card"><div class="print-card-header"><span class="print-lesson-name">${lesson.name}</span><span>${lesson.time}</span></div><div class="${bodyClass}">${lesson.objective}</div></div>`;
                });
                daysHTML += `</div>`;
            }
            daysHTML += '</div>';
            printContainer.innerHTML = pageHeaderHTML + daysHTML;
            window.print();
        }

        function parseTurkishDate(dateString) { const months = { 'ocak': 0, 'şubat': 1, 'mart': 2, 'nisan': 3, 'mayıs': 4, 'haziran': 5, 'temmuz': 6, 'ağustos': 7, 'eylül': 8, 'ekim': 9, 'kasım': 10, 'aralık': 11 }; const parts = dateString.toLowerCase().split(' '); if (parts.length < 2) return null; const day = parseInt(parts[0], 10); const month = months[parts[1]]; const year = parts.length > 2 ? parseInt(parts[2], 10) : new Date().getFullYear(); if (isNaN(day) || month === undefined || isNaN(year)) return null; return new Date(year, month, day); };
        
        function findCurrentWeekAndDay(weeks) {
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const dayMap = ['pazar', 'pazartesi', 'sali', 'carsamba', 'persembe', 'cuma', 'cumartesi'];
            let todayDayId = dayMap[today.getDay()];
            if (todayDayId === 'pazar' || todayDayId === 'cumartesi') { todayDayId = 'pazartesi'; }

            for(let i = 0; i < weeks.length; i++) {
                const week = weeks[i];
                const dateParts = week.dates.split(' - ');
                if (dateParts.length !== 2) continue;
                
                const endPart = dateParts[1].split(' ');
                const year = endPart[endPart.length - 1];
                let startPart = dateParts[0].split(' ');
                if (startPart.length === 2) { startPart.push(year); }

                const startDate = parseTurkishDate(startPart.join(' '));
                const endDate = parseTurkishDate(dateParts[1]);

                if (startDate && endDate && today >= startDate && today <= endDate) {
                    return { weekIndex: i, dayId: todayDayId };
                }
            }
            return { weekIndex: 0, dayId: 'pazartesi' };
        }

        // --- SAYFA YÜKLENDİĞİNDE BAŞLAT ---
        document.addEventListener('DOMContentLoaded', () => {
            // Veri zaten PHP tarafından yüklendiği için burada fetch yapmaya gerek yok.
            // Sadece arayüzü oluşturan fonksiyonları çağırıyoruz.
            populateSideNavMenu();
            populateDayNav();

            const target = findCurrentWeekAndDay(weeksData);
            currentDayId = target.dayId;
            displayWeek(target.weekIndex);
        });
    </script>
</body>
</html>

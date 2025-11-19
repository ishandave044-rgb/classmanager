<?php
// dashboard.php  — include or use at top of your protected pages
session_start();
if (!isset($_SESSION['teacher_name'])) {
    header("Location: login.php");
    exit();
}
$teacher = $_SESSION['teacher_name'];
// compute initials
$parts = preg_split('/\s+/', trim($teacher));
$initials = '';
foreach ($parts as $p) { $initials .= strtoupper($p[0] ?? ''); }
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Class Manager Pro</title>

<!-- Internal CSS for dashboard (won't touch other pages if this file is included only where needed) -->
<style>
:root{
  --bg:#f4f6fb; --card:#ffffff; --muted:#6b7280; --accent:#0066ff; --accent-2:#5a2dff;
  --text:#111827; --success:#28a745;
}
[data-theme="dark"]{
  --bg:#0f1724; --card:#0b1220; --muted:#9ca3af; --accent:#4c8bff; --accent-2:#8b5cf6; --text:#e6eef8;
}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,Segoe UI,Arial,Helvetica,sans-serif;background:var(--bg);color:var(--text);}

/* Topbar */
.topbar {
  display:flex;align-items:center;justify-content:space-between;
  gap:12px;padding:12px 18px;background:var(--card);box-shadow:0 4px 16px rgba(15,23,42,0.06);
  position:sticky;top:0;z-index:50;border-bottom:1px solid rgba(0,0,0,0.04);
}
.topbar .left { font-weight:700;font-size:18px;color:var(--accent-2) }
.topbar .center { font-size:15px;color:var(--muted) }
.topbar .right { display:flex;gap:12px;align-items:center }

/* profile circle */
.profile-circle {
  width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,var(--accent),var(--accent-2));color:#fff;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.12);
}

/* bell */
.icon-btn {
  background:transparent;border:0;padding:8px;border-radius:8px;cursor:pointer;color:var(--muted);
}
.icon-btn:hover{background:rgba(0,0,0,0.03)}

/* layout */
.wrapper { display:flex; min-height:calc(100vh - 64px); }

/* sidebar */
.sidebar {
  width:250px;min-width:250px;background:var(--card);padding:18px;border-right:1px solid rgba(0,0,0,0.03);
}
.sidebar .menu { list-style:none;padding:0;margin:8px 0 0 0 }
.sidebar .menu li { margin:6px 0; }
.sidebar .menu a {
  display:flex;gap:12px;align-items:center;padding:10px;border-radius:8px;color:var(--text);text-decoration:none;
  font-weight:600;
}
.sidebar .menu a .dot{width:10px;height:10px;border-radius:50%;background:var(--accent);margin-left:auto;opacity:0}
.sidebar .menu a.active, .sidebar .menu a:hover { background:linear-gradient(90deg, rgba(0,102,255,0.06), rgba(90,45,255,0.04));box-shadow:inset 0 0 0 1px rgba(0,0,0,0.02) }

/* main content area */
.main {
  flex:1;padding:22px;
}

/* small header within main */
.panel {
  background:var(--card);padding:18px;border-radius:12px;box-shadow:0 6px 20px rgba(2,6,23,0.04);
}

/* responsive */
@media (max-width:880px){
  .sidebar{ position:fixed; left:-320px; top:64px; height:calc(100% - 64px); transition:left .28s; z-index:60; }
  .sidebar.open{ left:0; }
  .wrapper{flex-direction:column;}
  .topbar{position:fixed;left:0;right:0;top:0}
  .main{padding-top:88px}
}

/* notification dropdown */
.notif-list{ position:absolute; right:12px; top:56px; width:320px; background:var(--card); border-radius:10px; box-shadow:0 10px 30px rgba(2,6,23,0.12); overflow:hidden; display:none; z-index:90}
.notif-list.show{display:block}
.notif-list .item{padding:12px;border-bottom:1px solid rgba(0,0,0,0.04); font-size:14px}
.notif-list .item:last-child{border-bottom:0}

/* toggle */
.toggle {
  display:flex;align-items:center;gap:8px;background:transparent;border-radius:999px;padding:6px;
}
.switch {
  width:44px;height:26px;background:#e6e6e6;border-radius:999px;position:relative;cursor:pointer;
}
.knob{width:18px;height:18px;border-radius:50%;background:white;position:absolute;left:4px;top:4px;box-shadow:0 4px 10px rgba(2,6,23,0.12);transition:all .22s;}
.switch.on{background:linear-gradient(90deg,var(--accent),var(--accent-2));}
.switch.on .knob{left:22px}

/* small footer in sidebar */
.sidebar .footer{ margin-top:18px;font-size:13px;color:var(--muted) }
</style>
</head>
<body data-theme="light">

<!-- Topbar -->
<div class="topbar">
  <div style="display:flex;gap:12px;align-items:center">
    <button id="hamb" class="icon-btn" title="Toggle menu" aria-label="menu" style="display:none;">☰</button>
    <div class="left">Class Manager Pro</div>
  </div>

  <div class="center">Welcome, <strong><?= htmlspecialchars($teacher) ?></strong></div>

  <div class="right">
    <!-- notifications -->
    <div style="position:relative">
      <button id="bell" class="icon-btn" title="Notifications">🔔</button>
      <div id="notif" class="notif-list" aria-hidden="true">
        <div class="item"><strong>No new notifications</strong></div>
        <div class="item">Tip: Use Next 10 to view more schedule rows.</div>
      </div>
    </div>

    <!-- dark mode toggle -->
    <div class="toggle" title="Toggle light / dark">
      <div id="switch" class="switch" role="button" tabindex="0" aria-pressed="false">
        <div class="knob"></div>
      </div>
    </div>

    <!-- profile -->
    <div style="display:flex;gap:8px;align-items:center;margin-left:6px">
      <div class="profile-circle"><?= htmlspecialchars($initials) ?></div>
    </div>
  </div>
</div>

<div class="wrapper">
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">
        C
      </div>
      <div>
        <div style="font-weight:700"><?= htmlspecialchars($teacher) ?></div>
        <div style="font-size:13px;color:var(--muted)">Teacher</div>
      </div>
    </div>

    <ul class="menu" style="margin-top:18px">
      <li><a href="dashboard.php" class="active">🏠 Dashboard <span class="dot"></span></a></li>
      <li><a href="index.php">➕ Create Schedule</a></li>
      <li><a href="schedules.php">📋 My Schedules</a></li>
      <li><a href="page2.php">📝 Entries</a></li>
      <li><a href="profile.php">👤 Profile</a></li>
      <li><a href="settings.php">⚙️ Settings</a></li>
      <li><a href="logout.php" style="color:#ff3b3b">🔓 Logout</a></li>
    </ul>

    <div class="footer">Class Manager Pro • LMC</div>
  </div>

  <!-- Main content -->
  <div class="main">
    <div class="panel">
      <!-- Example content area -->
      <h3>Dashboard</h3>
      <p style="color:var(--muted); margin-top:8px">
        Use the left menu to create schedules, view saved schedules and enter class details.
      </p>

      <!-- quick cards -->
      <div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap">
        <div style="flex:1;min-width:160px;background:var(--card);padding:12px;border-radius:10px;box-shadow:0 6px 18px rgba(2,6,23,0.04)">
          <div style="font-size:12px;color:var(--muted)">Schedules</div>
          <div style="font-weight:700;font-size:20px">12</div>
        </div>
        <div style="flex:1;min-width:160px;background:var(--card);padding:12px;border-radius:10px;box-shadow:0 6px 18px rgba(2,6,23,0.04)">
          <div style="font-size:12px;color:var(--muted)">Pending Entries</div>
          <div style="font-weight:700;font-size:20px">4</div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- JS: sidebar toggle, notif, dark mode -->
<script>
(function(){
  const bell = document.getElementById('bell');
  const notif = document.getElementById('notif');
  bell.addEventListener('click', e => {
    notif.classList.toggle('show');
  });
  document.addEventListener('click', e => {
    if (!bell.contains(e.target) && !notif.contains(e.target)) notif.classList.remove('show');
  });

  // dark mode toggle
  const sw = document.getElementById('switch');
  const body = document.body;
  const saved = localStorage.getItem('cm_theme') || 'light';
  body.setAttribute('data-theme', saved);
  if (saved === 'dark') { sw.classList.add('on'); sw.querySelector('.knob').style.left='22px'; }

  function setTheme(t){
    body.setAttribute('data-theme', t);
    localStorage.setItem('cm_theme', t);
    if (t === 'dark') { sw.classList.add('on'); sw.querySelector('.knob').style.left='22px'; }
    else { sw.classList.remove('on'); sw.querySelector('.knob').style.left='4px'; }
  }
  sw.addEventListener('click', () => {
    const now = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    setTheme(now);
  });

  // responsive sidebar (hamburger)
  const hamb = document.getElementById('hamb');
  const sidebar = document.getElementById('sidebar');
  // show hamburger on small screens
  function adapt() {
    if (window.innerWidth <= 880) hamb.style.display='inline-block'; else hamb.style.display='none';
  }
  adapt();
  window.addEventListener('resize', adapt);

  hamb.addEventListener('click', ()=> {
    sidebar.classList.toggle('open');
  });
})();
</script>

</body>
</html>

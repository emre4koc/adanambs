<aside id="sidebar" class="sidebar-nav fixed inset-y-0 left-0 z-30 w-64 transform -translate-x-full transition-transform duration-300 ease-in-out md:relative md:translate-x-0">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

        .sidebar-nav {
            background: linear-gradient(160deg, #0f172a 0%, #1e1b4b 60%, #0f172a 100%);
            border-right: 1px solid rgba(139, 92, 246, 0.15);
            font-family: 'Outfit', sans-serif;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Arka plan animasyonlu blob efekti */
        .sidebar-nav::before {
            content: '';
            position: absolute;
            top: -80px;
            left: -80px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.18) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            animation: blobFloat 8s ease-in-out infinite;
        }

        .sidebar-nav::after {
            content: '';
            position: absolute;
            bottom: 60px;
            right: -60px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            animation: blobFloat 10s ease-in-out infinite reverse;
        }

        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(15px, -20px) scale(1.05); }
            66% { transform: translate(-10px, 15px) scale(0.95); }
        }

        /* Logo alanı */
        .sidebar-logo {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70px;
            padding: 0 20px;
            background: rgba(139, 92, 246, 0.08);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            gap: 10px;
            flex-shrink: 0;
        }

        .sidebar-logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 13.5px;
            font-weight: 600;
            color: #e2e8f0;
            letter-spacing: 0.01em;
            line-height: 1.3;
        }

        /* Nav alanı */
        .sidebar-nav nav {
            position: relative;
            z-index: 2;
            padding: 14px 10px;
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(139,92,246,0.3) transparent;
        }

        /* Bölüm başlığı */
        .nav-section-label {
            padding: 14px 12px 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(139, 92, 246, 0.7);
        }

        /* Menü öğeleri */
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            margin-bottom: 3px;
            border-radius: 10px;
            color: rgba(203, 213, 225, 0.75);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .sidebar-nav a::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.12), rgba(6, 182, 212, 0.06));
            opacity: 0;
            transition: opacity 0.2s ease;
            border-radius: 10px;
        }

        .sidebar-nav a:hover {
            color: #f1f5f9;
            border-color: rgba(139, 92, 246, 0.25);
            transform: translateX(3px);
        }

        .sidebar-nav a:hover::before {
            opacity: 1;
        }

        .sidebar-nav a:hover .nav-icon {
            color: #a78bfa;
            transform: scale(1.1);
        }

        /* Active link - mevcut sayfayı vurgulamak için */
        .sidebar-nav a.active {
            color: #f1f5f9;
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.2), rgba(6, 182, 212, 0.08));
            border-color: rgba(139, 92, 246, 0.35);
        }

        .sidebar-nav a.active .nav-icon {
            color: #a78bfa;
        }

        /* İkon wrapper */
        .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 14px;
            color: rgba(148, 163, 184, 0.6);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        /* Admin panel label */
        .admin-divider {
            margin: 8px 12px;
            border: none;
            border-top: 1px solid rgba(139, 92, 246, 0.15);
        }

        /* Stagger animasyonu - ilk yüklemede */
        .sidebar-nav a {
            animation: navItemIn 0.4s ease both;
        }

        .sidebar-nav a:nth-child(1)  { animation-delay: 0.05s; }
        .sidebar-nav a:nth-child(2)  { animation-delay: 0.10s; }
        .sidebar-nav a:nth-child(3)  { animation-delay: 0.15s; }
        .sidebar-nav a:nth-child(4)  { animation-delay: 0.20s; }
        .sidebar-nav a:nth-child(5)  { animation-delay: 0.25s; }
        .sidebar-nav a:nth-child(6)  { animation-delay: 0.30s; }
        .sidebar-nav a:nth-child(7)  { animation-delay: 0.35s; }
        .sidebar-nav a:nth-child(8)  { animation-delay: 0.40s; }
        .sidebar-nav a:nth-child(9)  { animation-delay: 0.45s; }
        .sidebar-nav a:nth-child(10) { animation-delay: 0.50s; }

        @keyframes navItemIn {
            from { opacity: 0; transform: translateX(-12px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Scrollbar */
        .sidebar-nav nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav nav::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.3); border-radius: 4px; }
    </style>

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <i class="fas fa-whistle"></i>
        </div>
        <span class="sidebar-logo-text">Müsabaka Bilgi<br>Sistemi</span>
    </div>

    <nav>
        <?php
        // Pasif kullanıcı kontrolü
        $is_passive = isset($_SESSION['user_aktif']) && $_SESSION['user_aktif'] == 0;
        
        // Aktif sayfa tespiti
        $current_page = basename($_SERVER['PHP_SELF']);
        
        if (!$is_passive): 
            // AKTİF kullanıcılar için TÜM menüler
        ?>

        <a href="<?php echo BASE_URL; ?>/anasayfa.php" class="<?php echo $current_page == 'anasayfa.php' ? 'active' : ''; ?>">
            <i class="fas fa-home nav-icon"></i>
            <span>Anasayfa</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/gorevlerim.php" class="<?php echo $current_page == 'gorevlerim.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check nav-icon"></i>
            <span>Görevlerim</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/musaitlik.php" class="<?php echo $current_page == 'musaitlik.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-clock nav-icon"></i>
            <span>Müsaitlik Bildir</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/egitimler.php" class="<?php echo $current_page == 'egitimler.php' ? 'active' : ''; ?>">
            <i class="fas fa-school nav-icon"></i>
            <span>Eğitimler</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/gozlemci_canli.php" class="<?php echo $current_page == 'gozlemci_canli.php' ? 'active' : ''; ?>">
            <i class="fas fa-book nav-icon"></i>
            <span>Kronometre / Not Defteri</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/takvim.php" class="<?php echo $current_page == 'takvim.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt nav-icon"></i>
            <span>Takvim</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/rehber.php" class="<?php echo $current_page == 'rehber.php' ? 'active' : ''; ?>">
            <i class="fas fa-address-book nav-icon"></i>
            <span>Rehber</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/profil.php" class="<?php echo $current_page == 'profil.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog nav-icon"></i>
            <span>Profilim</span>
        </a>

        <?php if ($_SESSION['user_rol'] == 1): // Sadece Yönetici ?>
        <hr class="admin-divider">
        <div class="nav-section-label">Yönetici Paneli</div>
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt nav-icon"></i>
            <span>Yönetim Paneli</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/duyurular.php" class="<?php echo $current_page == 'duyurular.php' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn nav-icon"></i>
            <span>Duyurular</span>
        </a>
        <?php endif; ?>

        <?php else: 
            // PASİF kullanıcılar için SADECE 2 MENÜ
        ?>
        <a href="<?php echo BASE_URL; ?>/gorevlerim.php" class="<?php echo $current_page == 'gorevlerim.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check nav-icon"></i>
            <span>Görevlerim</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/profil.php" class="<?php echo $current_page == 'profil.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog nav-icon"></i>
            <span>Profilim</span>
        </a>
        <?php endif; ?>
    </nav>
</aside>
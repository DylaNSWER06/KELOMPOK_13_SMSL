<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sistem Manajemen Servis Laptop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            position: fixed;
            z-index: 1;
        }
        
        .sidebar .nav-item .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
        }
        
        .sidebar .nav-item .nav-link:hover {
            color: #fff;
        }
        
        .sidebar .nav-item .nav-link i {
            margin-right: 0.5rem;
        }
        
        .sidebar-brand {
            height: 4.375rem;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            z-index: 1;
            color: #fff;
        }
        
        .sidebar-divider {
            margin: 0 1rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .content {
            margin-left: 250px;
            padding: 1.5rem;
        }
        
        .topbar {
            height: 4.375rem;
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .dropdown-menu {
            min-width: 12rem;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
        }
        
        .dropdown-header {
            font-weight: 800;
            font-size: 0.65rem;
            color: #b7b9cc;
        }
        
        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SMSL</div>
            </a>
            
            <hr class="sidebar-divider">
            
            <div class="nav flex-column">
                <div class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <hr class="sidebar-divider">
                
                <div class="nav-item">
                    <a class="nav-link" href="pelanggan.php">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Pelanggan</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link" href="teknisi.php">
                        <i class="fas fa-fw fa-user-cog"></i>
                        <span>Teknisi</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link" href="servis.php">
                        <i class="fas fa-fw fa-tools"></i>
                        <span>Servis</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link" href="sparepart.php">
                        <i class="fas fa-fw fa-microchip"></i>
                        <span>Sparepart</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link" href="layanan.php">
                        <i class="fas fa-fw fa-list-alt"></i>
                        <span>Layanan</span>
                    </a>
                </div>
                
                <hr class="sidebar-divider">
                
                <div class="nav-item">
                    <a class="nav-link" href="laporan.php">
                        <i class="fas fa-fw fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link" href="analisis_sparepart.php">
                        <i class="fas fa-fw fa-chart-pie"></i>
                        <span>Analisis Sparepart</span>
                    </a>
                </div>
                
                <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin'): ?>
                <hr class="sidebar-divider">
                
                <div class="nav-item">
                    <a class="nav-link" href="admin.php">
                        <i class="fas fa-fw fa-users-cog"></i>
                        <span>Manajemen Admin</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Content Wrapper -->
        <div class="d-flex flex-column w-100">
            <!-- Topbar -->
            <nav class="topbar navbar navbar-expand navbar-light bg-white mb-4 static-top shadow">
                <div class="container-fluid">
                    <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggle">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
                    </div>
                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : $_SESSION['username'] ?>
                                    <?php if (isset($_SESSION['level'])): ?>
                                    <span class="badge bg-secondary"><?= ucfirst($_SESSION['level']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <i class="fas fa-user-circle fa-fw"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="change_password.php">
                                    <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Ubah Password
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="content">

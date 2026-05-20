<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$titulo = $titulo ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - MOOVA!</title>
    <?php
    $favicon_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST'];
    ?>
    <link rel="icon" type="image/png" href="<?= $favicon_base ?>/img/triges.png">
    <link rel="shortcut icon" type="image/png" href="<?= $favicon_base ?>/img/triges.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .outfit-font { font-family: 'Outfit', sans-serif; }
        
        /* Gradients and Backgrounds */
        .water-gradient, .sky-gradient { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); }
        .emerald-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .violet-gradient { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
        .amber-gradient { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .indigo-gradient { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); }
        .rose-gradient { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
        .btn-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); transition: all 0.3s ease; }
        .btn-primary:hover { box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4); transform: translateY(-2px); }
        .premium-gradient-text { background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-image: linear-gradient(135deg, #4f46e5, #e11d48); }
        .premium-gradient { background: linear-gradient(135deg, #4f46e5 0%, #e11d48 100%); }
        
        /* Background Blobs */
        .bg-blobs { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; background-color: #f8fafc; }
        .blob-1, .blob-2, .blob-3 { position: absolute; filter: blur(80px); border-radius: 50%; opacity: 0.5; animation: float 20s infinite alternate ease-in-out; }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: radial-gradient(circle, rgba(56,189,248,0.2) 0%, rgba(255,255,255,0) 70%); }
        .blob-2 { bottom: -20%; right: -10%; width: 60vw; height: 60vw; background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(255,255,255,0) 70%); animation-delay: -5s; }
        .blob-3 { top: 40%; left: 30%; width: 40vw; height: 40vw; background: radial-gradient(circle, rgba(16,185,129,0.1) 0%, rgba(255,255,255,0) 70%); animation-delay: -10s; }
        @keyframes float { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(5%, 5%) scale(1.1); } }

        /* Glassmorphism */
        .glass { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .glass-card, .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05); }
        .glass-input { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); }
        
        /* Tables */
        .table-separated { border-collapse: separate; border-spacing: 0 12px; }
        .table-row-card { background: white; border-radius: 1.5rem; box-shadow: 0 4px 15px -5px rgba(0,0,0,0.05); transition: all 0.3s ease; }
        .table-row-card:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -10px rgba(79,70,229,0.15); }
        .table-row-card td { border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .table-row-card td:first-child { border-left: 1px solid #f1f5f9; border-top-left-radius: 1.5rem; border-bottom-left-radius: 1.5rem; }
        .table-row-card td:last-child { border-right: 1px solid #f1f5f9; border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem; }
        .table-row-hover:hover { background-color: #f8fafc; }

        /* Upload Zone */
        .upload-zone { border: 2px dashed #cbd5e1; border-radius: 1.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden; background: #f8fafc; }
        .upload-zone:hover { border-color: #38bdf8; background: #f0f9ff; }
        .upload-zone.drag-over { border-color: #0ea5e9; background: #e0f2fe; }
        .upload-placeholder { pointer-events: none; }
        .preview-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: white; display: none; padding: 1rem; }
        .has-image .preview-img { display: block; }
        .has-image .upload-placeholder { display: none; }
        .remove-btn { position: absolute; top: 0.75rem; right: 0.75rem; width: 2rem; height: 2rem; background: #ef4444; color: white; border-radius: 50%; display: none; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .has-image .remove-btn { display: flex; }
        .remove-btn:hover { background: #dc2626; transform: scale(1.1); }

        /* Animations */
        .animate-fade { animation: fadeIn 0.6s ease-out; }
        .animate-slide, .animate-fade-up, .animate-fade-in-up { animation: slideUp 0.6s ease-out forwards; opacity: 0; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; opacity: 0; }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Other Utilities */
        .premium-shadow { box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05); }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); }
        
        /* Form Validation */
        .moova-field-error { border-color: #ef4444 !important; box-shadow: 0 0 0 4px rgba(239,68,68,0.1) !important; }
        .moova-error-text { color: #ef4444; font-size: 0.75rem; font-weight: 700; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem; animation: fadeIn 0.3s ease; }

        /* Scrollbar & Selection */
        .custom-scrollbar::-webkit-scrollbar, ::-webkit-scrollbar { width: 8px; height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track, ::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb, ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover, ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        ::selection { background: #0ea5e9; color: white; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="antialiased min-h-screen">
<div class="flex min-h-screen">
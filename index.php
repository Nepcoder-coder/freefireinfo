<?php
$playerData = null;
$error = null;

// Function to fetch item details from the new API (returns image directly)
function fetchItemDetails($itemId) {
    if (empty($itemId)) return null;

    // Direct image link from new API
    $imageUrl = "https://ff-itmes-api.vercel.app/?item_id=" . urlencode($itemId);

    // Optionally, you can check if the image exists (optional, for fallback)
    $headers = @get_headers($imageUrl, 1);
    if ($headers && strpos($headers[0], '200') !== false) {
        return [
            'id' => $itemId,
            'description' => 'Item #' . $itemId,
            'image_url' => $imageUrl
        ];
    }

    // Fallback if image not found
    return [
        'id' => $itemId,
        'description' => 'Item #' . $itemId,
        'image_url' => null
    ];
}

// Function to get multiple item details with caching
function getItemsDetails($itemIds) {
    static $cache = [];
    $items = [];
    
    if (is_array($itemIds)) {
        foreach ($itemIds as $id) {
            if (isset($cache[$id])) {
                $items[] = $cache[$id];
            } else {
                $itemDetail = fetchItemDetails($id);
                if ($itemDetail) {
                    $cache[$id] = $itemDetail;
                    $items[] = $itemDetail;
                } else {
                    // Add placeholder for items without icons
                    $items[] = [
                        'id' => $id,
                        'description' => 'Item #' . $id,
                        'image_url' => null
                    ];
                }
            }
        }
    }
    return $items;
}

// Enhanced function to get single item with fallback
function getSingleItemDetail($itemId, $itemType = 'Item') {
    if (empty($itemId)) return null;
    
    $itemDetail = fetchItemDetails($itemId);
    if ($itemDetail) {
        return $itemDetail;
    }
    
    // Return placeholder data if API fails
    return [
        'id' => $itemId,
        'description' => $itemType . ' #' . $itemId,
        'image_url' => null
    ];
}

if (isset($_POST['uid']) && isset($_POST['region'])) {
    $uid = htmlspecialchars($_POST['uid']);
    $region = htmlspecialchars($_POST['region']);
    
    $apiUrl = "https://freefireinfo.nepcoderapis.workers.dev/?uid=" . $uid . "&region=" . $region;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response !== false) {
        $playerData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "Invalid response from API";
        }
    } else {
        $error = "Failed to fetch player data. Please check the UID and try again.";
    }
}

$servers = [
    'bd' => 'Bangladesh',
    'ind' => 'India', 
    'pk' => 'Pakistan',
    'sg' => 'Singapore',
    'th' => 'Thailand',
    'vn' => 'Vietnam',
    'my' => 'Malaysia',
    'ph' => 'Philippines',
    'id' => 'Indonesia',
    'br' => 'Brazil',
    'mena' => 'Middle East & North Africa',
    'ru' => 'Russia',
    'us' => 'United States',
    'latam' => 'Latin America',
    'sa' => 'South Africa'
];

function getRankName($rank) {
    $ranks = [
        100 => 'Bronze I', 110 => 'Bronze II', 120 => 'Bronze III',
        200 => 'Silver I', 210 => 'Silver II', 220 => 'Silver III',
        300 => 'Gold I', 310 => 'Gold II', 320 => 'Gold III',
        400 => 'Platinum I', 410 => 'Platinum II', 420 => 'Platinum III',
        500 => 'Diamond I', 510 => 'Diamond II', 520 => 'Diamond III',
        600 => 'Heroic', 700 => 'Grand Master'
    ];
    return $ranks[$rank] ?? 'Unranked';
}

function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

function getGenderIcon($gender) {
    return $gender === 'Gender_MALE' ? 'fas fa-mars' : 'fas fa-venus';
}

function getModePreference($mode) {
    return $mode === 'ModePrefer_BR' ? 'Battle Royale' : 'Clash Squad';
}

function getRewardState($state) {
    return $state === 'REWARD_STATE_UNCLAIMED' ? 'Unclaimed' : 'Claimed';
}

function getAccountTypeText($type) {
    return $type == 1 ? 'Premium Account' : 'Guest Account';
}

// Function to display item with icon
function displayItemWithIcon($item, $defaultIcon = 'fas fa-question', $size = 'w-16 h-16') {
    if ($item && isset($item['image_url']) && $item['image_url']) {
        return '<img src="' . htmlspecialchars($item['image_url']) . '" alt="' . htmlspecialchars($item['description'] ?? 'Item') . '" class="' . $size . ' object-cover rounded-lg border-2 border-white/30 shadow-lg transition-all duration-300 hover:scale-105" loading="lazy">';
    } else {
        return '<div class="' . $size . ' bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg flex items-center justify-center border-2 border-white/30 shadow-lg transition-all duration-300 hover:scale-105"><i class="' . $defaultIcon . ' text-white text-2xl"></i></div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Free Fire Player Lookup - Complete Stats | NepDevs</title>
    <meta name="description" content="Advanced Free Fire player statistics lookup tool with real-time data from all regions. Get comprehensive player stats, rankings, and equipment details.">
    <meta name="keywords" content="Free Fire, player lookup, statistics, gaming, NepDevs, battle royale">
    <meta name="author" content="NepDevs">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Free Fire Player Lookup - Complete Stats">
    <meta property="og:description" content="Advanced Free Fire player statistics and information lookup tool">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Free Fire Player Lookup">
    <meta name="twitter:description" content="Get comprehensive Free Fire player statistics">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔥</text></svg>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'orbitron': ['Orbitron', 'monospace'],
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'gradient': 'gradient 15s ease infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'spin-slow': 'spin 6s linear infinite',
                        'wiggle': 'wiggle 1s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'scale-in': 'scaleIn 0.4s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'zoom-in': 'zoomIn 0.3s ease-out',
                        'shimmer': 'shimmer 2s linear infinite',
                        'typing': 'typing 3.5s steps(40, end)',
                        'blink': 'blink-caret .75s step-end infinite',
                    },
                    keyframes: {
                        gradient: {
                            '0%, 100%': { 'background-position': '0% 50%' },
                            '50%': { 'background-position': '100% 50%' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' },
                        },
                        glow: {
                            '0%': { 'box-shadow': '0 0 20px rgba(255, 107, 107, 0.3)' },
                            '100%': { 'box-shadow': '0 0 40px rgba(255, 107, 107, 0.6)' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(50px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        zoomIn: {
                            '0%': { transform: 'scale(0.8)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        shimmer: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' },
                        },
                        typing: {
                            '0%': { width: '0' },
                            '100%': { width: '100%' },
                        },
                        'blink-caret': {
                            '0%, 50%': { 'border-color': 'transparent' },
                            '51%, 100%': { 'border-color': 'currentColor' },
                        }
                    },
                }
            }
        }
    </script>
    
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Enhanced Light Mode Gradient */
        .light-gradient {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe, #00f2fe);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        /* Enhanced Dark Mode Gradient */
        .dark-gradient {
            background: linear-gradient(-45deg, #0f0f23, #16213e, #1a1a2e, #0f3460, #16537e);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        /* Enhanced Glass Effect */
        .glass-light {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .glass-dark {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        /* Enhanced Text Colors */
        .light .text-primary {
            color: #1a202c !important;
        }
        
        .light .text-secondary {
            color: #2d3748 !important;
        }
        
        .light .text-muted {
            color: #4a5568 !important;
        }
        
        .dark .text-primary {
            color: #ffffff !important;
        }
        
        .dark .text-secondary {
            color: #e2e8f0 !important;
        }
        
        .dark .text-muted {
            color: #a0aec0 !important;
        }
        
        /* Enhanced Card Hover Effects */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .dark .card-hover:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        
        /* Enhanced Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #ee5a52, #ff6b6b);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }
        
        /* Enhanced Progress Bar */
        .progress-bar {
            background: rgba(255, 255, 255, 0.1);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .light .progress-bar {
            background: rgba(0, 0, 0, 0.1);
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #ff6b6b, #ee5a52);
            height: 100%;
            transition: width 1.5s ease-in-out;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-image: linear-gradient(
                -45deg,
                rgba(255, 255, 255, .2) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, .2) 50%,
                rgba(255, 255, 255, .2) 75%,
                transparent 75%,
                transparent
            );
            background-size: 50px 50px;
            animation: shimmer 2s linear infinite;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        /* Fixed Positioning Elements */
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: bounce-slow 3s infinite;
        }
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        /* Enhanced Neon Text Effect */
        .neon-text {
            text-shadow: 0 0 10px currentColor, 0 0 20px currentColor, 0 0 30px currentColor;
        }
        
        .light .neon-text {
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }
        
        /* Item Display Styles */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }
        
        .item-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .light .item-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .item-card:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        .light .item-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: rgba(0, 0, 0, 0.2);
        }
        
        /* Shimmer Effect for Cards */
        .item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .item-card:hover::before {
            left: 100%;
        }
        
        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .floating-btn {
                bottom: 15px;
                right: 15px;
            }
            
            .theme-toggle {
                top: 15px;
                right: 15px;
            }
            
            .item-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 0.75rem;
            }
            
            .card-hover:hover {
                transform: translateY(-1px) scale(1.01);
            }
            
            .animate-gradient {
                animation-duration: 20s;
            }
            
            .animate-float {
                animation-duration: 8s;
            }
        }
        
        /* Enhanced Dark Mode Styles */
        .dark .glass-light {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        /* Light Mode Input Styles */
        .light input, .light select {
            background: rgba(255, 255, 255, 0.9) !important;
            color: #1a202c !important;
            border-color: rgba(0, 0, 0, 0.2) !important;
        }
        
        .light input::placeholder {
            color: #4a5568 !important;
        }
        
        .light option {
            background: #ffffff !important;
            color: #1a202c !important;
        }
        
        /* Smooth Transitions for Theme Switching */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 107, 107, 0.5);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 107, 107, 0.7);
        }
        
        /* Performance Optimizations */
        .will-change-transform {
            will-change: transform;
        }
        
        .gpu-accelerated {
            transform: translateZ(0);
            backface-visibility: hidden;
            perspective: 1000px;
        }
        
        /* Notification Styles */
        .notification {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Enhanced Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            transition: left 0.3s ease;
            z-index: 9999;
        }
        
        .dark .mobile-menu {
            background: rgba(0, 0, 0, 0.95);
        }
        
        .mobile-menu.open {
            left: 0;
        }
        
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .mobile-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        
        /* Typing Animation */
        .typing-text {
            overflow: hidden;
            border-right: .15em solid orange;
            white-space: nowrap;
            margin: 0 auto;
            letter-spacing: .15em;
            animation: typing 3.5s steps(40, end), blink-caret .75s step-end infinite;
        }
        
        /* Particle Effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            display: block;
            pointer-events: none;
            width: 4px;
            height: 4px;
            background: rgba(255, 107, 107, 0.6);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        /* Reduce Motion for Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Print Styles */
        @media print {
            .floating-btn, .theme-toggle, .mobile-menu, .mobile-overlay {
                display: none !important;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .glass-light, .glass-dark {
                background: white !important;
                border: 1px solid #ccc !important;
                box-shadow: none !important;
            }
        }
        
        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .glass-light, .glass-dark {
                border-width: 2px;
            }
            
            .light .text-primary {
                color: #000000 !important;
            }
            
            .light .text-secondary {
                color: #333333 !important;
            }
        }
        
        /* Enhanced Rank Badge Styles */
        .rank-badge {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #1a202c;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: bold;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        /* Stats Counter Animation */
        .counter {
            font-variant-numeric: tabular-nums;
        }
        
        /* Enhanced Loading States */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        .dark .skeleton {
            background: linear-gradient(90deg, #2d3748 25%, #4a5568 50%, #2d3748 75%);
            background-size: 200% 100%;
        }
    </style>
</head>

<body class="min-h-screen transition-all duration-300 light-gradient dark:dark-gradient relative overflow-x-hidden">
    <!-- Particle Background -->
    <div class="particles" id="particles"></div>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="p-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Menu</h2>
                <button onclick="closeMobileMenu()" class="text-gray-800 dark:text-white text-2xl hover:scale-110 transition-transform">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="space-y-4">
                <a href="#search" onclick="closeMobileMenu()" class="block text-gray-800 dark:text-white hover:text-orange-500 transition-colors p-3 rounded-lg hover:bg-orange-500/10">
                    <i class="fas fa-search mr-3"></i>Player Search
                </a>
                <a href="#stats" onclick="closeMobileMenu()" class="block text-gray-800 dark:text-white hover:text-orange-500 transition-colors p-3 rounded-lg hover:bg-orange-500/10">
                    <i class="fas fa-chart-bar mr-3"></i>Statistics
                </a>
                <a href="#features" onclick="closeMobileMenu()" class="block text-gray-800 dark:text-white hover:text-orange-500 transition-colors p-3 rounded-lg hover:bg-orange-500/10">
                    <i class="fas fa-star mr-3"></i>Features
                </a>
                <a href="https://t.me/nepdevsz" target="_blank" onclick="closeMobileMenu()" class="block text-gray-800 dark:text-white hover:text-orange-500 transition-colors p-3 rounded-lg hover:bg-orange-500/10">
                    <i class="fab fa-telegram mr-3"></i>Join Community
                </a>
                <button onclick="toggleTheme(); closeMobileMenu();" class="block w-full text-left text-gray-800 dark:text-white hover:text-orange-500 transition-colors p-3 rounded-lg hover:bg-orange-500/10">
                    <i class="fas fa-moon dark:hidden mr-3"></i>
                    <i class="fas fa-sun hidden dark:inline mr-3"></i>
                    <span class="dark:hidden">Dark Mode</span>
                    <span class="hidden dark:inline">Light Mode</span>
                </button>
            </nav>
        </div>
    </div>
    
    <!-- Mobile Menu Button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button onclick="openMobileMenu()" class="glass-light dark:glass-dark p-3 rounded-full text-gray-800 dark:text-white hover:scale-110 transition-all duration-300 shadow-lg">
            <i class="fas fa-bars text-lg"></i>
        </button>
    </div>
    
    <!-- Theme Toggle Button -->
    <div class="theme-toggle">
        <button onclick="toggleTheme()" class="glass-light dark:glass-dark p-3 rounded-full text-gray-800 dark:text-white hover:scale-110 transition-all duration-300 shadow-lg" title="Toggle Theme">
            <i class="fas fa-moon dark:hidden text-lg"></i>
            <i class="fas fa-sun hidden dark:inline text-lg"></i>
        </button>
    </div>

    <!-- Floating Action Buttons -->
    <div class="floating-btn">
        <div class="flex flex-col space-y-3">
            <!-- Telegram Button -->
            <a href="https://t.me/nepdevsz" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 will-change-transform hover:scale-110" title="Join Telegram">
                <i class="fab fa-telegram-plane text-lg"></i>
            </a>
            
            <!-- Scroll to Top Button -->
            <button onclick="scrollToTop()" class="bg-orange-500 hover:bg-orange-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 will-change-transform hidden hover:scale-110" id="scrollTopBtn" title="Scroll to Top">
                <i class="fas fa-arrow-up text-lg"></i>
            </button>
            
            <!-- Quick Search Button -->
            <button onclick="focusSearch()" class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 will-change-transform hover:scale-110" title="Quick Search">
                <i class="fas fa-search text-lg"></i>
            </button>
            
            <!-- Random Player Button -->
            <button onclick="randomPlayer()" class="bg-purple-500 hover:bg-purple-600 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 will-change-transform hover:scale-110" title="Random Player">
                <i class="fas fa-random text-lg"></i>
            </button>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6 max-w-6xl relative z-10">
        <!-- Header Section -->
        <div class="text-center mb-8 animate-fade-in" id="header">
            <div class="inline-block glass-light dark:glass-dark p-6 rounded-full mb-6 animate-float gpu-accelerated shadow-2xl">
                <i class="fas fa-fire text-5xl md:text-6xl text-orange-500 animate-wiggle"></i>
            </div>
            <h1 class="font-orbitron text-3xl md:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white mb-4 neon-text">
                FREE FIRE
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-red-500 to-pink-500 typing-text">
                    PLAYER LOOKUP
                </span>
            </h1>
            <p class="text-lg md:text-xl text-gray-800 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed mb-6">
                🔥 Advanced Free Fire player statistics and information lookup tool
                <br><span class="text-base text-gray-700 dark:text-gray-400">Powered by NepDevs - Real-time data from all regions</span>
            </p>
            
            <!-- Enhanced Stats Pills -->
            <div class="flex flex-wrap justify-center gap-3 md:gap-4 mb-6">
                <div class="glass-light dark:glass-dark px-4 py-2 rounded-full animate-scale-in hover:scale-105 transition-transform cursor-pointer">
                    <i class="fas fa-users text-blue-500 mr-2"></i>
                    <span class="text-gray-900 dark:text-white font-semibold text-sm md:text-base counter" data-target="100000000">0</span>
                    <span class="text-gray-900 dark:text-white font-semibold text-sm md:text-base">+ Players</span>
                </div>
                <div class="glass-light dark:glass-dark px-4 py-2 rounded-full animate-scale-in hover:scale-105 transition-transform cursor-pointer" style="animation-delay: 0.1s;">
                    <i class="fas fa-globe text-green-500 mr-2"></i>
                    <span class="text-gray-900 dark:text-white font-semibold text-sm md:text-base counter" data-target="15">0</span>
                    <span class="text-gray-900 dark:text-white font-semibold text-sm md:text-base">+ Regions</span>
                </div>
                <div class="glass-light dark:glass-dark px-4 py-2 rounded-full animate-scale-in hover:scale-105 transition-transform cursor-pointer" style="animation-delay: 0.2s;">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                    <span class="text-gray-900 dark:text-white font-semibold text-sm md:text-base">Real-time</span>
                </div>
                <div class="glass-light dark:glass-dark px-4 py-2 rounded-full animate-scale-in hover:scale-105 transition-transform cursor-pointer" style="animation-delay: 0.3s;">
                    <i class="fas fa-shield-alt text-purple-500 mr-2"></i>
                    <span class="text-gray-900 dark:text-white font-semibold text-sm md:text-base">Secure</span>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="max-w-2xl mx-auto mb-8 animate-slide-up" id="search">
            <div class="glass-light dark:glass-dark rounded-2xl p-6 md:p-8 card-hover shadow-2xl">
                <div class="text-center mb-6">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">🎯 Player Search</h2>
                    <p class="text-gray-700 dark:text-gray-400">Enter player details to view comprehensive statistics</p>
                </div>
                
                <form method="POST" class="space-y-6" id="searchForm">
                    <div class="space-y-4">
                        <!-- Region Selection -->
                        <div>
                            <label for="region" class="block text-gray-900 dark:text-white font-semibold text-lg mb-3">
                                <i class="fas fa-globe-americas mr-2 text-blue-500"></i>Server Region
                            </label>
                            <select class="w-full px-4 py-4 bg-white/90 dark:bg-white/10 border-2 border-gray-300 dark:border-white/20 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-4 focus:ring-orange-400/50 focus:border-orange-400 backdrop-blur-sm text-base font-medium transition-all duration-300 hover:border-orange-300" 
                                    id="region" name="region" required>
                                <option value="" class="text-gray-900 bg-white dark:bg-gray-800 dark:text-white">🎯 Choose your server region...</option>
                                <?php foreach ($servers as $code => $name): ?>
                                    <option value="<?= $code ?>" class="text-gray-900 bg-white dark:bg-gray-800 dark:text-white" <?= (isset($_POST['region']) && $_POST['region'] === $code) ? 'selected' : '' ?>>
                                        🌐 <?= $name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- UID Input -->
                        <div>
                            <label for="uid" class="block text-gray-900 dark:text-white font-semibold text-lg mb-3">
                                <i class="fas fa-user-tag mr-2 text-green-500"></i>Player UID
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-4 bg-white/90 dark:bg-white/10 border-2 border-gray-300 dark:border-white/20 rounded-xl text-gray-900 dark:text-white placeholder-gray-600 dark:placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-green-400/50 focus:border-green-400 backdrop-blur-sm text-base font-medium transition-all duration-300 hover:border-green-300" 
                                   id="uid" name="uid" 
                                   placeholder="🔢 Enter 9-12 digit UID" 
                                   value="<?= htmlspecialchars($_POST['uid'] ?? '') ?>" 
                                   pattern="[0-9]{9,12}"
                                   required>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn-primary text-white font-bold py-4 px-8 md:px-12 rounded-xl text-lg shadow-lg hover:shadow-xl transform transition-all duration-300 relative overflow-hidden w-full md:w-auto will-change-transform" id="searchBtn">
                            <i class="fas fa-search mr-3 text-xl"></i>
                            <span id="btnText" class="font-orbitron">🚀 SEARCH PLAYER</span>
                            <div id="btnLoading" class="loading-spinner ml-3 hidden"></div>
                        </button>
                    </div>
                </form>
                
                <!-- Quick Search Suggestions -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">💡 Quick Tips:</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="bg-blue-500/20 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-xs hover:bg-blue-500/30 transition-colors cursor-pointer">UID must be 9-12 digits</span>
                        <span class="bg-green-500/20 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-xs hover:bg-green-500/30 transition-colors cursor-pointer">Select correct region</span>
                        <span class="bg-purple-500/20 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-xs hover:bg-purple-500/30 transition-colors cursor-pointer">Real-time data</span>
                    </div>
                </div>
                
                <!-- Recent Searches -->
                <div class="mt-6" id="recentSearches" style="display: none;">
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">🕒 Recent Searches:</p>
                    <div class="flex flex-wrap gap-2" id="recentSearchesList">
                        <!-- Recent searches will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <!-- Enhanced Error Message -->
            <div class="max-w-2xl mx-auto mb-8 animate-slide-up">
                <div class="bg-red-500/20 border-2 border-red-500/40 text-red-900 dark:text-red-200 p-6 rounded-2xl backdrop-blur-sm shadow-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-3xl mr-4 animate-bounce"></i>
                        <div>
                            <h3 class="font-bold text-xl mb-2">⚠️ Error Occurred</h3>
                            <p class="text-lg"><?= htmlspecialchars($error) ?></p>
                            <div class="mt-4 flex gap-3">
                                <button onclick="retrySearch()" class="bg-red-500/30 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-sm hover:bg-red-500/50 transition-all font-semibold">
                                    <i class="fas fa-redo mr-2"></i>Try Again
                                </button>
                                <button onclick="clearForm()" class="bg-gray-500/30 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-full text-sm hover:bg-gray-500/50 transition-all font-semibold">
                                    <i class="fas fa-times mr-2"></i>Clear Form
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($playerData && isset($playerData['AccountInfo'])): ?>
            <!-- Enhanced Player Profile Header -->
            <div class="mb-8 animate-fade-in" id="stats">
                <div class="glass-light dark:glass-dark rounded-2xl p-6 md:p-8 card-hover shadow-2xl">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                        <div class="flex-shrink-0">
                            <?php 
                            $avatarDetail = getSingleItemDetail($playerData['AccountInfo']['AccountAvatarId'], 'Avatar');
                            ?>
                            <div class="relative">
                                <?= displayItemWithIcon($avatarDetail, 'fas fa-user', 'w-24 h-24 md:w-32 md:h-32') ?>
                                <div class="absolute -bottom-2 -right-2 bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold animate-bounce-slow shadow-lg">
                                    <?= $playerData['AccountInfo']['AccountLevel'] ?>
                                </div>
                                <!-- Online Status Indicator -->
                                <div class="absolute -top-2 -left-2 bg-green-500 rounded-full w-6 h-6 flex items-center justify-center animate-pulse shadow-lg">
                                    <i class="fas fa-circle text-white text-xs"></i>
                                </div>
                                <!-- Premium Badge -->
                                <?php if ($playerData['AccountInfo']['AccountType'] == 1): ?>
                                <div class="absolute top-0 right-0 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs animate-pulse">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-grow text-center md:text-left">
                            <h2 class="font-orbitron text-2xl md:text-4xl lg:text-5xl font-black text-gray-900 dark:text-white mb-4 neon-text">
                                <?= htmlspecialchars($playerData['AccountInfo']['AccountName']) ?>
                            </h2>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 md:gap-3 mb-4">
                                <span class="bg-blue-500/30 text-blue-900 dark:text-blue-200 px-3 py-2 rounded-full text-sm font-bold backdrop-blur-sm hover:bg-blue-500/40 transition-colors">
                                    <i class="fas fa-level-up-alt mr-1"></i>Level <?= $playerData['AccountInfo']['AccountLevel'] ?>
                                </span>
                                <span class="bg-green-500/30 text-green-900 dark:text-green-200 px-3 py-2 rounded-full text-sm font-bold backdrop-blur-sm hover:bg-green-500/40 transition-colors">
                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($playerData['AccountInfo']['AccountRegion']) ?>
                                </span>
                                <span class="bg-purple-500/30 text-purple-900 dark:text-purple-200 px-3 py-2 rounded-full text-sm font-bold backdrop-blur-sm hover:bg-purple-500/40 transition-colors">
                                    <i class="fas fa-gamepad mr-1"></i><?= htmlspecialchars($playerData['AccountInfo']['ReleaseVersion']) ?>
                                </span>
                                <span class="bg-orange-500/30 text-orange-900 dark:text-orange-200 px-3 py-2 rounded-full text-sm font-bold backdrop-blur-sm hover:bg-orange-500/40 transition-colors">
                                    <i class="fas fa-calendar mr-1"></i>Season <?= $playerData['AccountInfo']['AccountSeasonId'] ?>
                                </span>
                            </div>
                            
                            <!-- Enhanced Stats Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 text-center">
                                <div class="glass-light dark:glass-dark p-3 rounded-xl card-hover">
                                    <div class="text-xl md:text-2xl font-bold text-yellow-600 dark:text-yellow-400 counter" data-target="<?= $playerData['AccountInfo']['AccountEXP'] ?>">0</div>
                                    <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Experience</div>
                                </div>
                                <div class="glass-light dark:glass-dark p-3 rounded-xl card-hover">
                                    <div class="text-xl md:text-2xl font-bold text-pink-600 dark:text-pink-400 counter" data-target="<?= $playerData['AccountInfo']['AccountLikes'] ?>">0</div>
                                    <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Likes</div>
                                </div>
                                <div class="glass-light dark:glass-dark p-3 rounded-xl card-hover">
                                    <div class="text-xl md:text-2xl font-bold text-blue-600 dark:text-blue-400"><?= date('Y', $playerData['AccountInfo']['AccountCreateTime']) ?></div>
                                    <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Member Since</div>
                                </div>
                                <div class="glass-light dark:glass-dark p-3 rounded-xl card-hover">
                                    <div class="text-xl md:text-2xl font-bold text-green-600 dark:text-green-400"><?= $playerData['AccountInfo']['AccountBPBadges'] ?></div>
                                    <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">BP Badges</div>
                                </div>
                                <div class="glass-light dark:glass-dark p-3 rounded-xl card-hover">
                                    <div class="text-xl md:text-2xl font-bold text-purple-600 dark:text-purple-400"><?= $playerData['AccountInfo']['DiamondCost'] ?></div>
                                    <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Diamonds</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Account Information with Icons -->
            <div class="mb-8">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-6 neon-text">📊 Complete Account Information</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Account Type -->
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-id-card text-3xl text-blue-500 mb-3"></i>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= getAccountTypeText($playerData['AccountInfo']['AccountType']) ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs">Account Type</div>
                    </div>
                    
                    <!-- BP Badge with Icon -->
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <?php 
                        $bpBadgeDetail = getSingleItemDetail($playerData['AccountInfo']['AccountBPID'], 'BP Badge');
                        echo displayItemWithIcon($bpBadgeDetail, 'fas fa-medal', 'w-12 h-12 mx-auto mb-2');
                        ?>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= $playerData['AccountInfo']['AccountBPID'] ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs">BP Badge</div>
                    </div>
                    
                    <!-- Banner with Icon -->
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <?php 
                        $bannerDetail = getSingleItemDetail($playerData['AccountInfo']['AccountBannerId'], 'Banner');
                        echo displayItemWithIcon($bannerDetail, 'fas fa-flag', 'w-12 h-12 mx-auto mb-2');
                        ?>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= $playerData['AccountInfo']['AccountBannerId'] ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs">Banner</div>
                    </div>
                    
                    <!-- Title with Icon -->
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <?php 
                        $titleDetail = getSingleItemDetail($playerData['AccountInfo']['Title'], 'Title');
                        echo displayItemWithIcon($titleDetail, 'fas fa-trophy', 'w-12 h-12 mx-auto mb-2');
                        ?>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= $playerData['AccountInfo']['Title'] ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs">Title</div>
                    </div>
                    
                    <!-- Other stats -->
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-calendar-plus text-3xl text-green-500 mb-3"></i>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= date('M d, Y', $playerData['AccountInfo']['AccountCreateTime']) ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Created</div>
                    </div>
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-clock text-3xl text-orange-500 mb-3"></i>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= date('M d, Y', $playerData['AccountInfo']['AccountLastLogin']) ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Last Login</div>
                    </div>
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-user-circle text-3xl text-purple-500 mb-3"></i>
                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= $playerData['AccountInfo']['AccountAvatarId'] ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Avatar ID</div>
                    </div>
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-calendar-alt text-3xl text-red-500 mb-3"></i>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Season <?= $playerData['AccountInfo']['AccountSeasonId'] ?></div>
                        <div class="text-gray-700 dark:text-gray-400 text-xs font-semibold">Current Season</div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Rank Cards -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- BR Rank Card -->
                <div class="glass-light dark:glass-dark rounded-2xl p-6 card-hover shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">🏆 Battle Royale</h3>
                        <i class="fas fa-trophy text-4xl md:text-5xl text-yellow-500 animate-bounce-slow"></i>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 dark:text-gray-400">Current Rank</span>
                            <span class="rank-badge"><?= getRankName($playerData['AccountInfo']['BrMaxRank']) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 dark:text-gray-400">Max Rank</span>
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white"><?= $playerData['AccountInfo']['BrMaxRank'] ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 dark:text-gray-400">Rank Points</span>
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white counter" data-target="<?= $playerData['AccountInfo']['BrRankPoint'] ?>">0</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min(100, ($playerData['AccountInfo']['BrRankPoint'] / 200000) * 100) ?>%"></div>
                        </div>
                        <div class="text-center">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Rank Display: <?= $playerData['AccountInfo']['ShowBrRank'] ? '✅ Enabled' : '❌ Disabled' ?></span>
                        </div>
                    </div>
                </div>

                <!-- CS Rank Card -->
                <div class="glass-light dark:glass-dark rounded-2xl p-6 card-hover shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">⚔️ Clash Squad</h3>
                        <i class="fas fa-crosshairs text-4xl md:text-5xl text-red-500 animate-pulse-slow"></i>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 dark:text-gray-400">Current Rank</span>
                            <span class="rank-badge bg-gradient-to-r from-red-500 to-pink-500"><?= getRankName($playerData['AccountInfo']['CsMaxRank']) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 dark:text-gray-400">Max Rank</span>
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white"><?= $playerData['AccountInfo']['CsMaxRank'] ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 dark:text-gray-400">Rank Points</span>
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white counter" data-target="<?= $playerData['AccountInfo']['CsRankPoint'] ?>">0</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min(100, ($playerData['AccountInfo']['CsRankPoint'] / 100000) * 100) ?>%"></div>
                        </div>
                        <div class="text-center">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Rank Display: <?= $playerData['AccountInfo']['ShowCsRank'] ? '✅ Enabled' : '❌ Disabled' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Equipped Weapons with Icons -->
            <?php if (isset($playerData['captainBasicInfo']['EquippedWeapon']) && !empty($playerData['captainBasicInfo']['EquippedWeapon'])): ?>
            <div class="mb-8">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-6 neon-text">🔫 Equipped Weapons</h3>
                <div class="item-grid">
                    <?php 
                    $equippedWeapons = getItemsDetails($playerData['captainBasicInfo']['EquippedWeapon']);
                    foreach ($equippedWeapons as $index => $weapon): 
                    ?>
                        <div class="item-card animate-scale-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <?= displayItemWithIcon($weapon, 'fas fa-gun', 'w-16 h-16 mx-auto mb-3') ?>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($weapon['description'] ?? 'Unknown Weapon') ?></h4>
                            <p class="text-gray-700 dark:text-gray-400 text-xs">ID: <?= $weapon['id'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Equipped Outfit with Icons -->
            <?php if (isset($playerData['AccountProfileInfo']['EquippedOutfit']) && !empty($playerData['AccountProfileInfo']['EquippedOutfit'])): ?>
            <div class="mb-8">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-6 neon-text">👕 Equipped Outfit</h3>
                <div class="item-grid">
                    <?php 
                    $equippedOutfit = getItemsDetails($playerData['AccountProfileInfo']['EquippedOutfit']);
                    foreach ($equippedOutfit as $index => $outfit): 
                    ?>
                        <div class="item-card animate-scale-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <?= displayItemWithIcon($outfit, 'fas fa-tshirt', 'w-16 h-16 mx-auto mb-3') ?>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($outfit['description'] ?? 'Unknown Item') ?></h4>
                            <p class="text-gray-700 dark:text-gray-400 text-xs">ID: <?= $outfit['id'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Equipped Skills with Icons -->
            <?php if (isset($playerData['AccountProfileInfo']['EquippedSkills']) && !empty($playerData['AccountProfileInfo']['EquippedSkills'])): ?>
            <div class="mb-8">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-6 neon-text">⚡ Equipped Skills</h3>
                <div class="item-grid">
                    <?php 
                    $equippedSkills = getItemsDetails($playerData['AccountProfileInfo']['EquippedSkills']);
                    foreach ($equippedSkills as $index => $skill): 
                    ?>
                        <div class="item-card animate-scale-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <?= displayItemWithIcon($skill, 'fas fa-magic', 'w-16 h-16 mx-auto mb-3') ?>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($skill['description'] ?? 'Unknown Skill') ?></h4>
                            <p class="text-gray-700 dark:text-gray-400 text-xs">ID: <?= $skill['id'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Pet Information with Icon -->
            <?php if (isset($playerData['petInfo'])): ?>
            <div class="mb-8">
                <div class="glass-light dark:glass-dark rounded-2xl p-6 card-hover shadow-xl">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-paw text-4xl text-orange-500 mr-4 animate-bounce-slow"></i>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white neon-text">🐾 Pet Information</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <?php 
                            $petDetail = getSingleItemDetail($playerData['petInfo']['id'], 'Pet');
                            echo displayItemWithIcon($petDetail, 'fas fa-dog', 'w-12 h-12 mx-auto mb-2');
                            ?>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($playerData['petInfo']['name'] ?? 'Pet') ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Pet Name</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-level-up-alt text-3xl text-blue-500 mb-3"></i>
                            <div class="text-xl font-bold text-gray-900 dark:text-white mb-1">Level <?= $playerData['petInfo']['level'] ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Pet Level</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-star text-3xl text-yellow-500 mb-3"></i>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1 counter" data-target="<?= $playerData['petInfo']['exp'] ?>">0</div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Pet EXP</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-check-circle text-3xl <?= $playerData['petInfo']['isSelected'] ? 'text-green-500' : 'text-gray-500' ?> mb-3"></i>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $playerData['petInfo']['isSelected'] ? 'Active' : 'Inactive' ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Status</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <?php 
                            $petSkinDetail = getSingleItemDetail($playerData['petInfo']['skinId'], 'Pet Skin');
                            echo displayItemWithIcon($petSkinDetail, 'fas fa-palette', 'w-12 h-12 mx-auto mb-2');
                            ?>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $playerData['petInfo']['skinId'] ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Skin ID</div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <div class="glass-light dark:glass-dark p-3 rounded-xl inline-block">
                            <span class="text-gray-700 dark:text-gray-400 text-sm">Pet ID: </span>
                            <span class="text-gray-900 dark:text-white font-bold"><?= $playerData['petInfo']['id'] ?></span>
                            <span class="text-gray-700 dark:text-gray-400 text-sm ml-4">Selected Skill: </span>
                            <span class="text-gray-900 dark:text-white font-bold"><?= $playerData['petInfo']['selectedSkillId'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Credit Score Information -->
            <?php if (isset($playerData['creditScoreInfo'])): ?>
            <div class="mb-8">
                <div class="glass-light dark:glass-dark rounded-2xl p-6 card-hover shadow-xl">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-credit-card text-4xl text-green-500 mr-4 animate-pulse-slow"></i>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white neon-text">💳 Credit Score Information</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center glass-light dark:glass-dark p-6 rounded-xl card-hover">
                            <i class="fas fa-chart-line text-5xl text-green-500 mb-4"></i>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2 counter" data-target="<?= $playerData['creditScoreInfo']['creditScore'] ?>">0</div>
                            <div class="text-gray-700 dark:text-gray-400">Credit Score</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-6 rounded-xl card-hover">
                            <i class="fas fa-gift text-5xl <?= $playerData['creditScoreInfo']['rewardState'] === 'REWARD_STATE_UNCLAIMED' ? 'text-yellow-500' : 'text-gray-500' ?> mb-4"></i>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?= getRewardState($playerData['creditScoreInfo']['rewardState']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400">Reward Status</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-6 rounded-xl card-hover">
                            <i class="fas fa-calendar-times text-5xl text-red-500 mb-4"></i>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-2"><?= date('M d, Y', $playerData['creditScoreInfo']['periodicSummaryEndTime']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400">Summary End Time</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Social Information -->
            <?php if (isset($playerData['socialinfo'])): ?>
            <div class="mb-8">
                <div class="glass-light dark:glass-dark rounded-2xl p-6 card-hover shadow-xl">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-users text-4xl text-blue-500 mr-4"></i>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white neon-text">👥 Social Profile</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="<?= getGenderIcon($playerData['socialinfo']['Gender']) ?> text-3xl mb-3 <?= $playerData['socialinfo']['Gender'] === 'Gender_MALE' ? 'text-blue-500' : 'text-pink-500' ?>"></i>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= str_replace('Gender_', '', $playerData['socialinfo']['Gender']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Gender</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-language text-3xl text-green-500 mb-3"></i>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= str_replace('Language_', '', $playerData['socialinfo']['AccountLanguage']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Language</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-gamepad text-3xl text-purple-500 mb-3"></i>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= getModePreference($playerData['socialinfo']['ModePreference']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Preferred Mode</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-eye text-3xl text-orange-500 mb-3"></i>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= str_replace('RankShow_', '', $playerData['socialinfo']['RankDisplay']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Rank Display</div>
                        </div>
                    </div>
                    <div class="mt-6 text-center">
                        <div class="glass-light dark:glass-dark p-3 rounded-xl inline-block">
                            <span class="text-gray-700 dark:text-gray-400 text-sm">Account ID: </span>
                            <span class="text-gray-900 dark:text-white font-bold"><?= $playerData['socialinfo']['AccountID'] ?></span>
                        </div>
                    </div>
                    <?php if (!empty($playerData['socialinfo']['AccountSignature'])): ?>
                    <div class="mt-6 text-center">
                        <div class="glass-light dark:glass-dark p-4 rounded-xl">
                            <i class="fas fa-quote-left text-2xl text-yellow-500 mb-3"></i>
                            <p class="text-lg text-gray-900 dark:text-white italic">"<?= htmlspecialchars($playerData['socialinfo']['AccountSignature']) ?>"</p>
                            <div class="text-gray-700 dark:text-gray-400 text-xs mt-2">Player Signature</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Battle Tags Information -->
                    <div class="mt-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 text-center">🏷️ Battle Tags</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                                <i class="fas fa-tags text-3xl text-blue-500 mb-3"></i>
                                <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= count($playerData['socialinfo']['BattleTags']) ?></div>
                                <div class="text-gray-700 dark:text-gray-400 text-xs">Total Battle Tags</div>
                            </div>
                            <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                                <i class="fas fa-hashtag text-3xl text-purple-500 mb-3"></i>
                                <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= count($playerData['socialinfo']['BattleTagCount']) ?></div>
                                <div class="text-gray-700 dark:text-gray-400 text-xs">Tag Count Entries</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Guild Information -->
            <?php if (isset($playerData['GuildInfo']) && !empty($playerData['GuildInfo']['GuildName'])): ?>
            <div class="mb-8">
                <div class="glass-light dark:glass-dark rounded-2xl p-6 card-hover shadow-xl">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-shield-alt text-4xl text-green-500 mr-4 animate-pulse-slow"></i>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white neon-text">🛡️ Guild Information</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-crown text-3xl text-yellow-500 mb-3"></i>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($playerData['GuildInfo']['GuildName']) ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Guild Name</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-level-up-alt text-3xl text-blue-500 mb-3"></i>
                            <div class="text-xl font-bold text-gray-900 dark:text-white mb-1">Level <?= $playerData['GuildInfo']['GuildLevel'] ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Guild Level</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-users text-3xl text-purple-500 mb-3"></i>
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1"><?= $playerData['GuildInfo']['GuildMember'] ?>/<?= $playerData['GuildInfo']['GuildCapacity'] ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Members</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-hashtag text-3xl text-orange-500 mb-3"></i>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $playerData['GuildInfo']['GuildID'] ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Guild ID</div>
                        </div>
                        <div class="text-center glass-light dark:glass-dark p-4 rounded-xl card-hover">
                            <i class="fas fa-user-crown text-3xl text-red-500 mb-3"></i>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $playerData['GuildInfo']['GuildOwner'] ?></div>
                            <div class="text-gray-700 dark:text-gray-400 text-xs">Guild Owner</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Action Cards -->
            <div class="mb-8" id="features">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-6 neon-text">🚀 Player Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-share-alt text-3xl text-blue-500 mb-3 animate-bounce-slow"></i>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Share</h4>
                        <button onclick="shareProfile()" class="bg-blue-500/30 text-blue-900 dark:text-blue-200 px-4 py-2 rounded-full text-sm hover:bg-blue-500/50 transition-all font-semibold w-full hover:scale-105">
                            <i class="fas fa-copy mr-2"></i>Copy Link
                        </button>
                    </div>

                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-sync-alt text-3xl text-green-500 mb-3 animate-spin-slow"></i>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Refresh</h4>
                        <button onclick="refreshData()" class="bg-green-500/30 text-green-900 dark:text-green-200 px-4 py-2 rounded-full text-sm hover:bg-green-500/50 transition-all font-semibold w-full hover:scale-105">
                            <i class="fas fa-refresh mr-2"></i>Update
                        </button>
                    </div>

                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fab fa-telegram text-3xl text-blue-500 mb-3 animate-pulse-slow"></i>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Community</h4>
                        <a href="https://t.me/nepdevsz" target="_blank" class="bg-blue-500/30 text-blue-900 dark:text-blue-200 px-4 py-2 rounded-full text-sm hover:bg-blue-500/50 transition-all font-semibold inline-block w-full hover:scale-105">
                            <i class="fab fa-telegram mr-2"></i>Join
                        </a>
                    </div>

                    <div class="glass-light dark:glass-dark rounded-xl p-4 text-center card-hover">
                        <i class="fas fa-download text-3xl text-purple-500 mb-3"></i>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Export</h4>
                        <button onclick="exportData()" class="bg-purple-500/30 text-purple-900 dark:text-purple-200 px-4 py-2 rounded-full text-sm hover:bg-purple-500/50 transition-all font-semibold w-full hover:scale-105">
                            <i class="fas fa-file-export mr-2"></i>JSON
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Enhanced Footer -->
        <div class="text-center mt-12 animate-fade-in">
            <div class="glass-light dark:glass-dark rounded-2xl p-6 mb-6 shadow-2xl">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4 neon-text">🔥 Free Fire Player Lookup</h3>
                <p class="text-gray-700 dark:text-gray-400 text-lg mb-4">Powered by NepDevs - The most comprehensive Free Fire statistics platform</p>
                <div class="flex justify-center space-x-6 mb-4">
                    <a href="https://t.me/nepdevsz" target="_blank" class="text-blue-500 hover:text-blue-400 transition-colors text-2xl hover:scale-110 transform" title="Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="#" class="text-purple-500 hover:text-purple-400 transition-colors text-2xl hover:scale-110 transform" title="Discord">
                        <i class="fab fa-discord"></i>
                    </a>
                    <a href="#" class="text-red-500 hover:text-red-400 transition-colors text-2xl hover:scale-110 transform" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="text-pink-500 hover:text-pink-400 transition-colors text-2xl hover:scale-110 transform" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">© 2024 NepDevs. All rights reserved. | Made with ❤️ for Free Fire Community</p>
                
                <!-- Additional Footer Links -->
                <div class="mt-4 flex flex-wrap justify-center gap-4 text-sm">
                    <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-orange-500 transition-colors hover:scale-105 transform">Privacy Policy</a>
                    <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-orange-500 transition-colors hover:scale-105 transform">Terms of Service</a>
                    <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-orange-500 transition-colors hover:scale-105 transform">API Documentation</a>
                    <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-orange-500 transition-colors hover:scale-105 transform">Contact Us</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced Theme Management with System Preference Detection
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('theme', 'light');
                showNotification('☀️ Light mode activated', 'info');
            } else {
                html.classList.add('dark');
                html.classList.remove('light');
                localStorage.setItem('theme', 'dark');
                showNotification('🌙 Dark mode activated', 'info');
            }
        }

        // Initialize theme with system preference detection
        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } else if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                // Default to system preference
                if (systemPrefersDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                    localStorage.setItem('theme', 'light');
                }
            }
        }

        // Enhanced Mobile Menu Functions
        function openMobileMenu() {
            document.getElementById('mobileMenu').classList.add('open');
            document.getElementById('mobileOverlay').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            document.getElementById('mobileMenu').classList.remove('open');
            document.getElementById('mobileOverlay').classList.remove('open');
            document.body.style.overflow = '';
        }

        // Enhanced Scroll Functions
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function focusSearch() {
            document.getElementById('uid').focus();
            document.getElementById('search').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        // Show/Hide Scroll to Top Button
        function toggleScrollTopButton() {
            const scrollTopBtn = document.getElementById('scrollTopBtn');
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.remove('hidden');
            } else {
                scrollTopBtn.classList.add('hidden');
            }
        }

        // Enhanced form submission with loading state
        document.getElementById('searchForm').addEventListener('submit', function() {
            const btn = document.getElementById('searchBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            btnText.textContent = '🔍 SEARCHING...';
            btnLoading.classList.remove('hidden');
            btn.disabled = true;
            btn.classList.add('opacity-75');
            
            showNotification('🔍 Searching for player data...', 'info');
            
            // Save search to recent searches
            const uid = document.getElementById('uid').value;
            const region = document.getElementById('region').value;
            if (uid && region) {
                saveRecentSearch(uid, region);
            }
        });

        // Recent searches functionality
        function saveRecentSearch(uid, region) {
            let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            const search = { uid, region, timestamp: Date.now() };
            
            // Remove if already exists
            recentSearches = recentSearches.filter(s => s.uid !== uid || s.region !== region);
            
            // Add to beginning
            recentSearches.unshift(search);
            
            // Keep only last 5
            recentSearches = recentSearches.slice(0, 5);
            
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
            displayRecentSearches();
        }

        function displayRecentSearches() {
            const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            const container = document.getElementById('recentSearchesList');
            const section = document.getElementById('recentSearches');
            
            if (recentSearches.length > 0) {
                container.innerHTML = recentSearches.map(search => 
                    `<button onclick="loadRecentSearch('${search.uid}', '${search.region}')" class="bg-gray-500/20 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full text-xs hover:bg-gray-500/30 transition-colors">
                        ${search.uid} (${search.region.toUpperCase()})
                    </button>`
                ).join('');
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        }

        function loadRecentSearch(uid, region) {
            document.getElementById('uid').value = uid;
            document.getElementById('region').value = region;
            showNotification('🔄 Loaded recent search', 'info');
        }

        // Random player function
        function randomPlayer() {
            const randomUIDs = [
                '123456789', '987654321', '456789123', '789123456', '321654987'
            ];
            const regions = ['ind', 'sg', 'th', 'my', 'ph'];
            
            const randomUID = randomUIDs[Math.floor(Math.random() * randomUIDs.length)];
            const randomRegion = regions[Math.floor(Math.random() * regions.length)];
            
            document.getElementById('uid').value = randomUID;
            document.getElementById('region').value = randomRegion;
            
            showNotification('🎲 Random player loaded!', 'info');
        }

        // Clear form function
        function clearForm() {
            document.getElementById('uid').value = '';
            document.getElementById('region').value = '';
            showNotification('🗑️ Form cleared', 'info');
        }

        // Retry search function
        function retrySearch() {
            document.getElementById('searchForm').submit();
        }

        // Share profile function
        function shareProfile() {
            const url = window.location.href;
            const playerName = '<?= isset($playerData['AccountInfo']['AccountName']) ? addslashes($playerData['AccountInfo']['AccountName']) : '' ?>';
            const shareText = `🔥 Check out ${playerName}'s Free Fire stats!\n\n${url}\n\n#FreeFire #NepDevs`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'Free Fire Player Stats',
                    text: shareText,
                    url: url
                }).then(() => {
                    showNotification('✅ Profile shared successfully!', 'success');
                }).catch(() => {
                    fallbackShare(shareText);
                });
            } else {
                fallbackShare(shareText);
            }
        }

        function fallbackShare(text) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('✅ Profile link copied to clipboard!', 'success');
            }).catch(() => {
                showNotification('❌ Failed to copy link', 'error');
            });
        }

        // Refresh data function
        function refreshData() {
            showNotification('🔄 Refreshing player data...', 'info');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        // Export data function
        function exportData() {
            <?php if ($playerData): ?>
            const data = <?= json_encode($playerData) ?>;
            const playerName = '<?= addslashes($playerData['AccountInfo']['AccountName']) ?>';
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${playerName}-freefire-data.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showNotification('✅ Data exported successfully!', 'success');
            <?php else: ?>
            showNotification('❌ No data to export', 'error');
            <?php endif; ?>
        }

        // Enhanced notification system
        function showNotification(message, type) {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            notification.className = `fixed top-4 left-4 right-4 md:left-auto md:right-4 md:w-80 z-50 p-4 rounded-lg text-white font-semibold animate-slide-down ${bgColor} notification shadow-2xl`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-20px)';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        // Counter animation function
        function animateCounter(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }

        // Initialize counters
        function initCounters() {
            const counters = document.querySelectorAll('.counter');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.dataset.target);
                        if (target) {
                            animateCounter(entry.target, target);
                        }
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => {
                observer.observe(counter);
            });
        }

        // Particle system
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = window.innerWidth < 768 ? 20 : 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Enhanced keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                e.target.closest('form')?.submit();
            }
            
            // Theme toggle with 'T' key
            if (e.key === 't' || e.key === 'T') {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    toggleTheme();
                }
            }
            
            // Escape key to close mobile menu or clear search
            if (e.key === 'Escape') {
                if (document.getElementById('mobileMenu').classList.contains('open')) {
                    closeMobileMenu();
                } else {
                    clearForm();
                }
            }
            
            // Quick search with '/' key
            if (e.key === '/') {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    focusSearch();
                }
            }
            
            // Random player with 'R' key
            if (e.key === 'r' || e.key === 'R') {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    randomPlayer();
                }
            }
        });

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            initCounters();
            createParticles();
            displayRecentSearches();
            
            // Show welcome message for first-time visitors
            if (!localStorage.getItem('visited')) {
                setTimeout(() => {
                    const isDark = document.documentElement.classList.contains('dark');
                    showNotification(`${isDark ? '🌙' : '☀️'} Welcome! Press 'T' to toggle theme, '/' for quick search, 'R' for random player`, 'info');
                    localStorage.setItem('visited', 'true');
                }, 1000);
            }
            
            // Initialize scroll listener
            window.addEventListener('scroll', toggleScrollTopButton);
            
            // Initialize intersection observer for animations
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-fade-in');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('.card-hover').forEach(el => {
                    observer.observe(el);
                });
            }
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('theme')) {
                if (e.matches) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }
            }
        });

        // Enhanced touch feedback for mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('button, .card-hover, .btn-primary').forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                    this.style.transition = 'transform 0.1s ease';
                }, { passive: true });
                
                element.addEventListener('touchend', function() {
                    this.style.transform = '';
                    this.style.transition = 'transform 0.3s ease';
                }, { passive: true });
            });
        }

        // Image loading optimization
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = this.className.replace('object-cover', '') + ' bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center';
                    placeholder.innerHTML = '<i class="fas fa-image text-white text-2xl"></i>';
                    this.parentNode.insertBefore(placeholder, this);
                });
                
                img.addEventListener('load', function() {
                    this.style.opacity = '0';
                    this.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 100);
                });
            });
        });

        // Performance optimizations for mobile
        if (window.innerWidth <= 768) {
            document.querySelectorAll('[class*="animate-"]').forEach(el => {
                el.style.animationDuration = '2s';
            });
        }

        // Auto-refresh functionality (optional)
        <?php if ($playerData): ?>
        let autoRefreshTimer;
        function startAutoRefresh() {
            autoRefreshTimer = setTimeout(() => {
                showNotification('🔄 Auto-refreshing data...', 'info');
                setTimeout(() => window.location.reload(), 2000);
            }, 600000); // 10 minutes
        }

        function stopAutoRefresh() {
            if (autoRefreshTimer) {
                clearTimeout(autoRefreshTimer);
            }
        }

        startAutoRefresh();

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });
        <?php endif; ?>

        // Service Worker registration for offline functionality
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                console.log('Service Worker support detected');
                // Implement service worker registration if needed
            });
        }

        // Memory cleanup
        window.addEventListener('beforeunload', function() {
            if (typeof autoRefreshTimer !== 'undefined') {
                clearTimeout(autoRefreshTimer);
            }
        });

        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button or notification
            setTimeout(() => {
                showNotification('📱 Install this app for better experience!', 'info');
            }, 5000);
        });

        // Analytics and Error Tracking (placeholder)
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            // Implement error tracking if needed
        });

        // Performance monitoring
        window.addEventListener('load', function() {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                console.log('Page Load Time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
            }, 0);
        });

        // Easter egg - Konami code
        let konamiCode = [];
        const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65]; // ↑↑↓↓←→←→BA
        
        document.addEventListener('keydown', function(e) {
            konamiCode.push(e.keyCode);
            if (konamiCode.length > konamiSequence.length) {
                konamiCode.shift();
            }
            
            if (konamiCode.length === konamiSequence.length && 
                konamiCode.every((code, index) => code === konamiSequence[index])) {
                showNotification('🎉 Konami Code activated! You found the easter egg!', 'success');
                document.body.style.animation = 'wiggle 0.5s ease-in-out 3';
                konamiCode = [];
            }
        });
    </script>
</body>
</html>

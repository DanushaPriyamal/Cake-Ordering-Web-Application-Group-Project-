<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Cakes";
// Pagination settings
$limit = 12; // Number of items per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// Get search query
$searchQuery = isset($_GET['search']) ? sanitize($_GET['search']) : '';
// Get filter parameters
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$filterType = isset($_GET['filter_type']) ? sanitize($_GET['filter_type']) : 'category';
// Initialize variables
$cakes = [];
$totalCakes = 0;
$totalPages = 0;
// Search cakes with pagination
if (!empty($searchQuery)) {
    $cakes = searchCakesPaginated($searchQuery, $page, $limit);
    $totalCakes = getSearchCakesCount($searchQuery);
}
// Filter cakes by category, subcategory or brand with pagination
elseif (!empty($categoryFilter)) {
    $cakes = filterCakesByCategoryPaginated($categoryFilter, $filterType, $page, $limit);
    $totalCakes = getFilteredCakesCount($categoryFilter, $filterType);
}
// Get all active cakes with pagination
else {
    $cakes = getAllCakesPaginated($page, $limit);
    $totalCakes = getTotalCakesCount();
}
// Calculate total pages
$totalPages = ceil($totalCakes / $limit);
// Get all categories and subcategories for filter menu
$categories = getCategories();
$brands = getBrands();
// Get subcategories grouped by category
$subcategoriesByCategory = [];
$allSubcategories = $conn->query("
    SELECT s.*, c.name as category_name, c.slug as category_slug
    FROM subcategories s
    JOIN categories c ON s.category_id = c.id
    WHERE s.is_active = 1
    ORDER BY c.name, s.name
")->fetch_all(MYSQLI_ASSOC);
foreach ($allSubcategories as $subcategory) {
    if (!isset($subcategoriesByCategory[$subcategory['category_slug']])) {
        $subcategoriesByCategory[$subcategory['category_slug']] = [
            'category_name' => $subcategory['category_name'],
            'subcategories' => []
        ];
    }
    $subcategoriesByCategory[$subcategory['category_slug']]['subcategories'][] = $subcategory;
}
?>
<style>
    :root {
        --primary-color: #FF6B6B;
        --secondary-color: #FF8E8E;
        --accent-color: #FFD166;
        --dark-color: #2E2E2E;
        --light-color: #F8F9FA;
        --gray-color: #6C757D;
        --light-gray: #E9ECEF;
        --success-color: #06D6A0;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        --shadow-hover: 0 10px 20px rgba(0, 0, 0, 0.1);
        --border-radius: 16px;
        --border-radius-sm: 10px;
        --border-radius-lg: 24px;
        --vh: 1vh;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        color: var(--dark-color);
        background-color: #fff;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -webkit-tap-highlight-color: transparent;
    }

    .container {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    h1 {
        font-size: 2.75rem;
        margin-bottom: 2rem;
        color: var(--dark-color);
        text-align: center;
        position: relative;
        padding-bottom: 20px;
        font-weight: 700;
        line-height: 1.2;
    }

    h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        border-radius: 3px;
    }

    /* Products Section */
    .products {
        padding: 5rem 0;
    }

    /* Search Container */
    .search-container {
        position: sticky;
        top: 0;
        z-index: 50;
        background: white;
        padding: 12px 0;
        margin-bottom: 0;
    }

    /* Search Form */
    .search-form {
        display: flex;
        max-width: 700px;
        margin: 0 auto;
        width: 100%;
        position: relative;
    }

    .search-form input {
        flex: 1;
        padding: 16px 24px;
        border: 2px solid var(--light-gray);
        border-radius: var(--border-radius-lg);
        font-size: 1.05rem;
        transition: var(--transition);
        outline: none;
        padding-right: 60px;
        box-shadow: var(--shadow);
    }

    .search-form input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
    }

    .search-form button {
        position: absolute;
        right: 6px;
        top: 6px;
        bottom: 6px;
        width: 50px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        font-size: 1rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-form button:hover {
        background: var(--secondary-color);
        transform: scale(0.95);
    }

    /* Filter Container */
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        align-items: center;
        margin-top: 1.5rem;
    }

    /* Filter Button */
    .filter-btn {
        background: white;
        color: var(--dark-color);
        border: 2px solid var(--light-gray);
        padding: 12px 24px;
        border-radius: var(--border-radius-lg);
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: var(--transition);
        box-shadow: var(--shadow);
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }

    .filter-btn i {
        font-size: 0.9rem;
        transition: var(--transition);
    }

    /* Filter Dropdown */
    .filter-dropdown {
        position: relative;
        display: inline-block;
    }

    .filter-dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 280px;
        box-shadow: var(--shadow-hover);
        border-radius: var(--border-radius-sm);
        z-index: 100;
        padding: 12px 0;
        max-height: 400px;
        overflow-y: auto;
        transform: translateY(10px);
        opacity: 0;
        transition: opacity 0.2s ease, transform 0.2s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .filter-dropdown.active .filter-dropdown-content {
        display: block;
        opacity: 1;
        transform: translateY(0);
        animation: fadeInDropdown 0.3s ease-out;
    }

    @keyframes fadeInDropdown {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .filter-dropdown-content a {
        color: var(--dark-color);
        padding: 12px 24px;
        text-decoration: none;
        display: block;
        transition: var(--transition);
        position: relative;
        font-size: 0.95rem;
    }

    .filter-dropdown-content a:hover {
        background: rgba(255, 107, 107, 0.05);
        color: var(--primary-color);
        padding-left: 28px;
    }

    .filter-dropdown-content a.active-filter {
        color: var(--primary-color);
        font-weight: 600;
        background: rgba(255, 107, 107, 0.08);
    }

    .filter-dropdown-content a.active-filter::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: var(--primary-color);
        border-radius: 50%;
    }

    .subcategory-item {
        padding-left: 36px !important;
        font-size: 0.9rem;
        color: var(--gray-color);
        position: relative;
    }

    .subcategory-item::before {
        content: '→';
        position: absolute;
        left: 20px;
        color: var(--gray-color);
        opacity: 0.6;
    }

    /* Reset Filters */
    .reset-filters {
        background: none;
        border: none;
        color: var(--gray-color);
        cursor: pointer;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: var(--transition);
        padding: 8px 16px;
        border-radius: var(--border-radius-sm);
    }

    .reset-filters:hover {
        color: var(--primary-color);
        background: rgba(255, 107, 107, 0.05);
    }

    /* Cake Grid */
    .cake-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2.5rem;
        margin-top: 2rem;
    }

    /* Cake Card */
    .cake-card {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
        border: 1px solid rgba(0, 0, 0, 0.05);
        cursor: pointer;
    }

    .cake-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
        border-color: rgba(255, 107, 107, 0.2);
    }

    .cake-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        opacity: 0;
        transition: var(--transition);
    }

    .cake-card:hover::before {
        opacity: 1;
    }

    .cake-image-container {
        position: relative;
        overflow: hidden;
    }

    .cake-card img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        display: block;
        transition: var(--transition);
    }

    .cake-card:hover img {
        transform: scale(1.03);
    }

    .quick-view-btn {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 107, 107, 0.9);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        z-index: 2;
    }

    .cake-card:hover .quick-view-btn {
        display: block;
    }

    .cake-card-content {
        padding: 1.75rem;
    }

    .cake-card h3 {
        font-size: 1.3rem;
        margin-bottom: 0.75rem;
        color: var(--dark-color);
        font-weight: 700;
    }

    .cake-card .brand,
    .cake-card .subcategory {
        font-size: 0.9rem;
        color: var(--gray-color);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cake-card .price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 1rem 0;
        display: flex;
        align-items: center;
    }

    .cake-card .price::before {
        content: 'Rs';
        font-size: 1rem;
        margin-right: 4px;
        opacity: 1;
    }

    .card-actions {
        margin-top: 1rem;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 28px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--border-radius-lg);
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        gap: 8px;
        box-shadow: 0 4px 6px rgba(255, 107, 107, 0.2);
    }

    .btn:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(255, 107, 107, 0.3);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn-secondary {
        background: white;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        box-shadow: none;
    }

    .btn-secondary:hover {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 6px rgba(255, 107, 107, 0.2);
    }

    /* No Results */
    .no-results {
        text-align: center;
        grid-column: 1 / -1;
        padding: 4rem;
        color: var(--gray-color);
        font-size: 1.2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .no-results i {
        font-size: 3.5rem;
        color: var(--primary-color);
        opacity: 0.7;
    }

    /* Active Filter Tags */
    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        margin-bottom: 1.5rem;
        padding: 0.5rem;
    }

    .active-filter-tag {
        display: inline-flex;
        align-items: center;
        background: white;
        color: #2d3748;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: var(--transition);
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .active-filter-tag::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-color);
    }

    .active-filter-tag:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .active-filter-tag i {
        margin-left: 10px;
        cursor: pointer;
        font-size: 0.8rem;
        color: #718096;
        transition: var(--transition);
        background: #edf2f7;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .active-filter-tag i:hover {
        color: white;
        background: var(--primary-color);
        transform: rotate(90deg);
    }

    /* Modern animation for appearing */
    @keyframes tagAppear {
        0% {
            opacity: 0;
            transform: translateY(5px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .active-filter-tag {
        animation: tagAppear 0.3s ease-out forwards;
        opacity: 0;
    }

    .active-filter-tag:nth-child(1) {
        animation-delay: 0.1s;
    }

    .active-filter-tag:nth-child(2) {
        animation-delay: 0.2s;
    }

    .active-filter-tag:nth-child(3) {
        animation-delay: 0.3s;
    }

    /* Mobile Filter Panel */
    .mobile-filter-panel {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        overflow-y: auto;
        padding: 20px;
        display: none;
    }

    .mobile-filter-panel.active {
        transform: translateX(0);
        display: block;
    }

    .mobile-filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid var(--light-gray);
        margin-bottom: 20px;
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .mobile-filter-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark-color);
    }

    .mobile-filter-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--gray-color);
        cursor: pointer;
        transition: var(--transition);
    }

    .mobile-filter-close:hover {
        color: var(--primary-color);
        transform: rotate(90deg);
    }

    .mobile-filter-content {
        padding-bottom: 20px;
    }

    /* Mobile Filter FAB */
    .mobile-filter-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.3);
        z-index: 90;
        cursor: pointer;
        transition: var(--transition);
    }

    .mobile-filter-fab:hover {
        transform: scale(1.1);
        background: var(--secondary-color);
    }

    .mobile-filter-fab i {
        font-size: 1.5rem;
    }

    /* Product Detail Overlay */
    .product-detail-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.9);
        z-index: 1000;
        display: none;
        overflow-y: auto;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-detail-overlay.active {
        opacity: 1;
        display: block;
    }

    .product-detail-content {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        max-width: 600px;
        margin: 20px auto;
        animation: slideUp 0.3s ease-out;
        position: relative;
    }

    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--gray-color);
        transition: var(--transition);
        z-index: 2;
    }

    .close-btn:hover {
        color: var(--primary-color);
    }

    /* Product Detail Content */
    .product-detail-image {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: var(--border-radius-sm);
        margin-bottom: 1.5rem;
    }

    .product-detail-title {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        color: var(--dark-color);
    }

    .product-detail-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .product-detail-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--gray-color);
        font-size: 0.95rem;
    }

    .product-detail-price {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 1rem 0;
    }

    .product-detail-description {
        color: #555;
        line-height: 1.7;
        margin-bottom: 1.5rem;
        max-height: 200px;
        overflow-y: auto;
        padding-right: 10px;
    }

    /* Share Dropdown */
    .share-dropdown {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .share-toggle {
        width: 100%;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: var(--transition);
    }

    .share-toggle:hover {
        background: var(--secondary-color);
    }

    .share-dropdown-content {
        display: none;
        position: absolute;
        bottom: 100%;
        left: 0;
        background: white;
        width: 100%;
        box-shadow: var(--shadow-hover);
        border-radius: var(--border-radius-sm);
        z-index: 1;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .share-dropdown.active .share-dropdown-content {
        display: block;
        animation: fadeInDropdown 0.2s ease-out;
    }

    .share-option {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--dark-color);
        text-decoration: none;
        transition: var(--transition);
    }

    .share-option:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    .share-option i {
        width: 20px;
        text-align: center;
    }

    .share-option.facebook {
        color: #4267B2;
    }

    .share-option.whatsapp {
        color: #25D366;
    }

    .share-option.twitter {
        color: #1DA1F2;
    }

    .share-option.link {
        color: var(--gray-color);
    }

    /* Action Buttons */
    .product-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .product-actions .btn {
        flex: 1;
    }

    /* Back Button */
    .back-btn {
        display: inline-flex;
        align-items: center;
        color: var(--gray-color);
        margin-bottom: 1rem;
        text-decoration: none;
        transition: var(--transition);
    }

    .back-btn:hover {
        color: var(--primary-color);
    }

    .back-btn i {
        margin-right: 8px;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        :root {
            --border-radius: 14px;
            --border-radius-sm: 8px;
        }

        h1 {
            font-size: 2.25rem;
            padding-bottom: 15px;
        }

        h1::after {
            width: 80px;
            height: 4px;
        }

        .products {
            padding: 3rem 0;
            min-height: calc(var(--vh, 1vh) * 100 - 120px);
        }

        .search-container {
            position: sticky;
            top: 0;
            z-index: 50;
            background: white;
            padding: 12px 0;
            margin-bottom: 0;
        }

        .search-form input {
            padding: 14px 20px;
            font-size: 16px;
        }

        .filter-container {
            display: none;
        }

        .filter-dropdown {
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .filter-btn {
            width: 100%;
            justify-content: space-between;
            padding: 16px 24px;
            border-radius: var(--border-radius);
        }

        .filter-dropdown-content {
            position: static;
            width: 100%;
            box-shadow: none;
            border: 1px solid var(--light-gray);
            border-top: none;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            max-height: 250px;
            margin-top: 4px;
        }

        .mobile-filter-fab {
            display: flex;
        }

        .cake-grid {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .cake-card img {
            height: 200px;
        }

        .cake-card-content {
            padding: 1.25rem;
            padding-bottom: 1rem;
        }

        .card-actions {
            display: none;
        }

        .quick-view-btn {
            display: block;
        }

        .cake-card:active {
            transform: scale(0.98);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Quick View */
        .product-detail-content {
            width: 100%;
            max-width: none;
            margin: 0;
            border-radius: 0;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .product-detail-image {
            height: 250px;
        }

        .product-detail-title {
            font-size: 1.6rem;
        }

        .product-detail-description {
            max-height: 150px;
        }

        .product-actions {
            flex-direction: column;
        }

        .share-dropdown-content {
            bottom: auto;
            top: 100%;
            margin-bottom: 0;
            margin-top: 10px;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .cake-card {
        animation: fadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        opacity: 0;
        animation-delay: calc(var(--index) * 0.1s);
    }



    /* pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin: 2rem 0;
        flex-wrap: wrap;
    }

    .pagination-numbers {
        display: flex;
        gap: 0.5rem;
    }

    .pagination-btn,
    .pagination-number {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination-btn:hover,
    .pagination-number:hover {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .pagination-number.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .pagination-info {
        text-align: center;
        color: #666;
        margin-bottom: 2rem;
    }

    /* Responsive pagination */
    @media (max-width: 768px) {
        .pagination {
            gap: 0.5rem;
        }

        .pagination-numbers {
            order: 3;
            width: 100%;
            justify-content: center;
            margin-top: 1rem;
        }

        .pagination-btn {
            padding: 0.5rem;
            font-size: 0.9rem;
        }

        .pagination-number {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
    }
</style>
<?php include('includes/header.php'); ?>
<section class="products">
    <div class="container">
        <h1>Our Cakes</h1>
        <div class="search-container">
            <form method="get" action="products" class="search-form">
                <input type="text" name="search" placeholder="Search Products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <?php if (!empty($searchQuery) || !empty($categoryFilter)): ?>
            <div class="active-filters">
                <?php if (!empty($searchQuery)): ?>
                    <span class="active-filter-tag">
                        Search: "<?php echo htmlspecialchars($searchQuery); ?>"
                        <a href="products"><i class="fas fa-times"></i></a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($categoryFilter)): ?>
                    <?php if ($filterType == 'category'): ?>
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['slug'] == $categoryFilter): ?>
                                <span class="active-filter-tag">
                                    Category: <?php echo htmlspecialchars($cat['name']); ?>
                                    <a href="products"><i class="fas fa-times"></i></a>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php elseif ($filterType == 'subcategory'): ?>
                        <?php foreach ($allSubcategories as $subcat): ?>
                            <?php if ($subcat['slug'] == $categoryFilter): ?>
                                <span class="active-filter-tag">
                                    Type: <?php echo htmlspecialchars($subcat['name']); ?>
                                    <a href="products"><i class="fas fa-times"></i></a>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php elseif ($filterType == 'brand'): ?>
                        <?php foreach ($brands as $brand): ?>
                            <?php if ($brand['slug'] == $categoryFilter): ?>
                                <span class="active-filter-tag">
                                    Brand: <?php echo htmlspecialchars($brand['name']); ?>

                                    <a href="products"><i class="fas fa-times"></i></a>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <!-- Desktop Filter Container (hidden on mobile)-->
        <div class="filter-container">
            <!-- Categories Dropdown -->
            <div class="filter-dropdown">
                <button class="filter-btn">
                    <i class="fas fa-layer-group"></i> Categories <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-dropdown-content">
                    <?php foreach ($categories as $category): ?>
                        <a href="products?category=<?= urlencode($category['slug']) ?>&filter_type=category"
                            class="<?php echo ($categoryFilter == $category['slug'] && $filterType == 'category') ? 'active-filter' : ''; ?>">
                            <?= htmlspecialchars($category['name']) ?>
                            <i class="fas fa-chevron-right float-right"></i>
                        </a>
                        <!-- Subcategories for this category -->
                        <?php if (isset($subcategoriesByCategory[$category['slug']])): ?>
                            <?php foreach ($subcategoriesByCategory[$category['slug']]['subcategories'] as $subcategory): ?>
                                <a href="products?category=<?= urlencode($subcategory['slug']) ?>&filter_type=subcategory"
                                    class="subcategory-item <?php echo ($categoryFilter == $subcategory['slug'] && $filterType == 'subcategory') ? 'active-filter' : ''; ?>">
                                    <?= htmlspecialchars($subcategory['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Brands Dropdown -->
            <div class="filter-dropdown">
                <button class="filter-btn">
                    <i class="fas fa-tags"></i> Brands <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-dropdown-content">
                    <?php foreach ($brands as $brand): ?>
                        <a href="products?category=<?= urlencode($brand['slug']) ?>&filter_type=brand"
                            class="<?php echo ($categoryFilter == $brand['slug'] && $filterType == 'brand') ? 'active-filter' : ''; ?>">

                            <img src="assets/uploads/brands/<?= htmlspecialchars($brand['image']) ?>"
                                alt="<?= htmlspecialchars($brand['name']) ?>"
                                style="width: 25px; height: 25px; border-radius: 50%; object-fit: cover; margin-right: 8px; vertical-align: middle;">

                            <?= htmlspecialchars($brand['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (!empty($searchQuery) || !empty($categoryFilter)): ?>
                <a href="products" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reset Filters
                </a>
            <?php endif; ?>
        </div>
        <!-- Mobile Filter Panel -->
        <div class="mobile-filter-panel" id="mobileFilterPanel">
            <div class="mobile-filter-header">
                <h2 class="mobile-filter-title">Filter Products</h2>
                <button class="mobile-filter-close" id="mobileFilterClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-filter-content">
                <!-- Categories Dropdown -->
                <div class="filter-dropdown">
                    <button class="filter-btn">
                        <i class="fas fa-layer-group"></i> Categories <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="filter-dropdown-content">
                        <?php foreach ($categories as $category): ?>
                            <a href="products?category=<?= urlencode($category['slug']) ?>&filter_type=category"
                                class="<?php echo ($categoryFilter == $category['slug'] && $filterType == 'category') ? 'active-filter' : ''; ?>">
                                <img src="assets/uploads/categories/<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                                <i class="fas fa-chevron-right float-right"></i>
                            </a>
                            <!-- Subcategories for this category -->
                            <?php if (isset($subcategoriesByCategory[$category['slug']])): ?>
                                <?php foreach ($subcategoriesByCategory[$category['slug']]['subcategories'] as $subcategory): ?>
                                    <a href="products?category=<?= urlencode($subcategory['slug']) ?>&filter_type=subcategory"
                                        class="subcategory-item <?php echo ($categoryFilter == $subcategory['slug'] && $filterType == 'subcategory') ? 'active-filter' : ''; ?>">
                                        <img src="assets/uploads/subcategories/<?= htmlspecialchars($subcategory['image']) ?>" alt="<?= htmlspecialchars($subcategory['name']) ?>">
                                        <?= htmlspecialchars($subcategory['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Brands Dropdown -->
                <div class="filter-dropdown">
                    <button class="filter-btn">
                        <i class="fas fa-tags"></i> Brands <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="filter-dropdown-content">
                        <?php foreach ($brands as $brand): ?>
                            <a href="products?category=<?= urlencode($brand['slug']) ?>&filter_type=brand"
                                class="<?php echo ($categoryFilter == $brand['slug'] && $filterType == 'brand') ? 'active-filter' : ''; ?>">
                                <img src="assets/uploads/brands/<?= htmlspecialchars($brand['image']) ?>"
                                    alt="<?= htmlspecialchars($brand['name']) ?>"
                                    style="width: 25px; height: 25px; border-radius: 50%; object-fit: cover; margin-right: 8px; vertical-align: middle;">

                                <?= htmlspecialchars($brand['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="cake-grid">
            <?php if (!empty($cakes)): ?>
                <?php foreach ($cakes as $index => $cake): ?>


                    <div class="cake-card"
                        data-product-id="<?php echo $cake['id']; ?>"
                        data-product-slug="<?php echo htmlspecialchars($cake['slug']); ?>"
                        style="--index: <?php echo $index % 8; ?>">



                        <div class="cake-image-container">
                            <img src="<?php echo getCakeImage($cake['image']); ?>" alt="<?php echo htmlspecialchars($cake['name']); ?>">
                            <button class="quick-view-btn">Quick View</button>
                        </div>
                        <div class="cake-card-content">
                            <h3><?php echo htmlspecialchars($cake['name']); ?></h3>
                            <?php if (!empty($cake['brand_name'])): ?>
                                <p class="brand"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($cake['category_name']); ?></p>
                            <?php endif; ?>
                            <p class="price"><?php echo number_format($cake['price'], 2); ?></p>
                            <div class="card-actions">
                                <a href="product-detail?slug=<?php echo urlencode($cake['slug']); ?>" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fa-solid fa-cake-candles"></i>
                    <p>No Product found matching your criteria.</p>
                    <a href="products" class="btn">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo buildPaginationUrl($page - 1); ?>" class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                <div class="pagination-numbers">
                    <?php
                    // Show page numbers
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?<?php echo buildPaginationUrl($i); ?>"
                            class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo buildPaginationUrl($page + 1); ?>" class="pagination-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <div class="pagination-info">
                Showing <?php echo (($page - 1) * $limit) + 1; ?> -
                <?php echo min($page * $limit, $totalCakes); ?> of <?php echo $totalCakes; ?> products
            </div>
        <?php endif; ?>
    </div>
</section>
<div class="mobile-filter-fab" id="mobileFilterFab">
    <i class="fas fa-filter"></i>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle viewport height for mobile
        function adjustViewport() {
            if (window.innerWidth <= 768) {
                document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
            }
        }
        window.addEventListener('resize', adjustViewport);
        adjustViewport();
        // Mobile filter panel functionality
        const mobileFilterFab = document.getElementById('mobileFilterFab');
        const mobileFilterPanel = document.getElementById('mobileFilterPanel');
        const mobileFilterClose = document.getElementById('mobileFilterClose');
        if (mobileFilterFab && mobileFilterPanel) {
            mobileFilterFab.addEventListener('click', function() {
                mobileFilterPanel.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        if (mobileFilterClose) {
            mobileFilterClose.addEventListener('click', function(e) {
                e.preventDefault();
                mobileFilterPanel.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        // Close mobile filter panel when clicking outside
        mobileFilterPanel.addEventListener('click', function(e) {
            if (e.target === mobileFilterPanel) {
                mobileFilterPanel.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        // Handle product card clicks
        const productCards = document.querySelectorAll('.cake-card');
        productCards.forEach(card => {
            // Click handler for the entire card
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on a button/link inside
                if (e.target.closest('a, button')) return;
                const productId = this.dataset.productId;
                // Note: For card click, we still use ID for now, but redirect uses slug in links
                window.location.href = `product-detail.php?slug=${encodeURIComponent(card.dataset.productSlug || 'default-slug')}`;
            });
            // Quick view button handler
            const quickViewBtn = card.querySelector('.quick-view-btn');
            if (quickViewBtn) {
                quickViewBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent the card click from firing
                    const productId = card.dataset.productId;
                    showQuickView(productId);
                });
            }
        });
        // Filter dropdowns
        const dropdownButtons = document.querySelectorAll('.filter-btn');
        dropdownButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.closest('.filter-dropdown');
                dropdown.classList.toggle('active');
                // Close other dropdowns
                document.querySelectorAll('.filter-dropdown').forEach(dd => {
                    if (dd !== dropdown) {
                        dd.classList.remove('active');
                    }
                });
                // Rotate chevron icon
                const icon = this.querySelector('.fa-chevron-down');
                if (icon) {
                    icon.style.transform = dropdown.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0)';
                }
            });
        });
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
            // Reset all chevron icons
            document.querySelectorAll('.filter-btn .fa-chevron-down').forEach(icon => {
                icon.style.transform = 'rotate(0)';
            });
        });
        // Auto-close mobile filter panel when a filter is selected
        document.querySelectorAll('.mobile-filter-content .filter-dropdown-content a').forEach(link => {
            link.addEventListener('click', function() {
                mobileFilterPanel.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        // Quick view functionality
        function showQuickView(productId) {
            const overlay = document.createElement('div');
            overlay.className = 'product-detail-overlay';
            overlay.innerHTML = `
                    <div class="product-detail-content">
                        <button class="close-btn">×</button>
                        <a href="products.php" class="back-btn" id="back-to-products">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                        <div class="loading-spinner" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                            <p style="margin-top: 1rem;">Loading product details...</p>
                        </div>
                    </div>
                `;
            document.body.appendChild(overlay);
            overlay.style.display = 'block';
            setTimeout(() => {
                overlay.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden';
            // Close button
            overlay.querySelector('.close-btn').addEventListener('click', function() {
                closeQuickView(overlay);
            });
            // Click outside to close
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeQuickView(overlay);
                }
            });
            // Back to products button
            overlay.querySelector('#back-to-products').addEventListener('click', function(e) {
                e.preventDefault();
                closeQuickView(overlay);
            });
            // Load product details via AJAX
            fetch(`get-product-details.php?id=${productId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const productUrl = `${window.location.origin}/product-detail.php?slug=${encodeURIComponent(data.slug)}`;
                    overlay.querySelector('.product-detail-content').innerHTML = `
                            <button class="close-btn">×</button>
                            <a href="products.php" class="back-btn" id="back-to-products">
                                <i class="fas fa-arrow-left"></i> Back to Products
                            </a>
                           
                            <img src="${data.image}" alt="${data.name}" class="product-detail-image">
                           
                            <h3 class="product-detail-title">${data.name}</h3>
                           
                            <div class="product-detail-meta">
                                ${data.brand_name ? `<span class="product-detail-meta-item">
                                    <i class="fas fa-tag"></i> ${data.brand_name}
                                </span>` : ''}
                                ${data.subcategory_name ? `<span class="product-detail-meta-item">
                                    <i class="fas fa-layer-group"></i> ${data.subcategory_name}
                                </span>` : ''}
                            </div>
                           
                            <p class="product-detail-price">Rs ${parseFloat(data.price).toFixed(2)}</p>
                           
                            <div class="product-detail-description">
                                ${data.description || 'No description available.'}
                            </div>
                           
                            <div class="share-dropdown">
                                <button class="share-toggle">
                                    <i class="fas fa-share-alt"></i> Share This Product
                                </button>
                                <div class="share-dropdown-content">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}"
                                       target="_blank" class="share-option facebook">
                                        <i class="fab fa-facebook-f"></i> Facebook
                                    </a>
                                    <a href="https://wa.me/?text=${encodeURIComponent(`Check out this product: ${data.name} - ${productUrl}`)}"
                                       target="_blank" class="share-option whatsapp">
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(`Check out this product: ${data.name}`)}&url=${encodeURIComponent(productUrl)}"
                                       target="_blank" class="share-option twitter">
                                        <i class="fab fa-twitter"></i> Twitter
                                    </a>
                                    <a href="#" class="share-option link" onclick="copyToClipboard('${productUrl}', this); return false;">
                                        <i class="fas fa-link"></i> Copy Link
                                    </a>
                                </div>
                            </div>
                           
                            <div class="product-actions">
                                <a href="product-detail.php?slug=${encodeURIComponent(data.slug)}" class="btn">
                                    <i class="fas fa-external-link-alt"></i> View Full Details
                                </a>
                            </div>
                        `;
                    // Reattach close events
                    overlay.querySelector('.close-btn').addEventListener('click', function() {
                        closeQuickView(overlay);
                    });
                    overlay.querySelector('#back-to-products').addEventListener('click', function(e) {
                        e.preventDefault();
                        closeQuickView(overlay);
                    });
                    // Share dropdown toggle
                    const shareToggle = overlay.querySelector('.share-toggle');
                    const shareDropdown = overlay.querySelector('.share-dropdown');
                    if (shareToggle && shareDropdown) {
                        shareToggle.addEventListener('click', function(e) {
                            e.stopPropagation();
                            shareDropdown.classList.toggle('active');
                        });
                    }
                    // Close share dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (shareDropdown && !shareDropdown.contains(e.target)) {
                            shareDropdown.classList.remove('active');
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading product details:', error);
                    overlay.querySelector('.product-detail-content').innerHTML = `
                            <button class="close-btn">×</button>
                            <a href="products.php" class="back-btn" id="back-to-products">
                                <i class="fas fa-arrow-left"></i> Back to Products
                            </a>
                            <div style="text-align: center; padding: 2rem;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--primary-color);"></i>
                                <p style="margin-top: 1rem;">Error loading product details: ${error.message}</p>
                                <a href="product-detail.php?slug=${encodeURIComponent('default-slug')}" class="btn" style="margin-top: 1rem; display: inline-block;">
                                    View Product Page
                                </a>
                            </div>
                        `;
                    overlay.querySelector('#back-to-products').addEventListener('click', function(e) {
                        e.preventDefault();
                        closeQuickView(overlay);
                    });
                });
        }

        function closeQuickView(overlay) {
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.remove();
                document.body.style.overflow = '';
            }, 300);
        }
        // Copy to clipboard function
        window.copyToClipboard = function(text, element) {
            navigator.clipboard.writeText(text).then(() => {
                if (element) {
                    const originalText = element.innerHTML;
                    element.innerHTML = '<i class="fas fa-check"></i> Link Copied!';
                    setTimeout(() => {
                        element.innerHTML = originalText;
                    }, 2000);
                }
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                if (element) {
                    const originalText = element.innerHTML;
                    element.innerHTML = '<i class="fas fa-times"></i> Failed to Copy';
                    setTimeout(() => {
                        element.innerHTML = originalText;
                    }, 2000);
                }
            });
        };
        // Auto-focus search input when filter FAB is clicked on mobile
        if (window.innerWidth <= 768) {
            const searchInput = document.querySelector('.search-form input');
            if (searchInput && mobileFilterFab) {
                mobileFilterFab.addEventListener('click', function() {
                    setTimeout(() => {
                        searchInput.focus();
                    }, 300);
                });
            }
        }
    });
</script>
<?php include 'includes/footer.php'; ?>
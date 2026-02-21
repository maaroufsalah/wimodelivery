<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();



include get_file("files/sql/get/os_settings");
$primaryColor = "#006964";
?>



<html lang="en">
<!--begin::Head-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php print $set_name; ?></title>
<!--begin::Primary Meta Tags-->
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="icon" type="image/x-icon" href="uploads/<?=$set_favicon;?>">

<script src="https://cdn.jsdelivr.net/npm/get-google-fonts@1.2.2/main.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/4.5.6/css/ionicons-core.min.css" integrity="sha512-OmevVDECSDeo7M4G+Nvh0+VLVGS2XnEOkXWJcJ0TRom3GpGgc/ryQIgpRZw20mb5eR2U0sqsm33MaR8yD1zdsQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/4.5.6/css/ionicons.min.css" integrity="sha512-0/rEDduZGrqo4riUlwqyuHDQzp2D1ZCgH/gFIfjMIL5az8so6ZiXyhf1Rg8i6xsjv+z/Ubc4tt1thLigEcu6Ug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
crossorigin="anonymous"													
/>
<!--end::Fonts-->
<!--begin::Third Party Plugin(OverlayScrollbars)-->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
crossorigin="anonymous"
/>
<!--end::Third Party Plugin(OverlayScrollbars)-->
<!--begin::Third Party Plugin(Bootstrap Icons)-->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
crossorigin="anonymous"
/>
<!--end::Third Party Plugin(Bootstrap Icons)-->
<!--begin::Required Plugin(AdminLTE)-->
<link rel="stylesheet" href="themes/css/adminlte.css" />
<link rel="stylesheet" href="themes/css/dashlite.css" />
<link rel="stylesheet" href="themes/richtexteditor/rte_theme_default.css" />
<!--end::Required Plugin(AdminLTE)-->
<!-- apexcharts -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
crossorigin="anonymous"
/>
<!-- jsvectormap -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
crossorigin="anonymous"
/>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />



<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>


<style>
.t-row{
font-size: .9em;
}
.tb-row{
font-size: .7em;
}
.select2-selection__rendered {
line-height: 38px !important;
}
.select2-container .select2-selection--single {
height: 38px !important;
}
.select2-selection__arrow {
height: 38px !important;
}
</style>
<style>
.timeline {
list-style: none;
padding: 0;
position: relative;
}
.timeline:before {
content: '';
position: absolute;
left: 18px;
top: 0;
bottom: 0;
width: 2px;
background: #dee2e6;
}
.timeline-item {
position: relative;
margin-left: 40px;
}
.timeline-item:before {
content: '';
position: absolute;
left: -22px;
top: 5px;
width: 12px;
height: 12px;
background-color: #0d6efd;
border-radius: 50%;
border: 2px solid white;
z-index: 1;
}

/* خلفيات متدرجة للكروت */
.bg-gradient-cosmic {
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-gradient-ibiza {
background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
}
.bg-gradient-ohhappiness {
background: linear-gradient(135deg, #ff5858 0%, #f09819 100%);
}
.bg-success {
background-color: #198754 !important;
}
.bg-dark {
background-color: #212529 !important;
}
.widgets-icons {
padding: 15px;
border-radius: 50%;
}
.bg-light-success {
background-color: #d1e7dd;
}
.text-success {
color: #0f5132 !important;
}
a{
text-decoration: auto;
}

.sidebar-wrapper .sidebar-menu > .nav-item.menu-open > .nav-link, .sidebar-wrapper .sidebar-menu > .nav-item:hover > .nav-link, .sidebar-wrapper .sidebar-menu > .nav-item > .nav-link:focus {
color: #343a40;
background-color: var(--lte-sidebar-hover-bg);
}

.sidebar-wrapper .nav-link {
font-weight: bolder;
color: black;
display: flex;
justify-content: flex-start;
}


.sidebar-wrapper .sidebar-menu > .nav-item > .nav-link.active(:hover) {
color: #ed9f64;
background-color: var(--lte-sidebar-menu-active-bg);
}

.nav-link{
color:black;
}

.btn-sm {
font-size:15px !important;
}

.pagination {
--bs-pagination-padding-x: 0.75rem;
--bs-pagination-padding-y: 0.375rem;
--bs-pagination-font-size: 1rem;
--bs-pagination-color: <?=$primaryColor;?>;
--bs-pagination-bg: var(--bs-body-bg);
--bs-pagination-border-width: var(--bs-border-width);
--bs-pagination-border-color: var(--bs-border-color);
--bs-pagination-border-radius: var(--bs-border-radius);
--bs-pagination-hover-color: <?=$primaryColor;?>;
--bs-pagination-hover-bg: var(--bs-tertiary-bg);
--bs-pagination-hover-border-color: var(--bs-border-color);
--bs-pagination-focus-color: <?=$primaryColor;?>;
--bs-pagination-focus-bg: var(--bs-secondary-bg);
--bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
--bs-pagination-active-color: #fff;
--bs-pagination-active-bg: <?=$primaryColor;?>;
--bs-pagination-active-border-color: <?=$primaryColor;?>;
--bs-pagination-disabled-color: var(--bs-secondary-color);
--bs-pagination-disabled-bg: var(--bs-secondary-bg);
--bs-pagination-disabled-border-color: var(--bs-border-color);
display: flex
;
padding-left: 0;
list-style: none;
}

.btn-primary {
font-weight: bold;
--bs-btn-color: #fff;
--bs-btn-bg: <?=$primaryColor;?>;
--bs-btn-border-color: <?=$primaryColor;?>;
--bs-btn-hover-color: #fff;
--bs-btn-hover-bg: <?=$primaryColor;?>;
--bs-btn-hover-border-color: <?=$primaryColor;?>;
--bs-btn-focus-shadow-rgb: 49, 132, 253;
--bs-btn-active-color: #fff;
--bs-btn-active-bg: <?=$primaryColor;?>;
--bs-btn-active-border-color: <?=$primaryColor;?>;
--bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
--bs-btn-disabled-color: #fff;
--bs-btn-disabled-bg: <?=$primaryColor;?>;
--bs-btn-disabled-border-color: <?=$primaryColor;?>;
}


.bg-primary {
background: <?=$primaryColor;?> !important;
}

.table-primary {
background: <?=$primaryColor;?> !important;
}

.text-primary {
color: <?=$primaryColor;?> !important;
}

nav-link:hover { color: orange !important; }


.sidebar-wrapper .nav-treeview > .nav-item > .nav-link {
color: #343a40;
}

.sidebar-wrapper .nav-treeview > .nav-item > .nav-link:hover {
background-color: #f99f5c;
}


.select2-container {
width: 100% !important;
}

.select2-container--open {
z-index: 9999;
}


@media (max-width: 768px) {
.app-sidebar {
position: static;

}
}

@media (max-width: 991.98px) {
.sidebar-expand-lg .app-sidebar {
max-width: 0;
position: fixed;
top: 0;
bottom: 0;
max-height: 100vh;
margin-left: calc(var(--lte-sidebar-width) * -1);
}
}

aside {
flex-grow: 1;
overflow-y: auto; /* ✅ Scroll عمودي */

}

.app-sidebar .sidebar-brand {

padding: 1.5rem 2rem;
border-bottom: 1px solid #eee;
text-align: center;
}

.app-sidebar {
width: 280px;
max-width:280px;
}

.app-sidebar .sidebar-brand img {
max-height: 50px;
width: auto;
}

.app-sidebar nav ul {
list-style: none;
margin: 0;
padding: 0;
}
.app-sidebar nav ul li {
position: relative;
}
.app-sidebar .nav-link {
display: flex;
align-items: center;
padding: 1rem 2rem;
color: #222;
font-weight: 600;
text-decoration: none;
transition: background 0.3s, color 0.3s;
cursor: pointer;
}
.app-sidebar .nav-link .icon {
font-size: 1.5rem;
margin-right: 1rem;
color: #222;
transition: color 0.3s;
display: flex;
align-items: center;
justify-content: center;
}
.app-sidebar .nav-link .text {
flex-grow: 1;
}
.app-sidebar .nav-link .arrow {
font-size: 1.4rem;
transition: transform 0.3s, color 0.3s;
color: #999;
margin-left: auto;
}
.app-sidebar .nav-link:hover,
.app-sidebar .nav-link.active {
background: <?=$primaryColor;?>;
color: #fff;
border-radius: 0rem;
}



.app-sidebar .nav-link:hover .icon,
.app-sidebar .nav-link.active .icon {
color: #fff;
}
.app-sidebar .nav-link:hover .arrow,
.app-sidebar .nav-link.active .arrow {
color: #fff;
}
.app-sidebar .nav-treeview {
display: none;
background: #fafafa;
}
.app-sidebar .nav-treeview .nav-link {
padding-left: 2rem;
font-weight: 500;
color: #555;
}
.app-sidebar .nav-treeview .nav-link:hover {
background: <?=$primaryColor;?>;
color: #fff;
}
.app-sidebar .badge {
font-size: 0.75rem;
font-weight: 600;
margin-left: 5px;
vertical-align: middle;
border-radius: 0rem;
}

.menu-size-tree{
font-size:13px;
}





</style>



</head>




<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.whatsapp-float {
position: fixed;
width: 60px;
height: 60px;
bottom: 80px;
right: 20px;
background-color: #25D366;
color: #fff;
border-radius: 50%;
text-align: center;
font-size: 30px;
box-shadow: 2px 2px 3px #999;
z-index: 100;
display: flex;
align-items: center;
justify-content: center;
text-decoration: none;
}
.whatsapp-float:hover {
background-color: #20b858;
color: #fff;
}
</style>



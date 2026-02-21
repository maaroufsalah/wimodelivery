<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");

?>

<script>
document.addEventListener('DOMContentLoaded', function() {
const selectAllBtn = document.getElementById('selectAllBtn');
if (!selectAllBtn) return; // لو ما فيه زر لا نفعل شيئاً

let allSelected = false;

function updateSelectAllBtnText() {
let checkboxes = document.querySelectorAll('.order-checkbox');
let checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
allSelected = checkedBoxes.length === checkboxes.length && checkboxes.length > 0;

if (allSelected) {
selectAllBtn.textContent = `Désélectionner tout (${checkedBoxes.length})`;
} else if (checkedBoxes.length > 0) {
selectAllBtn.textContent = `Sélectionner (${checkedBoxes.length})`;
} else {
selectAllBtn.textContent = 'Tout sélectionner';
}
}

selectAllBtn.addEventListener('click', function() {
let checkboxes = document.querySelectorAll('.order-checkbox');
allSelected = !allSelected;

checkboxes.forEach(function(checkbox) {
checkbox.checked = allSelected;
});

updateSelectAllBtnText();
});

document.body.addEventListener('change', function(e) {
if (e.target.classList.contains('order-checkbox')) {
updateSelectAllBtnText();
}
});

updateSelectAllBtnText();
});

</script>
<footer class="app-footer">
<!--begin::To the end-->
<div class="float-end d-none d-sm-inline"></div>
<!--end::To the end-->
<!--begin::Copyright-->
<strong>
Copyright &copy; <?php print date("Y") ;?>&nbsp;
<a href="" class="text-decoration-none"><?php print $set_name ;?></a>.
</strong>
All rights reserved.
<!--end::Copyright-->
</footer>
<!--end::Footer-->
</div>
<!--end::App Wrapper-->
<!--begin::Script-->
<!--begin::Third Party Plugin(OverlayScrollbars)-->


<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>





<script
src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
crossorigin="anonymous"
></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script
src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
crossorigin="anonymous"
></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
crossorigin="anonymous"
></script>
<!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
<script src="themes/js/adminlte.js"></script>
<script src="themes/richtexteditor/rte.js"></script>
<!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
<script>
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
const Default = {
scrollbarTheme: 'os-theme-light',
scrollbarAutoHide: 'leave',
scrollbarClickScroll: true,
};
document.addEventListener('DOMContentLoaded', function () {
const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
scrollbars: {
theme: Default.scrollbarTheme,
autoHide: Default.scrollbarAutoHide,
clickScroll: Default.scrollbarClickScroll,
},
});
}
});
</script>
<!--end::OverlayScrollbars Configure-->
<!-- OPTIONAL SCRIPTS -->
<!-- sortablejs -->
<script
src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ="
crossorigin="anonymous"
></script>
<!-- sortablejs -->
<script>
const connectedSortables = document.querySelectorAll('.connectedSortable');
connectedSortables.forEach((connectedSortable) => {
let sortable = new Sortable(connectedSortable, {
group: 'shared',
handle: '.card-header',
});
});

const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
cardHeaders.forEach((cardHeader) => {
cardHeader.style.cursor = 'move';
});
</script>
<!-- apexcharts -->
<script
src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
crossorigin="anonymous"
></script>
<!-- ChartJS -->
<script>
// NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
// IT'S ALL JUST JUNK FOR DEMO
// ++++++++++++++++++++++++++++++++++++++++++

const sales_chart_options = {
series: [
{
name: 'Digital Goods',
data: [28, 48, 40, 19, 86, 27, 90],
},
{
name: 'Electronics',
data: [65, 59, 80, 81, 56, 55, 40],
},
],
chart: {
height: 300,
type: 'area',
toolbar: {
show: false,
},
},
legend: {
show: false,
},
colors: ['#0d6efd', '#20c997'],
dataLabels: {
enabled: false,
},
stroke: {
curve: 'smooth',
},
xaxis: {
type: 'datetime',
categories: [
'2023-01-01',
'2023-02-01',
'2023-03-01',
'2023-04-01',
'2023-05-01',
'2023-06-01',
'2023-07-01',
],
},
tooltip: {
x: {
format: 'MMMM yyyy',
},
},
};

const sales_chart = new ApexCharts(
document.querySelector('#revenue-chart'),
sales_chart_options,
);
sales_chart.render();
</script>
<!-- jsvectormap -->
<script
src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
crossorigin="anonymous"
></script>
<script
src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
crossorigin="anonymous"
></script>
<!-- jsvectormap -->
<script>
const visitorsData = {
US: 398, // USA
SA: 400, // Saudi Arabia
CA: 1000, // Canada
DE: 500, // Germany
FR: 760, // France
CN: 300, // China
AU: 700, // Australia
BR: 600, // Brazil
IN: 800, // India
GB: 320, // Great Britain
RU: 3000, // Russia
};

// World map by jsVectorMap
const map = new jsVectorMap({
selector: '#world-map',
map: 'world',
});

// Sparkline charts
const option_sparkline1 = {
series: [
{
data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
},
],
chart: {
type: 'area',
height: 50,
sparkline: {
enabled: true,
},
},
stroke: {
curve: 'straight',
},
fill: {
opacity: 0.3,
},
yaxis: {
min: 0,
},
colors: ['#DCE6EC'],
};

const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
sparkline1.render();

const option_sparkline2 = {
series: [
{
data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
},
],
chart: {
type: 'area',
height: 50,
sparkline: {
enabled: true,
},
},
stroke: {
curve: 'straight',
},
fill: {
opacity: 0.3,
},
yaxis: {
min: 0,
},
colors: ['#DCE6EC'],
};

const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
sparkline2.render();

const option_sparkline3 = {
series: [
{
data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
},
],
chart: {
type: 'area',
height: 50,
sparkline: {
enabled: true,
},
},
stroke: {
curve: 'straight',
},
fill: {
opacity: 0.3,
},
yaxis: {
min: 0,
},
colors: ['#DCE6EC'],
};

const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
sparkline3.render();
</script>
<script>
$(document).ready(function() {
$('.editor').summernote({
height: 300,
placeholder: 'اكتب النص هنا...',
toolbar: [
['style', ['bold', 'italic', 'underline', 'clear']],
['font', ['strikethrough', 'superscript', 'subscript']],
['para', ['ul', 'ol', 'paragraph']],
['insert', ['link', 'picture', 'video', 'emoji']],
['view', ['fullscreen', 'codeview', 'help']]
]
});
});
</script>


<script>
function initSelect2(context = document) {
  $(context).find("select").each(function () {
    // إذا select2 مش محمل من قبل
    if (!$(this).hasClass("select2-hidden-accessible")) {
      const $modalParent = $(this).closest('.modal');

      $(this).select2({
        dropdownParent: $modalParent.length ? $modalParent : $('body'),
        width: '100%',
        minimumResultsForSearch: 0
      });
    }
  });
}

$(document).ready(function () {
  // التهيئة العامة (خارج المودالات)
  initSelect2();
});

// إعادة التهيئة بعد ما المودال يتفتح
$(document).on('shown.bs.modal', '.modal', function () {
  $(this).find("select").select2({
    dropdownParent: $(this),   // نربط الـ dropdown مباشرة بالمودال
    width: '100%',
    minimumResultsForSearch: 0
  });
});
</script>


<script>
const current = window.location.pathname.split("/").pop();
document.querySelectorAll('.nav-link').forEach(link => {
if(link.getAttribute('href') === current){
link.classList.add('active');
}
});
</script>



<!--end::Script-->
</body>
<!--end::Body-->
</html>


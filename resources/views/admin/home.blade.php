<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
  @include('admin.css')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery Cookie Plugin (if used) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">

<!-- DataTables Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  </head>
  <body>
    @include('admin.header')

      <!-- partial:partials/_sidebar.html -->
    @include('admin.sidebar')

      <!-- partial -->
    @include('admin.navbar')
        <!-- partial -->
    @include('admin.body')

    <!-- container-scroller -->
    <!-- plugins:js -->
    @include('admin.java')




    <!-- End custom js for this page -->
  </body>
</html>